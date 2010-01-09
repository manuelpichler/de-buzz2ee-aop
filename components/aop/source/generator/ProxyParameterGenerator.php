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
 * @package   de\buzz2ee\aop\generator
 * @author    Manuel Pichler <mapi@buzz2ee.de>
 * @copyright 2009-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://buzz2ee.de/
 */

namespace de\buzz2ee\aop\generator;

/**
 *
 *
 * @category  Components
 * @package   de\buzz2ee\aop\generator
 * @author    Manuel Pichler <mapi@buzz2ee.de>
 * @copyright 2009-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://buzz2ee.de/
 */
class ProxyParameterGenerator
{
    /**
     * This method generates php code that reflects all aspects of the given
     * parameter instance.
     *
     * @param \ReflectionParameter $parameter
     *        The context parameter instance that must be rendered as php code.
     *
     * @return string
     */
    public function generate( \ReflectionParameter $parameter )
    {
        return $this->_generateOptionalTypeHint( $parameter ) .
               $this->_generateOptionalPassedByRef( $parameter ) .
               '$' . $parameter->getName() .
               $this->_generateOptionalDefaultValue( $parameter );
    }

    /**
     * Generates the type hint for the given parameter or returns an empty
     * string when the parameter has no type hint.
     *
     * @param \ReflectionParameter $parameter
     *        The context parameter instance that must be rendered as php code.
     *
     * @return string
     */
    private function _generateOptionalTypeHint( \ReflectionParameter $parameter )
    {
        if ( $parameter->isArray() )
        {
            return 'array ';
        }
        else if ( is_object( $parameter->getClass() ) )
        {
            return '\\' . $parameter->getClass()->getName() . ' ';
        }
        return '';
    }

    /**
     * Renders the php reference operator <b>&</b> when the given parameter is
     * passed by reference, otherwise this method will return an empty string.
     *
     * @param \ReflectionParameter $parameter
     *        The context parameter instance that must be rendered as php code.
     *
     * @return string
     */
    private function _generateOptionalPassedByRef( \ReflectionParameter $parameter )
    {
        if ( $parameter->isPassedByReference() )
        {
            return '&';
        }
        return '';
    }

    /**
     * Renders the php code for the default value of the given method parameter.
     * It will return an empty string when the given parameter does not define
     * a default value.
     *
     * @param \ReflectionParameter $parameter
     *        The context parameter instance that must be rendered as php code.
     *
     * @return string
     */
    private function _generateOptionalDefaultValue( \ReflectionParameter $parameter )
    {
        if ( $parameter->isDefaultValueAvailable() )
        {
            return ' = ' . var_export( $parameter->getDefaultValue(), true );
        }
        return '';
    }
}