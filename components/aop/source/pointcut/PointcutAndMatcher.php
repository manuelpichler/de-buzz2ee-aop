<?php
/**
 * I provide completely working code within this framework, which may not be
 * developed any further, because there are already existing packages, which
 * try to provide similar functionallities.
 */

namespace de\buzz2ee\aop\pointcut;

use de\buzz2ee\aop\interfaces\JoinPoint;
use de\buzz2ee\aop\interfaces\PointcutRegistry;

/**
 * And binary matcher.
 *
 * @author  Manuel Pichler <mapi@pdepend.org>
 * @license Copyright by Manuel Pichler
 * @version $Revision$
 */
class PointcutAndMatcher extends PointcutBinaryMatcher
{
    const TYPE = __CLASS__;

    /**
     * @param JoinPoint        $joinPoint Currently inspectedt join point.
     * @param PointcutRegistry $registry  Registry will all available pointcuts
     *        in the actual container configuration.
     *
     * @return boolean
     */
    public function match( JoinPoint $joinPoint, PointcutRegistry $registry )
    {
        return $this->matchLeft( $joinPoint ) && $this->matchRight( $joinPoint );
    }
}