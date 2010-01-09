<?php
namespace de\buzz2ee\aop\generator;

use de\buzz2ee\aop\advice\AfterAdvice;
use de\buzz2ee\aop\advice\AfterReturningAdvice;
use de\buzz2ee\aop\advice\AfterThrowingAdvice;
use de\buzz2ee\aop\advice\BeforeAdvice;

use de\buzz2ee\aop\interfaces\JoinPoint;
use de\buzz2ee\aop\interfaces\PointcutRegistry;

class AdviceCodeGenerator
{
    /**
     *
     * @var \de\buzz2ee\aop\interfaces\PointcutRegistry
     */
    private $_registry = null;

    private $_aspects = array();

    private $_interceptors = array();

    public function __construct( PointcutRegistry $registry, array $aspects )
    {
        $this->_registry = $registry;
        $this->_aspects  = $aspects;
    }

    public function generateClassInterceptCode()
    {
        $code = '    private $_aop_interceptor_configuration__ = array(' . PHP_EOL;
        foreach ( array_unique( $this->_interceptors ) as $interceptor )
        {
            $code .= "        '" . $interceptor . "'," . PHP_EOL;
        }
        $code .= '    );' . 
                 PHP_EOL . PHP_EOL .
                 '    private $_aop_interceptor_instances__ = array();' .
                 PHP_EOL . PHP_EOL .
                 '    public function _get_aop_interceptor_configuration()' . PHP_EOL .
                 '    {' . PHP_EOL .
                 '        return $this->_aop_interceptor_configuration__;' . PHP_EOL .
                 '    }' . 
                 PHP_EOL . PHP_EOL .
                 '    public function _add_aop_interceptor_instance( $name, $interceptor )' . PHP_EOL .
                 '    {' . PHP_EOL .
                 '        $this->_aop_interceptor_instances__[$name] = $interceptor;' . PHP_EOL .
                 '    }' . PHP_EOL;

        return $code;
    }

    public function generateMethodInterceptCodeProlog( JoinPoint $joinPoint )
    {
        return $this->generateProlog( $joinPoint ) .
               $this->generateBefore( $joinPoint ) .
               $this->generateTryCatchProlog( $joinPoint );
    }

    public function generateMethodInterceptCodeEpilog( JoinPoint $joinPoint )
    {
        return $this->generateTryCatchEpilog( $joinPoint ) .
               $this->generateAfterReturning( $joinPoint );
    }

    public function generateProlog( JoinPoint $joinPoint )
    {
        if ( count( $this->_getAdvicesForJoinPoint( $joinPoint ) ) === 0 )
        {
            return '';
        }
        
        return '        $arguments = func_get_args();' . PHP_EOL .
               '        $joinPoint = new \de\buzz2ee\aop\RuntimeJoinPoint( $this, ' .
               "'" . $joinPoint->getClassName() . "', " .
               "'" . $joinPoint->getMethodName() . "', " .
               '$arguments );' . PHP_EOL .
               PHP_EOL;
    }

    public function generateBefore( JoinPoint $joinPoint )
    {
        $code = '';
        foreach ( $this->_getAdvicesForJoinPoint( $joinPoint ) as $advice )
        {
            if ( $advice instanceof BeforeAdvice )
            {
                $this->_interceptors[] = $advice->getName();

                $code .= '        $this->_aop_interceptor_instances__["' . $advice->getName() . '"]->invoke( $joinPoint );' . PHP_EOL;
            }
        }
        return $code;
    }

    public function generateTryCatchProlog( JoinPoint $joinPoint )
    {
        foreach ( $this->_getAdvicesForJoinPoint( $joinPoint) as $advice )
        {
            if ( ( $advice instanceof AfterAdvice || $advice instanceof AfterThrowingAdvice ) === false )
            {
                continue;
            }
            return '        try' . PHP_EOL .
                   '        {' . PHP_EOL;
        }
        return '';
    }

    public function generateTryCatchEpilog( JoinPoint $joinPoint )
    {
        foreach ( $this->_getAdvicesForJoinPoint( $joinPoint) as $advice )
        {
            if ( ( $advice instanceof AfterAdvice || $advice instanceof AfterThrowingAdvice ) === false )
            {
                continue;
            }
            return $this->generateAfter( $joinPoint ) .
                   '        }' . PHP_EOL .
                   '        catch ( \Exception $e )' . PHP_EOL .
                   '        {' . PHP_EOL .
                   $this->generateAfterThrowing( $joinPoint ) .
                   $this->generateAfter( $joinPoint ) .
                   '            throw $e;' . PHP_EOL .
                   '        }' . PHP_EOL;
        }
        return '';
    }

    public function generateAfter( JoinPoint $joinPoint )
    {
        $code = '';
        foreach ( $this->_getAdvicesForJoinPoint( $joinPoint ) as $advice )
        {
            if ( $advice instanceof AfterAdvice )
            {
                $this->_interceptors[] = $advice->getName();

                $code .= '            $this->_aop_interceptor_instances__["' . $advice->getName() . '"]->invoke( $joinPoint );' . PHP_EOL;
            }
        }
        return $code;
    }

    public function generateAfterThrowing( JoinPoint $joinPoint )
    {
        $code = '';
        foreach ( $this->_getAdvicesForJoinPoint( $joinPoint ) as $advice )
        {
            if ( $advice instanceof AfterThrowingAdvice )
            {
                $this->_interceptors[] = $advice->getName();

                $code .= '            $this->_aop_interceptor_instances__["' . $advice->getName() . '"]->invoke( $joinPoint );' . PHP_EOL;
            }
        }
        return $code;
    }

    public function generateAfterReturning( JoinPoint $joinPoint )
    {
        $code = '';
        foreach ( $this->_getAdvicesForJoinPoint( $joinPoint ) as $advice )
        {
            if ( $advice instanceof AfterReturningAdvice )
            {
                $this->_interceptors[] = $advice->getName();

                $code .= '        $this->_aop_interceptor_instances__["' . $advice->getName() . '"]->invoke( $joinPoint );' . PHP_EOL;
            }
        }
        return $code;
    }

    private function _getAdvicesForJoinPoint( JoinPoint $joinPoint )
    {
        $advices = array();
        foreach ( $this->_aspects as $aspect )
        {
            foreach ( $aspect->getAdvices() as $advice )
            {
                if ( $advice->getPointcut()->match( $joinPoint, $this->_registry ) )
                {
                    $advices[] = $advice;
                }
            }
        }
        return $advices;
    }
}