<?php
namespace de\buzz2ee\aop\interfaces;

use de\buzz2ee\aop\interfaces\JoinPoint;
use de\buzz2ee\aop\interfaces\PointcutRegistry;

interface Pointcut
{
    /**
     * Returns the qualified name of this pointcut instance.
     *
     * Qualified name means that it is combination of the aspect's class name,
     * including its namespace, and the name of the method where this pointcut
     * was defined. Class and method name are separated by a double colon and
     * the name ends with opening and closing parenthesis.
     *
     * For the following example:
     *
     * <code>
     * namespace com\example;
     *
     * class Aspect
     * {
     *     // Note: Inline comment aren't recognized by the aop component, they
     *     //       are only used in this example.
     *     // @Pointcut("public *::execute()")
     *     public function adviceExecute() {}
     * }
     * </code>
     *
     * The pointcut name will look like:
     *
     * <code>
     * com\example\Aspect::adviceExecute()
     * </code>
     *
     * @return string
     */
    function getName();

    /**
     * This method test if this pointcut instances matches on the given join
     * point instance. On success it will return <b>true</b>, otherwise it
     * returns <b>false</b>.
     *
     * @param \de\buzz2ee\aop\interfaces\JoinPoint $joinPoint
     *        A currently inspected join point that maybe matched by this
     *        pointcut instance.
     * @param \de\buzz2ee\aop\interfaces\PointcutRegistry $registry
     *        Registry that holds all named pointcuts available in the actual
     *        application context. For example, it can be used to retrieve
     *        pointcuts referenced by their name.
     *
     * @return boolean
     */
    function match( JoinPoint $joinPoint, PointcutRegistry $registry );
}