<?php
namespace scanner\aspects;

use de\buzz2ee\aop\interfaces\JoinPoint;
use de\buzz2ee\aop\interfaces\ProceedingJoinPoint;

/**
 * @Aspect
 */
class MyAspectTwo
{
    const TYPE = __CLASS__;

    /**
     * @AfterReturning("execution(* *MyClass::foo())")
     */
    public function myAfterReturningAdvice( JoinPoint $joinPoint )
    {
        // echo __METHOD__ . '(' . $joinPoint->getClassName() . '::' . $joinPoint->getMethodName() . ')' . PHP_EOL;
    }

    /**
     * @AfterThrowing("execution(* *MyClass::bar())")
     */
    public function myAfterThrowingAdvice( JoinPoint $joinPoint )
    {
        // echo __METHOD__ . '(' . $joinPoint->getClassName() . '::' . $joinPoint->getMethodName() . ')' . PHP_EOL;
    }

    /**
     * @Around("execution(* *MyClass::bar())")
     */
    public function myAroundAdvice( ProceedingJoinPoint $joinPoint )
    {
        // echo __METHOD__ . '(' . $joinPoint->getClassName() . '::' . $joinPoint->getMethodName() . ')' . PHP_EOL;
        $joinPoint->proceed();
    }
}