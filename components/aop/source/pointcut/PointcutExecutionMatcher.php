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

use de\buzz2ee\aop\interfaces\JoinPoint;
use de\buzz2ee\aop\interfaces\PointcutMatcher;
use de\buzz2ee\aop\interfaces\PointcutRegistry;

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
class PointcutExecutionMatcher implements PointcutMatcher
{
    const TYPE = __CLASS__;

    /**
     * @var string
     */
    private $_visibility = null;

    /**
     * @var string
     */
    private $_className = null;

    /**
     * @var string
     */
    private $_methodName = null;

    /**
     * @param string $className
     * @param string $methodName
     * @param string $visibility
     */
    public function __construct( $className, $methodName, $visibility )
    {
        $this->_className  = $className;
        $this->_methodName = $methodName;
        $this->_visibility = $visibility;
    }

    /**
     * @param JoinPoint        $joinPoint Currently inspectedt join point.
     * @param PointcutRegistry $registry  Registry will all available pointcuts
     *        in the actual container configuration.
     *
     * @return boolean
     */
    public function match( JoinPoint $joinPoint, PointcutRegistry $registry )
    {
        $pattern = sprintf( 
            '(^%s %s::%s$)',
            $this->_prepareRegexp( $this->_visibility === '' ? '*' : $this->_visibility ),
            $this->_prepareRegexp( $this->_prepareNamespaceDelimiter( $this->_className ) ),
            $this->_prepareRegexp( $this->_methodName )
        );
        $subject = sprintf( 
            '%s %s::%s',
            $joinPoint->getVisibility(),
            $this->_prepareNamespaceDelimiter( $joinPoint->getClassName() ),
            $joinPoint->getMethodName()
        );
        return ( preg_match( $pattern, $subject ) === 1 );
    }

    private function _prepareRegexp( $pattern )
    {
        return str_replace( '\\*', '.*', preg_quote( $pattern ) );
    }

    private function _prepareNamespaceDelimiter( $name )
    {
        return strtr( $name, '\\', '/' );
    }
}