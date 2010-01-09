<?php
namespace de\buzz2ee\aop;

use de\buzz2ee\aop\interfaces\JoinPoint;

class RuntimeJoinPoint implements JoinPoint
{
    private $_targetObject = null;
    
    private $_className = null;

    private $_methodName = null;

    private $_arguments = array();
    
    public function __construct( $targetObject, $className, $methodName, array $arguments )
    {
        $this->_targetObject = $targetObject;
        $this->_className    = $className;
        $this->_methodName   = $methodName;
        $this->_arguments    = $arguments;
    }
    
    public function getVisibility()
    {
        return 42;
    }

    public function getClassName()
    {
        $this->_className;
    }

    public function getMethodName()
    {
        $this->_methodName;
    }
}