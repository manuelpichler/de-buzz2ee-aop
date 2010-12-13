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

use de\buzz2ee\aop\advice\Advices;
use de\buzz2ee\aop\advice\AfterAdvice;
use de\buzz2ee\aop\advice\AfterReturningAdvice;
use de\buzz2ee\aop\advice\AfterThrowingAdvice;
use de\buzz2ee\aop\advice\AroundAdvice;
use de\buzz2ee\aop\advice\BeforeAdvice;

use de\buzz2ee\aop\interfaces\Advice;
use de\buzz2ee\aop\interfaces\JoinPoint;
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
class AdviceCodeGenerator
{
    /**
     *
     * @var \de\buzz2ee\aop\generator\AdviceRegistry
     */
    private $_adviceRegistry = null;

    private $_interceptors = array();

    private $buffer = '';

    private $indent = 4;

    private $depth = 0;

    /**
     *
     * @var \de\buzz2ee\aop\generator\CodeGenerator
     */
    private $generator = null;

    public function __construct( AdviceRegistry $registry )
    {
        $this->_adviceRegistry = $registry;
        $this->generator = new CodeGenerator();
    }

    private function incrementDepth()
    {
        ++$this->depth;
        return $this;
    }

    private function decrementDepth()
    {
        --$this->depth;
        return $this;
    }

    private function append( $line = '' )
    {
        if ( $line ) {
            $this->buffer .= str_repeat( ' ', $this->indent * $this->depth ) . $line;
        }
        $this->buffer .= PHP_EOL;

        return $this;
    }

    private function flush()
    {
        $result = $this->buffer;

        $this->depth  = 0;
        $this->buffer = '';

        return $result;
    }

    public function generateClassInterceptCode()
    {
        $this->generator->indent()
            ->append( 'private $____aop_interception_target = null;' )
            ->append()
            ->append( 'private $____aop_interceptor_instances = array();' )
            ->append()
            ->append( 'private $____aop_interceptor_classes = array(' )
            ->indent();

        foreach ( array_unique( $this->_interceptors ) as $interceptor )
        {
            $this->generator->append( "'{$interceptor}' => '{$interceptor}'," );
        }

        $this->generator->outdent()
            ->append( ');' )
            ->append()
            ->appendMethod( '____aop_set_interception_target', 'private', array( '$target' ))
            ->append( '$this->____aop_interception_target = $target;' )
            ->appendMethodEnd()
            ->appendMethod( '____aop_get_interceptor_classes', 'public' )
            ->append( 'return $this->____aop_interceptor_classes;' )
            ->appendMethodEnd()
            ->appendMethod( '____aop_add_interceptor_instance', 'public', array( '$name', '$interceptor' ) )
            ->append( '$this->____aop_interceptor_instances[$name] = $interceptor;' )
            ->appendMethodEnd();

        return $this->generator->getCode();
    }

    public function generateMethodInterceptionCode( JoinPoint $joinPoint, \ReflectionMethod $method )
    {
        $advices = $this->_adviceRegistry->getMatchingAdvices( $joinPoint );

        $this->generator->indent()
            ->indent();

        $this->generateProlog( $joinPoint, $advices, $method );
        $this->generateBefore( $advices );
        $this->_generateTryCatchCode( $advices, $method );
        $this->generateAfterReturning( $advices );

        return $this->generator
            ->appendReturn( '$returnValue' )
            ->getCode();
    }

    public function generateProlog( JoinPoint $joinPoint, Advices $advices, \ReflectionMethod $method )
    {
        if ( $advices->hasAroundAdvice() )
        {
            $this->_generateProceedingJoinPointInitializationCode( $joinPoint, $advices, $method );
        }
        else if ( $advices->hasAdvice() )
        {
            $this->_generateJoinPointInitializationCode( $joinPoint, $advices, $method );
        }
    }

    private function _generateJoinPointInitializationCode( JoinPoint $joinPoint, Advices $advices, \ReflectionMethod $method )
    {
        $this->_generateJoinPointConstructorInvocationCode( $joinPoint, $method, '\de\buzz2ee\aop\RuntimeJoinPoint' );
    }

    private function _generateProceedingJoinPointInitializationCode( JoinPoint $joinPoint, Advices $advices, \ReflectionMethod $method )
    {
        $this->_generateJoinPointConstructorInvocationCode( $joinPoint, $method, '\de\buzz2ee\aop\RuntimeProceedingJoinPoint' );

        $this->generator->append( '$joinPoint->setAdviceChain(' )
            ->indent()
            ->append( 'array(' );

        foreach ( $advices->getAroundAdvices() as $advice )
        {
            $this->_interceptors[] = $advice->getAspectClassName();
        
            $this->generator->append( "array( \$this->____aop_interceptor_instances['{$advice->getAspectClassName()}'], '{$advice->getAspectMethodName()}')," );
        }

        $this->generator->append( ')' )
            ->outdent()
            ->append( ');' )
            ->append();
    }

    private function _generateJoinPointConstructorInvocationCode( JoinPoint $joinPoint, \ReflectionMethod $method, $className )
    {
        $this->generator->append( "\$joinPoint = new {$className}(" )
            ->indent()
            ->append( '$this->____aop_interception_target,' )
            ->append( '$this,' )
            ->append( "'{$joinPoint->getClassName()}'," )
            ->append( "'{$joinPoint->getMethodName()}'," )
            ->append( $this->generateArgumentsArrayCode( $method ) )
            ->outdent()
            ->append( ');' );
    }

    private function generateBefore( Advices $advices )
    {
        foreach ( $advices->getBeforeAdvices() as $advice )
        {
            $this->_generateAdviceInvocationCode( $advice );
        }
    }

    private function _generateTryCatchCode( Advices $advices, \ReflectionMethod $method )
    {
        if ( $advices->hasAfterAdvice() || $advices->hasAfterThrowingAdvice() )
        {
            $this->generator->appendTry();

            $this->_generateAroundAdviceCode( $advices, $method );
            $this->generateAfter( $advices );

            $this->generator->appendCatch( '\Exception', '$e' );

            $this->generateAfterThrowing( $advices );
            $this->generateAfter( $advices );

            $this->generator
                ->append( 'throw $e;' )
                ->appendTryEnd();
        } else {
            $this->_generateAroundAdviceCode( $advices, $method );
        }
    }

    private function _generateAroundAdviceCode( Advices $advices, \ReflectionMethod $method )
    {
        if ( $advices->hasAroundAdvice() )
        {
            $this->generator->append( '$returnValue = $joinPoint->proceed();' );
        } else {
            $this->_generateTargetMethodInvocationCode( $method );
        }
    }

    private function _generateTargetMethodInvocationCode( \ReflectionMethod $method )
    {
        $this->generator
            ->append( '$returnValue = $this->____aop_interception_target->' . $method->getName() . '(' )
            ->indent()
            ->append( $this->generateMethodInvocationParameterCode( $method ) )
            ->outdent()
            ->append( ');' );
    }

    private function generateMethodInvocationParameterCode( \ReflectionMethod $method )
    {
        if ( 0 === count( $method->getParameters() ) )
        {
            return '';
        }
        $code = array();
        foreach ( $method->getParameters() as $parameter )
        {
            $code[] = $parameter->getName();
        }
        return ' $' . join( ', $', $code ) . ' ';
    }

    private function generateArgumentsArrayCode( \ReflectionMethod $method )
    {
        return 'array(' . $this->generateMethodInvocationParameterCode( $method ) . ')';
    }

    private function generateAfter( Advices $advices )
    {
        foreach ( $advices->getAfterAdvices() as $advice )
        {
            $this->_generateAdviceInvocationCode( $advice );
        }
    }

    private function generateAfterThrowing( Advices $advices )
    {
        foreach ( $advices->getAfterThrowingAdvices() as $advice )
        {
            $this->_generateAdviceInvocationCode( $advice );
        }
    }

    private function generateAfterReturning( Advices $advices )
    {
        foreach ( $advices->getAfterReturningAdvices() as $advice )
        {
            $this->_generateAdviceInvocationCode( $advice );
        }
    }

    private function _generateAdviceInvocationCode( Advice $advice )
    {
        $this->_interceptors[] = $advice->getAspectClassName();

        $this->generator->append( '$this->____aop_interceptor_instances[\'' . $advice->getAspectClassName() . '\']->' . $advice->getAspectMethodName() . '( $joinPoint );' );
    }
}

class CodeGenerator
{
    private $indent = '    ';

    private $depth = 0;

    /**
     *
     * @var \de\buzz2ee\aop\generator\Stream
     */
    private $stream = null;

    public function __construct()
    {
        $this->stream = new \de\buzz2ee\aop\generator\MemoryStream();
    }

    public function getCode()
    {
        $this->depth = 0;

        $code = $this->stream->getBuffer();
        $this->stream->flush();

        return $code;
    }

    public function indent()
    {
        ++$this->depth;
        return $this;
    }

    public function outdent()
    {
        --$this->depth;
        return $this;
    }

    public function appendTry()
    {
        return $this->append( 'try' )
            ->append( '{' )
            ->indent();
    }

    public function appendTryEnd()
    {
        return $this->outdent()
            ->append( '}' );
    }

    public function appendCatch( $class = '\Exception', $name = '$e' )
    {
        return $this->outdent()
            ->append( '}' )
            ->append( "catch ( {$class} {$name} )" )
            ->append( '{' )
            ->indent();
    }

    public function appendMethod( $name, $visibilty, array $params = array() )
    {
        $code = $this->getParameters( $params );
        return $this->append( "{$visibilty} function {$name}({$code})" )
            ->append( '{' )
            ->indent();
    }

    public function appendMethodEnd()
    {
        return $this->outdent()
            ->append( '}' );
    }

    public function appendReturn( $code )
    {
        return $this->append( 'return ' . $code . ';' );
    }

    public function append( $code = '' )
    {
        $this->stream->write( $this->indentCode( $code ) );
        return $this;
    }

    private function indentCode( $code )
    {
        return $this->getIndent() . $code;
    }

    private function getIndent()
    {
        return str_repeat( $this->indent, $this->depth );
    }

    private function getParameters( array $params )
    {
        if ( count( $params ) > 0 )
        {
            return ' ' . join( ', ', $params ) . ' ';
        }
        return '';
    }
}

interface Stream
{
    function write( $line );

    function flush();
}

class MemoryStream implements Stream
{
    private $buffer = '';

    public function write( $line )
    {
        if ( $line )
        {
            $this->buffer .= $line;
        }
        $this->buffer .= PHP_EOL;
    }

    public function flush()
    {
        $result = $this->buffer;

        $this->depth  = 0;
        $this->buffer = '';

        return $result;
    }

    public function getBuffer()
    {
        return $this->buffer;
    }
}