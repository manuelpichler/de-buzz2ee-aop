<?php
/**
 * This file is part of the aspect oriented programming component.
 *
 * PHP Version 5
 *
 * Copyright (c) 2009-2010, Manuel Pichler <mapi@pdepend.org>.
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
 * @package   de\buzz2ee\aop
 * @author    Manuel Pichler <mapi@buzz2ee.de>
 * @copyright 2009-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://buzz2ee.de/
 */

namespace de\buzz2ee\aop;

use de\buzz2ee\aop\interfaces\Pointcut;
use de\buzz2ee\aop\interfaces\PointcutRegistry;

use de\buzz2ee\aop\pointcut\PointcutExpressionParser;
use de\buzz2ee\aop\pointcut\PointcutMatcherFactory;

use de\buzz2ee\aop\generator\AdviceCodeGenerator;

/**
 *
 *
 * @category  Components
 * @package   de\buzz2ee\aop
 * @author    Manuel Pichler <mapi@buzz2ee.de>
 * @copyright 2009-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://buzz2ee.de/
 */
class Container implements PointcutRegistry
{
    private $_aspects = array();

    public function __construct()
    {
        PointcutMatcherFactory::set( new PointcutMatcherFactory() );
    }

    public function registerAspect( $aspectClassName )
    {
        $aspect = new Aspect( $aspectClassName );

        $reflection = new \ReflectionClass( $aspectClassName );
        foreach ( $reflection->getMethods() as $method )
        {
            if ( preg_match( '(\*\s*@(Pointcut|After|AfterReturning|AfterThrowing|Before)\(\s*"(.*)"\s*\)\s*\*(\r|\n|/))s', $method->getDocComment(), $match ) === 0 )
            {
                continue;
            }

            $pointcutName = $reflection->getName() . '::' . $method->getName() . '()';

            $tagName    = $match[1];
            $expression = $match[2];

            $expression = trim( preg_replace( '(\s*\*\s+)', '', $expression ) );

            $parser   = new PointcutExpressionParser();
            $pointcut = new \de\buzz2ee\aop\pointcut\DefaultPointcut(
                $pointcutName,
                $parser->parse( $expression )
            );

            switch ( $tagName )
            {
                case 'Pointcut':
                    $aspect->addPointcut( $pointcut );
                    break;

                case 'After':
                    $aspect->addAdvice( new \de\buzz2ee\aop\advice\AfterAdvice( $pointcut, $method->getName(), $reflection->getName() ) );
                    break;

                case 'AfterReturning':
                    $aspect->addAdvice( new \de\buzz2ee\aop\advice\AfterReturningAdvice( $pointcut, $method->getName(), $reflection->getName() ) );
                    break;

                case 'AfterThrowing':
                    $aspect->addAdvice( new \de\buzz2ee\aop\advice\AfterThrowingAdvice( $pointcut, $method->getName(), $reflection->getName() ) );
                    break;

                case 'Before':
                    $aspect->addAdvice( new \de\buzz2ee\aop\advice\BeforeAdvice( $pointcut, $method->getName(), $reflection->getName() ) );
                    break;
            }
        }

        $this->_aspects[] = $aspect;
    }

    public function createObject( $className )
    {
        $adviceGenerator = new AdviceCodeGenerator( $this, $this->_aspects );
        $methodGenerator = new ProxyMethodGenerator( $adviceGenerator );

        $creator    = new ProxyClassGenerator( $adviceGenerator, $methodGenerator );
        $proxyClass = $creator->create( $className );

        $proxyInstance = new $proxyClass( new $className() );
        foreach ( $proxyInstance->_get_aop_interceptor_configuration() as $name )
        {
            $proxyInstance->_add_aop_interceptor_instance( $name, new $name() );
        }

        return $proxyInstance;
    }

    public function getPointcutByName( $pointcutName )
    {
        $name = ltrim( $pointcutName, '\\' );
        foreach ( $this->_aspects as $aspect )
        {
            foreach ( $aspect->getPointcuts() as $pointcut )
            {
                if ( $pointcut->getName() === $name )
                {
                    return $pointcut;
                }
            }
        }
        throw new \InvalidArgumentException( 'Unknown pointcut name: ' . $pointcutName  );
    }
}

class ProxyClassGenerator implements \de\buzz2ee\aop\interfaces\ClassGenerator
{
    /**
     *
     * @var \de\buzz2ee\aop\generator\AdviceCodeGenerator
     */
    private $_adviceGenerator = null;

    /**
     *
     * @var \de\buzz2ee\aop\ProxyMethodGenerator
     */
    private $_methodGenerator = null;

    public function  __construct( 
        AdviceCodeGenerator $adviceGenerator,
        ProxyMethodGenerator $methodGenerator
    ) {
        $this->_adviceGenerator      = $adviceGenerator;
        $this->_constructorGenerator = new ProxyConstructorGenerator( $adviceGenerator );
        $this->_methodGenerator      = $methodGenerator;
    }

    public function create( $className )
    {
        $position = strrpos( $className, '\\' );

        if ( is_int( $position ) )
        {
            $proxyName     = 'Proxy__' . substr( $className, $position + 1 );
            $namespaceName = substr( $className, 0, $position + 1 );
        }
        else
        {
            $proxyName     = 'Proxy__' . $className;
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

        $code .= $this->_constructorGenerator->generate( $class );
        foreach ( $class->getMethods() as $method )
        {
            $code .= $this->_methodGenerator->create( $method );
        }
        $code .= $this->_adviceGenerator->generateClassInterceptCode();
        
        $code .= '}' . PHP_EOL;

        return $code;
    }
}

class ProxyConstructorGenerator
{
    public function generate( \ReflectionClass $class )
    {
        return '    public function __construct( \\' . $class->getName() . ' $subject )' . PHP_EOL .
               '    {' . PHP_EOL .
               '        $this->_subject = $subject;' . PHP_EOL .
               '    }' . PHP_EOL .
               PHP_EOL;
    }
}



class ProxyMethodGenerator
{
    /**
     *
     * @var \de\buzz2ee\aop\ProxyParameterGenerator
     */
    private $_parameterGenerator = null;

    /**
     * @var \de\buzz2ee\aop\AdviceCodeGenerator
     */
    private $_adviceCodeGenerator = null;

    public function __construct( AdviceCodeGenerator $adviceGenerator )
    {
        $this->_adviceCodeGenerator = $adviceGenerator;
        $this->_parameterGenerator  = new ProxyParameterGenerator();
    }

    public function create( \ReflectionMethod $method )
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

        $parameters = $this->_createParameters( $method->getParameters() );

        $code = '    ' .
                $joinPoint->getVisibility() . ' function ' . $method->getName() . '( ' . $parameters . ')' . PHP_EOL .
                '    {' . PHP_EOL .
                '        $arguments = func_get_args();' . PHP_EOL .
                $this->_adviceCodeGenerator->generateMethodInterceptCodeProlog( $joinPoint ) .
                '        $returnValue = call_user_func_array( array( $this->_subject, "' .
                $method->getName() . '" ), $arguments );' . PHP_EOL .
                $this->_adviceCodeGenerator->generateMethodInterceptCodeEpilog( $joinPoint ) .
                '        return $returnValue;' . PHP_EOL .
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

class ProxyParameterGenerator
{
    public function generate( \ReflectionParameter $parameter )
    {
        $code = '';
        if ( $parameter->isArray() )
        {
            $code .=  'array ';
        } 
        else if ( is_object( $parameter->getClass() ) )
        {
            $code .= '\\' . $parameter->getClass()->getName() . ' ';
        }

        $code .= '$' . $parameter->getName();

        if ( $parameter->isDefaultValueAvailable() )
        {

            $code .= ' = "???"';
        }
        return $code;
    }
}