<?php
namespace de\buzz2ee\aop;

use de\buzz2ee\aop\interfaces\JoinPoint;

class ReflectionJoinPoint implements JoinPoint
{
    /**
     *
     * @var \ReflectionMethod
     */
    private $_method = null;

    public function __construct( \ReflectionMethod $method )
    {
        $this->_method = $method;
    }

    public function getVisibility()
    {
        if ( $this->_method->isPrivate() )
        {
            return 'private';
        }
        else if ( $this->_method->isProtected() )
        {
            return 'protected';
        }
        return 'public';
    }

    public function getClassName()
    {
        return $this->_method->getDeclaringClass()->getName();
    }

    public function getMethodName()
    {
        return $this->_method->getName();
    }
}