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
 * @package   de\buzz2ee\aop\interfaces
 * @author    Manuel Pichler <mapi@buzz2ee.de>
 * @copyright 2009-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://buzz2ee.de/
 */

namespace de\buzz2ee\aop\interfaces;

use de\buzz2ee\aop\interfaces\JoinPoint;
use de\buzz2ee\aop\interfaces\PointcutRegistry;

/**
 *
 *
 * @category  Components
 * @package   de\buzz2ee\aop\interfaces
 * @author    Manuel Pichler <mapi@buzz2ee.de>
 * @copyright 2009-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://buzz2ee.de/
 */
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