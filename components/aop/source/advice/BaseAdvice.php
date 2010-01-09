<?php
namespace de\buzz2ee\aop\advice;

use de\buzz2ee\aop\interfaces\Advice;
use de\buzz2ee\aop\pointcut\Pointcut;

abstract class BaseAdvice implements Advice
{
    /**
     * The associated pointcut instance.
     *
     * @var \de\buzz2ee\aop\pointcut\Pointcut
     */
    private $_pointcut = null;

    /**
     * Constructs a new advice instance.
     *
     * @param \de\buzz2ee\aop\pointcut\Pointcut $pointcut The associated pointcut.
     */
    public function __construct( Pointcut $pointcut )
    {
        $this->_pointcut = $pointcut;
    }

    public function match( JoinPoint $joinPoint, PointcutRegistry $registry )
    {
        return $this->_pointcut->match( $joinPoint, $registry );
    }

    public function getName()
    {
        return $this->_pointcut->getName();
    }

    public function __toString()
    {
        return get_class($this) . "::({$this->_pointcut->getName()})";
    }
}