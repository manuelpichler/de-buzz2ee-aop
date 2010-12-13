<?php
namespace scanner\aspects;

use de\buzz2ee\aop\interfaces\ProceedingJoinPoint;

/**
 * @Aspect
 */
class MyThirdAspect
{
    const TYPE = __CLASS__;

    /**
     * @Pointcut("
     *     execution(
     *         *MyClass::baz()
     *     )
     * ")
     */
    function myPointcut() {}

    /**
     * @Around("\scanner\aspects\MyThirdAspect::myPointcut()")
     */
    public function myFirstAroundAdvice( ProceedingJoinPoint $joinPoint )
    {
        return 1 + $joinPoint->proceed();
    }

    /**
     * @Around("\scanner\aspects\MyThirdAspect::myPointcut()")
     */
    public function mySecondAroundAdvice( ProceedingJoinPoint $joinPoint )
    {
        return 1 + $joinPoint->proceed();
    }

    /**
     * @Around("\scanner\aspects\MyThirdAspect::myPointcut()")
     */
    public function myThirdAroundAdvice( ProceedingJoinPoint $joinPoint )
    {
        return 1 + $joinPoint->proceed();
    }
}