<?php
namespace de\buzz2ee\aop;

use de\buzz2ee\aop\advice\Advices;
use de\buzz2ee\aop\interfaces\JoinPoint;
use de\buzz2ee\aop\interfaces\AdviceRegistry;
use de\buzz2ee\aop\interfaces\PointcutRegistry;

use de\buzz2ee\aop\pointcut\PointcutMatcherFactory;
use de\buzz2ee\aop\pointcut\PointcutExpressionParser;

class DefaultAdviceRegistry implements AdviceRegistry, PointcutRegistry
{
    /**
     *
     * @var array(\de\buzz2ee\aop\Aspect)
     */
    private $_aspects = array();

    public function __construct()
    {
        PointcutMatcherFactory::set( new PointcutMatcherFactory() );
    }

    public function registerAspect( $aspectClassName )
    {
        $aspect = new Aspect( $aspectClassName );

        $reflection = new \ReflectionClass( $aspectClassName );
        foreach ( $reflection->getMethods() as $method )
        {
            if ( preg_match( '(\*\s*@(Pointcut|After|AfterReturning|AfterThrowing|Before|Around)\(\s*"(.*)"\s*\)\s*\*(\r|\n|/))s', $method->getDocComment(), $match ) === 0 )
            {
                continue;
            }

            $pointcutName = $reflection->getName() . '::' . $method->getName() . '()';

            $tagName    = $match[1];
            $expression = $match[2];

            $expression = trim( preg_replace( '(\s*\*\s+)', '', $expression ) );

            $parser   = new PointcutExpressionParser();
            $pointcut = new \de\buzz2ee\aop\pointcut\DefaultPointcut(
                $pointcutName,
                $parser->parse( $expression )
            );

            switch ( $tagName )
            {
                case 'Pointcut':
                    $aspect->addPointcut( $pointcut );
                    break;

                case 'After':
                    $aspect->addAdvice( new \de\buzz2ee\aop\advice\AfterAdvice( $pointcut, $method->getName(), $reflection->getName() ) );
                    break;

                case 'AfterReturning':
                    $aspect->addAdvice( new \de\buzz2ee\aop\advice\AfterReturningAdvice( $pointcut, $method->getName(), $reflection->getName() ) );
                    break;

                case 'AfterThrowing':
                    $aspect->addAdvice( new \de\buzz2ee\aop\advice\AfterThrowingAdvice( $pointcut, $method->getName(), $reflection->getName() ) );
                    break;

                case 'Around':
                    $aspect->addAdvice( new \de\buzz2ee\aop\advice\AroundAdvice( $pointcut, $method->getName(), $reflection->getName() ) );
                    break;

                case 'Before':
                    $aspect->addAdvice( new \de\buzz2ee\aop\advice\BeforeAdvice( $pointcut, $method->getName(), $reflection->getName() ) );
                    break;
            }
        }

        $this->_aspects[] = $aspect;
    }

    public function getPointcutByName( $pointcutName )
    {
        $name = ltrim( $pointcutName, '\\' );
        foreach ( $this->_aspects as $aspect )
        {
            foreach ( $aspect->getPointcuts() as $pointcut )
            {
                if ( $pointcut->getName() === $name )
                {
                    return $pointcut;
                }
            }
        }

        throw new \InvalidArgumentException( 'Unknown pointcut name: ' . $pointcutName  );
    }

    /**
     * @return \de\buzz2ee\aop\advice\Advices
     */
    public function getMatchingAdvices( JoinPoint $joinPoint )
    {
        return $this->_getAdvicesForJoinPoint( $joinPoint );
    }

    private function _getAdvicesForJoinPoint( JoinPoint $joinPoint )
    {
        $advices = new Advices();
        foreach ( $this->_aspects as $aspect )
        {
            foreach ( $aspect->getAdvices() as $advice )
            {
                if ( $advice->getPointcut()->match( $joinPoint, $this ) )
                {
                    $advices->add( $advice );
                }
            }
        }
        return $advices;
    }
}