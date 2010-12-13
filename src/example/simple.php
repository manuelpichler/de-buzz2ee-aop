#!/usr/bin/env php
<?php
namespace de\buzz2ee\aop;

spl_autoload_register(
    function($className) {
        if (strpos($className, __NAMESPACE__) === 0) {
            include __DIR__ . '/../main/php/' . strtr( $className, '\\', '/' ) . '.php';
        }
    }
);

use de\buzz2ee\aop\generator\ProxyGeneratorFactory;
use de\buzz2ee\aop\interfaces\JoinPoint;
use de\buzz2ee\aop\interfaces\ProceedingJoinPoint;

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
        // echo __METHOD__ . '(' . $joinPoint->getClassName() . '::' . $joinPoint->getMethodName() . ')' . PHP_EOL;
    }

    /**
     * @After("\de\buzz2ee\aop\MyAspect::myPointcut()")
     */
    function myAfterAdvice( JoinPoint $joinPoint )
    {
        // echo __METHOD__ . '(' . $joinPoint->getClassName() . '::' . $joinPoint->getMethodName() . ')' . PHP_EOL;
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
     * @Around("\de\buzz2ee\aop\MyThirdAspect::myPointcut()")
     */
    public function myFirstAroundAdvice( ProceedingJoinPoint $joinPoint )
    {
        return 1 + $joinPoint->proceed();
    }

    /**
     * @Around("\de\buzz2ee\aop\MyThirdAspect::myPointcut()")
     */
    public function mySecondAroundAdvice( ProceedingJoinPoint $joinPoint )
    {
        return 1 + $joinPoint->proceed();
    }

    /**
     * @Around("\de\buzz2ee\aop\MyThirdAspect::myPointcut()")
     */
    public function myThirdAroundAdvice( ProceedingJoinPoint $joinPoint )
    {
        return 1 + $joinPoint->proceed();
    }
}

class MyClass extends \stdClass
{
    const TYPE = __CLASS__;

    function foo()
    {
        // echo __METHOD__, PHP_EOL;
    }

    function bar( MyAspect $aspect )
    {
        throw new \Exception( __METHOD__ );
    }
    
    function baz( \stdClass $class )
    {
        return 42;
    }
}

// Just for this benchmark, pre include stuff
class_exists('\de\buzz2ee\aop\RuntimeProceedingJoinPoint', true);
class_exists('\de\buzz2ee\aop\RuntimeJoinPoint', true);

//sleep(2);

$adviceRegistry = new DefaultAdviceRegistry();
$adviceRegistry->registerAspect( MyAspect::TYPE );
$adviceRegistry->registerAspect( MyAspectTwo::TYPE );
$adviceRegistry->registerAspect( MyThirdAspect::TYPE );

$container = new Container();
$container->setGeneratorFactory( new ProxyGeneratorFactory( $adviceRegistry ) );
$container->setAdviceRegistry( $adviceRegistry );

$count = 50000;

$object0 = new MyClass();

// START: ======================================================================
$start = microtime(true);

for ($i = 0; $i < $count; ++$i) {
    $object0->foo();

//    try {
//        $object->bar( new MyAspect() );
//    } catch ( \Exception $e ) {
//        // echo $e->getFile(), ' +', $e->getLine(), PHP_EOL;
//    }
    $object0->baz( new \stdClass() );
}

printf("===========================\nTotal time:      %.8f\n===========================\n", ( ( $v0 = microtime( true ) - $start ) / $count ) );
// END: ========================================================================

$object1 = $container->createObject( MyClass::TYPE );

// START: ======================================================================
$start = microtime(true);
//echo "foo()\n===========================\n";

for ($i = 0; $i < $count; ++$i) {
//    $s0 = microtime(true);
    $object1->foo();
//    printf( "Outer benchmark: %.8f\n", (microtime(true) - $s0));
//    try {
//        $object->bar( new MyAspect() );
//    } catch ( \Exception $e ) {
//        // echo $e->getFile(), ' +', $e->getLine(), PHP_EOL;
//    }
//
//    echo "===========================\nbaz()\n===========================\n";
//    $s0 = microtime(true);
    $object1->baz( new \stdClass() );
//    printf( "Outer benchmark: %.8f\n", (microtime(true) - $s0));
}

printf("===========================\nTotal time:      %.8f\n===========================\n", ( ( $v1 = microtime( true ) - $start ) / $count ) );
// END: ========================================================================


printf ( "Proxy vs. POPO %.2f%%\n", ( $v1 / ( $v0 / 100 ) ) );

$x = ( $object0 === $object1 ? 1 : 0);
exit( $x );
