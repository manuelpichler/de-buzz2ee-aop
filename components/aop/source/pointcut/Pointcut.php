<?php
namespace de\buzz2ee\aop\pointcut;

use de\buzz2ee\aop\interfaces\JoinPoint;
use de\buzz2ee\aop\interfaces\PointcutMatcher;
use de\buzz2ee\aop\interfaces\PointcutRegistry;

class Pointcut
{
    /**
     *
     * @var string
     */
    private $_name = null;

    /**
     *
     * @var de\buzz2ee\aop\interfaces\PointcutMatcher
     */
    private $_matcher = null;

    public function __construct( $name, PointcutMatcher $matcher )
    {
        $this->_name    = $name;
        $this->_matcher = $matcher;
    }

    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param JoinPoint        $joinPoint Currently inspectedt join point.
     * @param PointcutRegistry $registry  Registry will all available pointcuts
     *        in the actual container configuration.
     *
     * @return boolean
     */
    public function match( JoinPoint $joinPoint, PointcutRegistry $registry )
    {
        return $this->_matcher->match( $joinPoint, $registry );
    }
}