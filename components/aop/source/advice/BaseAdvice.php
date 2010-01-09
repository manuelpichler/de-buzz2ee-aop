<?php
namespace de\buzz2ee\aop\advice;

use de\buzz2ee\aop\interfaces\Advice;
use de\buzz2ee\aop\interfaces\JoinPoint;
use de\buzz2ee\aop\interfaces\Pointcut;

abstract class BaseAdvice implements Advice
{
    /**
     * The associated pointcut instance.
     *
     * @var \de\buzz2ee\aop\interfaces\Pointcut
     */
    private $_pointcut = null;

    /**
     * Constructs a new advice instance.
     *
     * @param \de\buzz2ee\aop\interfaces\Pointcut $pointcut The associated pointcut.
     */
    public function __construct( Pointcut $pointcut )
    {
        $this->_pointcut = $pointcut;
    }

    public function getName()
    {
        return $this->_pointcut->getName();
    }

    /**
     *
     * @return \de\buzz2ee\aop\interfaces\Pointcut
     */
    public function getPointcut()
    {
        return $this->_pointcut;
    }

    public function __toString()
    {
        return get_class($this) . "::({$this->_pointcut->getName()})";
    }
}