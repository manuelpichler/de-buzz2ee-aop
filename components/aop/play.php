#!/usr/bin/env php
<?php
namespace de\buzz2ee\aop;

spl_autoload_register(
    function($className) {
        if (strpos($className, __NAMESPACE__) === 0) {
            include __DIR__ . '/source/' . strtr( substr( $className, strlen( __NAMESPACE__ ) + 1 ), '\\', '/' ) . '.php';
        }
    }
);

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
    function myPointcut() {}

    /**
     * @Before("\de\buzz2ee\aop\MyAspect::myPointcut()")
     */
    function myBeforeAdvice( JoinPoint $joinPoint ) {
        echo __METHOD__ . '(' . $joinPoint->getClassName() . '::' . $joinPoint->getMethodName() . ')' . PHP_EOL;
    }

    /**
     * @After("\de\buzz2ee\aop\MyAspect::myPointcut()")
     */
    function myAfterAdvice( JoinPoint $joinPoint )
    {
        echo __METHOD__ . '(' . $joinPoint->getClassName() . '::' . $joinPoint->getMethodName() . ')' . PHP_EOL;
    }
}

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
        echo __METHOD__ . '(' . $joinPoint->getClassName() . '::' . $joinPoint->getMethodName() . ')' . PHP_EOL;
    }

    /**
     * @AfterThrowing("execution(* *MyClass::bar())")
     */
    public function myAfterThrowingAdvice( JoinPoint $joinPoint )
    {
        echo __METHOD__ . '(' . $joinPoint->getClassName() . '::' . $joinPoint->getMethodName() . ')' . PHP_EOL;
    }

    /**
     * @Around("execution(* *MyClass::bar())")
     */
    public function myAroundAdvice( interfaces\ProceedingJoinPoint $joinPoint )
    {
        echo __METHOD__ . '(' . $joinPoint->getClassName() . '::' . $joinPoint->getMethodName() . ')' . PHP_EOL;
        $joinPoint->proceed();
    }
}

class MyClass extends \stdClass
{
    const TYPE = __CLASS__;

    function foo()
    {
        echo __METHOD__, PHP_EOL;
    }

    function bar( MyAspect $aspect )
    {
        throw new \Exception( __METHOD__ );
    }
}

$container = new Container();
$container->registerAspect( MyAspect::TYPE );
$container->registerAspect( MyAspectTwo::TYPE );

$object = $container->createObject( MyClass::TYPE );
$object->foo();

try {
    $object->bar( new MyAspect() );
} catch ( \Exception $e ) {}