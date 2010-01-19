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
class ProxyClassGenerator
{
    /**
     * Generator used to generate the real advice code.
     *
     * @var \de\buzz2ee\aop\generator\AdviceCodeGenerator
     */
    private $_adviceGenerator = null;

    /**
     * Generator used to generate all proxy methods of the adviced class.
     *
     * @var \de\buzz2ee\aop\generator\ProxyMethodGenerator
     */
    private $_methodGenerator = null;

    public function  __construct(
        AdviceCodeGenerator $adviceGenerator,
        ProxyMethodGenerator $methodGenerator
    ) {
        $this->_adviceGenerator      = $adviceGenerator;
        $this->_methodGenerator      = $methodGenerator;
    }

    public function create( $className )
    {
        $position = strrpos( $className, '\\' );

        if ( is_int( $position ) )
        {
            $proxyName     = substr( $className, $position + 1 ) . '____AOPProxy';
            $namespaceName = substr( $className, 0, $position + 1 );
        }
        else
        {
            $proxyName     = $className . '____AOPProxy';
            $namespaceName = '';
        }

        $fileName  = getcwd() . '/' . strtr( $className, '\\', '_' ) . '.php';

        if ( true || file_exists( $fileName ) === false )
        {
            $code = $this->_createClass( $proxyName, new \ReflectionClass( $className ) );
            file_put_contents( $fileName, $code );

            echo $code;
        }

        include_once $fileName;

        return $namespaceName . $proxyName;
    }

    private function _createClass( $proxyName, \ReflectionClass $class )
    {
        if ( $class->isFinal() || $class->isAbstract() )
        {
            throw new \Exception( '#fail' );
        }

        $code  = '<?php' . PHP_EOL;
        if ( $class->inNamespace() )
        {
            $code .= 'namespace ' . $class->getNamespaceName() . ';' . PHP_EOL .
                     PHP_EOL;
        }

        $code .= 'class ' . $proxyName .
                 ' extends \\' . $class->getName() .
                 ' implements \de\buzz2ee\aop\interfaces\Proxy' . PHP_EOL .
                 '{' . PHP_EOL;

        $code .= $this->_methodGenerator->generateConstructor( $class );
        foreach ( $class->getMethods() as $method )
        {
            $code .= $this->_methodGenerator->generate( $method );
        }
        $code .= $this->_adviceGenerator->generateClassInterceptCode();

        $code .= '}' . PHP_EOL;

        return $code;
    }
}