<?php
namespace de\buzz2ee\aop\interfaces;

interface Advice
{
    /**
     * Returns the class name of the declaring aspect.
     *
     * @return string
     */
    function getAspectClassName();

    /**
     * Returns the aspect method name that declares this advice.
     *
     * <code>
     * // @Aspect
     * class Foo
     *
     * </code>
     *
     * @return string
     */
    function getAspectMethodName();
}