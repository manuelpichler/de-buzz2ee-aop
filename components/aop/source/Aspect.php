<?php
namespace de\buzz2ee\aop;

class Aspect
{
    private $_className = null;

    private $_pointcuts = array();

    private $_advices = array();

    public function __construct( $className )
    {
        $this->_className = $className;
    }

    public function getClassName()
    {
        return $this->_className;
    }

    public function getPointcuts()
    {
        return $this->_pointcuts;
    }

    public function addPointcut( pointcut\Pointcut $pointcut )
    {
        $this->_pointcuts[] = $pointcut;
    }

    public function getAdvices()
    {
        return $this->_advices;
    }

    public function addAdvice( Advice $advice )
    {
        $this->_advices[] = $advice;
    }
}