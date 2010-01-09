<?php
namespace de\buzz2ee\aop;

use de\buzz2ee\aop\interfaces\Advice;
use de\buzz2ee\aop\interfaces\Pointcut;

class Aspect
{
    /**
     * Name of the concrete aspect class.
     *
     * @var string
     */
    private $_className = null;

    /**
     * Pointcuts defined in this aspect.
     *
     * @var array(\de\buzz2ee\aop\interfaces\Pointcut)
     */
    private $_pointcuts = array();

    /**
     * Advices that declared by this aspect.
     *
     * @var array(\de\buzz2ee\aop\interfaces\Advice)
     */
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

    public function addPointcut( Pointcut $pointcut )
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