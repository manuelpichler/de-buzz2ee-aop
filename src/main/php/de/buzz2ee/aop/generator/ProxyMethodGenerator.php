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

use de\buzz2ee\aop\ReflectionJoinPoint;

use de\buzz2ee\aop\interfaces\AdviceRegistry;

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
class ProxyMethodGenerator
{
    /**
     *
     * @var \de\buzz2ee\aop\interfaces\AdviceRegistry
     */
    private $adviceRegistry = null;
    
    /**
     *
     * @var \de\buzz2ee\aop\generator\ProxyParameterGenerator
     */
    private $_parameterGenerator = null;

    /**
     * @var \de\buzz2ee\aop\generator\AdviceCodeGenerator
     */
    private $_adviceCodeGenerator = null;

    public function __construct( AdviceRegistry $adviceRegistry, AdviceCodeGenerator $adviceGenerator )
    {
        $this->adviceRegistry       = $adviceRegistry;
        $this->_adviceCodeGenerator = $adviceGenerator;
        $this->_parameterGenerator  = new ProxyParameterGenerator();
    }

    public function generateConstructor( \ReflectionClass $class )
    {
        return '    public function __construct( \\' . $class->getName() . ' $target )' . PHP_EOL .
               '    {' . PHP_EOL .
               '        $this->____aop_set_interception_target( $target );' . PHP_EOL .
               '    }' . PHP_EOL .
               PHP_EOL;
    }

    public function generateMethod( \ReflectionMethod $method )
    {
        if ( $method->isAbstract() )
        {
            return;
        }
        if ( $method->isConstructor() )
        {
            return;
        }
        if ( $method->isDestructor() )
        {
            return;
        }
        if ( $method->isFinal() )
        {
            return;
        }
        if ( $method->isPrivate() )
        {
            return;
        }
        if ( $method->isStatic() )
        {
            return;
        }

        $joinPoint = new ReflectionJoinPoint( $method );
        if ( false === $this->adviceRegistry->getMatchingAdvices( $joinPoint )->hasAdvice() )
        {
            return  ;
        }

        $parameters = $this->_createParameters( $method->getParameters() );

        $code = '    ' .
                $joinPoint->getVisibility() . ' function ' . $method->getName() . '( ' . $parameters . ' )' . PHP_EOL .
                '    {' . PHP_EOL .
                $this->_adviceCodeGenerator->generateMethodInterceptionCode( $joinPoint, $method ) .
                '    }' . PHP_EOL .
                PHP_EOL;

        return $code;
    }

    private function _createParameters( array $parameters )
    {
        $code = array();
        foreach ( $parameters as $index => $parameter )
        {
            $code[] = $this->_parameterGenerator->generate( $parameter );
        }
        return join( ', ', $code );
    }
}