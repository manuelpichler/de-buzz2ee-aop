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
    function myAdvice( interfaces\JoinPoint $joinPoint ) {
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
    public function myAfterReturningAdvice( interfaces\JoinPoint $joinPoint )
    {
        echo __METHOD__ . '(' . $joinPoint->getClassName() . '::' . $joinPoint->getMethodName() . ')' . PHP_EOL;
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
        echo __METHOD__, PHP_EOL;
    }
}

$container = new Container();
$container->registerAspect( MyAspect::TYPE );
$container->registerAspect( MyAspectTwo::TYPE );

$object = $container->createObject( MyClass::TYPE );
$object->foo();