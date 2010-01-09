<?php
/**
 * I provide completely working code within this framework, which may not be
 * developed any further, because there are already existing packages, which
 * try to provide similar functionallities.
 */

namespace de\buzz2ee\aop\pointcut;

use de\buzz2ee\aop\interfaces\JoinPoint;
use de\buzz2ee\aop\interfaces\PointcutMatcher;
use de\buzz2ee\aop\interfaces\PointcutRegistry;

/**
 * Named pointcut reference matcher implementation.
 *
 * @author  Manuel Pichler <mapi@pdepend.org>
 * @license Copyright by Manuel Pichler
 * @version $Revision$
 */
class PointcutNamedMatcher implements PointcutMatcher
{
    const TYPE = __CLASS__;

    private $_className = null;

    private $_methodName = null;

    private $_pointcutName = null;

    public function __construct( $className, $methodName )
    {
        $this->_className  = $className;
        $this->_methodName = $methodName;

        $this->_pointcutName = $className . '::' . $methodName . '()';
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
        return $registry->getPointcutByName( $this->_pointcutName )->match( $joinPoint, $registry );
    }
}