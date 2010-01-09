<?php
namespace de\buzz2ee\aop\interfaces;

interface PointcutRegistry
{
    /**
     * 
     *
     * @param string $pointcutName The qualified pointcut identifier.
     *
     * @return \de\buzz2ee\aop\pointcut\Pointcut
     */
    function getPointcutByName( $pointcutName );
}