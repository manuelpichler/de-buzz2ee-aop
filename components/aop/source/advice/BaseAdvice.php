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
     * Name of the declaring aspect class.
     *
     * @var string
     */
    private $_aspectClassName = null;

    /**
     * Name of the declaring aspect method.
     *
     * @var string
     */
    private $_aspectMethodName = null;

    /**
     * Constructs a new advice instance.
     *
     * @param \de\buzz2ee\aop\interfaces\Pointcut $pointcut
     *        The pointcut instance that describes the join points where this
     *        advice will apply.
     * @param string $aspectMethodName
     *        The name of the declaring aspect method.
     * @param string $aspectClassName
     *        The name of the declaring aspect class.
     */
    public function __construct( Pointcut $pointcut, $aspectMethodName, $aspectClassName )
    {
        $this->_pointcut         = $pointcut;
        $this->_aspectClassName  = $aspectClassName;
        $this->_aspectMethodName = $aspectMethodName;
    }

    public function getAspectClassName()
    {
        return $this->_aspectClassName;
    }

    public function getAspectMethodName()
    {
        return $this->_aspectMethodName;
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