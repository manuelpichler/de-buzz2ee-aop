<?php
namespace de\buzz2ee\aop\generator;

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

    public function __construct( PointcutRegistry $registry, array $aspects )
    {
        $this->_registry = $registry;
        $this->_aspects  = $aspects;
    }

    public function generateProlog( JoinPoint $joinPoint )
    {
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

        foreach ( $this->_aspects as $aspect )
        {
            foreach ( $aspect->getAdvices() as $advice )
            {
                if ( $advice instanceof BeforeAdvice && $advice->match( $joinPoint, $this->_registry ) )
                {
                    $code .= '        //$this->_advices["' . $advice->getName() . '"]->invoke( $joinPoint );' . PHP_EOL;
                }
            }
        }
        return $code;
    }

    public function generateProxyCode()
    {

    }
}