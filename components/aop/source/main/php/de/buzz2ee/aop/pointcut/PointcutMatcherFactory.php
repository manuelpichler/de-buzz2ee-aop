<?php
/**
 * This file is part of the aspect oriented programming component.
 *
 * PHP Version 5
 *
 * Copyright (c) 2009-2010, Manuel Pichler <mapi@buzz2ee.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  Components
 * @package   de\buzz2ee\aop\pointcut
 * @author    Manuel Pichler <mapi@buzz2ee.de>
 * @copyright 2009-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://buzz2ee.de/
 */

namespace de\buzz2ee\aop\pointcut;

use \de\buzz2ee\aop\interfaces\PointcutMatcher;

/**
 *
 *
 * @category  Components
 * @package   de\buzz2ee\aop\pointcut
 * @author    Manuel Pichler <mapi@buzz2ee.de>
 * @copyright 2009-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://buzz2ee.de/
 */
class PointcutMatcherFactory
{
    const TYPE = __CLASS__;

    /**
     * @var PointcutMatcherFactory
     */
    private static $_factory = null;

    /**
     * @return PointcutMatcherFactory
     */
    public static function get()
    {
        if ( self::$_factory === null )
        {
            throw new \RuntimeException( 'No PointcutMatcherFactory configured.' );
        }
        return self::$_factory;
    }

    /**
     * @param PointcutMatcherFactory $factory
     *
     * @return void
     */
    public static function set( PointcutMatcherFactory $factory = null )
    {
        self::$_factory = $factory;
    }

    /**
     * @param string $className
     * @param string $methodName
     * @param string $visibility
     *
     * @return PointcutExecutionMatcher
     */
    public function createExecutionMatcher( $className, $methodName, $visibility )
    {
        return new PointcutExecutionMatcher( $className, $methodName, $visibility );
    }

    /**
     * @param string $className
     * @param string $methodName
     *
     * @return PointcutNamedMatcher
     */
    public function createNamedMatcher( $className, $methodName )
    {
        return new PointcutNamedMatcher( $className, $methodName );
    }

    /**
     * @param PointcutMatcher $matcher
     *
     * @return PointcutNotMatcher
     */
    public function createNotMatcher( PointcutMatcher $matcher )
    {
        return new PointcutNotMatcher( $matcher );
    }

    /**
     * @param PointcutMatcher $left
     * @param PointcutMatcher $right
     *
     * @return PointcutAndMatcher
     */
    public function createAndMatcher( PointcutMatcher $left, PointcutMatcher $right )
    {
        return new PointcutAndMatcher( $left, $right );
    }

    /**
     * @param PointcutMatcher $left
     * @param PointcutMatcher $right
     *
     * @return PointcutOrMatcher
     */
    public function createOrMatcher( PointcutMatcher $left, PointcutMatcher $right )
    {
        return new PointcutOrMatcher( $left, $right );
    }
}