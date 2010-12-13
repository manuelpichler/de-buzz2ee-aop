<?php
namespace scanner\aspects;

use de\buzz2ee\aop\interfaces\JoinPoint;

/** 
 * @Aspect 
 */
class MyAspect
{
    const TYPE = __CLASS__;

    /**
     * @Pointcut("
     *     execution(
     *         *MyClass::foo()
     *     )
     * ")
     */
    function myPointcut() { }

    /**
     * @Before("\scanner\aspects\MyAspect::myPointcut()")
     */
    function myBeforeAdvice( JoinPoint $joinPoint ) {
        // echo __METHOD__ . '(' . $joinPoint->getClassName() . '::' . $joinPoint->getMethodName() . ')' . PHP_EOL;
    }

    /**
     * @After("\scanner\aspects\MyAspect::myPointcut()")
     */
    function myAfterAdvice( JoinPoint $joinPoint )
    {
        // echo __METHOD__ . '(' . $joinPoint->getClassName() . '::' . $joinPoint->getMethodName() . ')' . PHP_EOL;
    }
}