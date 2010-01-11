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

require_once 'BaseTest.php';

/**
 * Test case for the parameter proxy generator class.
 *
 * @category  Components
 * @package   de\buzz2ee\aop\generator
 * @author    Manuel Pichler <mapi@buzz2ee.de>
 * @copyright 2009-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://buzz2ee.de/
 */
class ProxyParameterGeneratorTest extends \de\buzz2ee\aop\BaseTest
{
    /**
     * testGenerateParameterWithoutTypeHintAndDefaultValue
     *
     * @return void
     * @covers \de\buzz2ee\aop\generator\ProxyParameterGenerator
     * @group aop
     * @group aop::generator
     * @group unittest
     */
    public function testGenerateParameterWithoutTypeHintAndDefaultValue()
    {
        $generator = new ProxyParameterGenerator();

        $code = $generator->generate( $this->createParameter( 'name' ) );
        $this->assertEquals( '$name', $code );
    }

    /**
     * testGenerateParameterPassedByReference
     *
     * @return void
     * @covers \de\buzz2ee\aop\generator\ProxyParameterGenerator
     * @group aop
     * @group aop::generator
     * @group unittest
     */
    public function testGenerateParameterPassedByReference()
    {
        $generator = new ProxyParameterGenerator();

        $parameter = $this->createParameter( 'name' );
        $parameter->expects( $this->once() )
            ->method( 'isPassedByReference' )
            ->will( $this->returnValue( true ) );

        $code = $generator->generate( $parameter );
        $this->assertEquals( '&$name', $code );
    }
    
    /**
     * testGenerateParameterWithoutTypeHintAndFloatDefaultValue
     * 
     * @return void
     * @covers \de\buzz2ee\aop\generator\ProxyParameterGenerator
     * @group aop
     * @group aop::generator
     * @group unittest
     */
    public function testGenerateParameterWithoutTypeHintAndFloatDefaultValue()
    {
        $generator = new ProxyParameterGenerator();

        $parameter = $this->createParameter( 'name' );
        $parameter->expects( $this->once() )
            ->method( 'isDefaultValueAvailable' )
            ->will( $this->returnValue( true ) );
        $parameter->expects( $this->once() )
            ->method( 'getDefaultValue' )
            ->will( $this->returnValue( 3.14 ) );

        $code = $generator->generate( $parameter );
        $this->assertEquals( '$name = 3.14', $code );
    }
    
    /**
     * testGenerateParameterWithoutTypeHintAndArrayDefaultValue
     * 
     * @return void
     * @covers \de\buzz2ee\aop\generator\ProxyParameterGenerator
     * @group aop
     * @group aop::generator
     * @group unittest
     */
    public function testGenerateParameterWithoutTypeHintAndArrayDefaultValue()
    {
        $generator = new ProxyParameterGenerator();

        $parameter = $this->createParameter( 'name' );
        $parameter->expects( $this->once() )
            ->method( 'isDefaultValueAvailable' )
            ->will( $this->returnValue( true ) );
        $parameter->expects( $this->once() )
            ->method( 'getDefaultValue' )
            ->will( $this->returnValue( array( 'foo' => 'bar', 23 => 42 ) ) );

        $code = $generator->generate( $parameter );
        $this->assertEquals( "\$name = array (
  'foo' => 'bar',
  23 => 42,
)",
            $code
        );
    }

    /**
     * testGenerateParameterWithoutArrayTypeHintAndWithoutDefaultValue
     *
     * @return void
     * @covers \de\buzz2ee\aop\generator\ProxyParameterGenerator
     * @group aop
     * @group aop::generator
     * @group unittest
     */
    public function testGenerateParameterWithoutArrayTypeHintAndWithoutDefaultValue()
    {
        $generator = new ProxyParameterGenerator();

        $parameter = $this->createParameter( 'name' );
        $parameter->expects( $this->once() )
            ->method( 'isArray' )
            ->will( $this->returnValue( true ) );

        $code = $generator->generate( $parameter );
        $this->assertEquals( 'array $name', $code );
    }

    /**
     * testGenerateParameterWithoutArrayTypeHintAndArrayDefaultValue
     *
     * @return void
     * @covers \de\buzz2ee\aop\generator\ProxyParameterGenerator
     * @group aop
     * @group aop::generator
     * @group unittest
     */
    public function testGenerateParameterWithoutArrayTypeHintAndArrayDefaultValue()
    {
        $generator = new ProxyParameterGenerator();

        $parameter = $this->createParameter( 'name' );
        $parameter->expects( $this->once() )
            ->method( 'isArray' )
            ->will( $this->returnValue( true ) );
        $parameter->expects( $this->once() )
            ->method( 'isDefaultValueAvailable' )
            ->will( $this->returnValue( true ) );
        $parameter->expects( $this->once() )
            ->method( 'getDefaultValue' )
            ->will( $this->returnValue( array() ) );

        $code = $generator->generate( $parameter );
        $this->assertEquals( 'array $name = array (
)', 
            $code
        );
    }

    /**
     * testGenerateParameterWithoutArrayTypeHintAndNullDefaultValue
     *
     * @return void
     * @covers \de\buzz2ee\aop\generator\ProxyParameterGenerator
     * @group aop
     * @group aop::generator
     * @group unittest
     */
    public function testGenerateParameterWithoutArrayTypeHintAndNullDefaultValue()
    {
        $generator = new ProxyParameterGenerator();

        $parameter = $this->createParameter( 'name' );
        $parameter->expects( $this->once() )
            ->method( 'isArray' )
            ->will( $this->returnValue( true ) );
        $parameter->expects( $this->once() )
            ->method( 'isDefaultValueAvailable' )
            ->will( $this->returnValue( true ) );
        $parameter->expects( $this->once() )
            ->method( 'getDefaultValue' )
            ->will( $this->returnValue( null ) );

        $code = $generator->generate( $parameter );
        $this->assertEquals( 'array $name = NULL', $code );
    }

    /**
     * testGenerateParameterWithoutClassTypeHintAndWithoutDefaultValue
     *
     * @return void
     * @covers \de\buzz2ee\aop\generator\ProxyParameterGenerator
     * @group aop
     * @group aop::generator
     * @group unittest
     */
    public function testGenerateParameterWithoutClassTypeHintAndWithoutDefaultValue()
    {
        $generator = new ProxyParameterGenerator();

        $parameter = $this->createParameter( 'name' );
        $parameter->expects( $this->any() )
            ->method( 'getClass' )
            ->will( $this->returnValue( new \ReflectionClass( __CLASS__ ) ) );

        $code = $generator->generate( $parameter );
        $this->assertEquals( '\\' . __CLASS__ . ' $name', $code );
    }

    /**
     * testGenerateParameterWithoutClassTypeHintAndWithoutDefaultValue
     *
     * @return void
     * @covers \de\buzz2ee\aop\generator\ProxyParameterGenerator
     * @group aop
     * @group aop::generator
     * @group unittest
     */
    public function testGenerateParameterWithoutClassTypeHintAndNullDefaultValue()
    {
        $generator = new ProxyParameterGenerator();

        $parameter = $this->createParameter( 'name' );
        $parameter->expects( $this->any() )
            ->method( 'getClass' )
            ->will( $this->returnValue( new \ReflectionClass( __CLASS__ ) ) );
        $parameter->expects( $this->once() )
            ->method( 'isDefaultValueAvailable' )
            ->will( $this->returnValue( true ) );
        $parameter->expects( $this->once() )
            ->method( 'getDefaultValue' )
            ->will( $this->returnValue( null ) );

        $code = $generator->generate( $parameter );
        $this->assertEquals( '\\' . __CLASS__ . ' $name = NULL', $code );
    }

    /**
     * Creates a mocked reflection parameter instance.
     *
     * @param string $name The parameter name.
     *
     * @return \ReflectionParameter
     */
    protected function createParameter( $name )
    {
        $parameter = $this->getMock( '\ReflectionParameter', array(), array( null, 0 ), '', false );
        $parameter->expects( $this->any() )
            ->method( 'getName' )
            ->will( $this->returnValue( $name ) );

        return $parameter;
    }
}