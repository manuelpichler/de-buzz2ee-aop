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
            if ( preg_match( '(\*\s*@(Pointcut|After|AfterReturning|AfterThrowing|Before|Around)\(\s*"(.*)"\s*\)\s*\*(\r|\n|/))s', $method->getDocComment(), $match ) === 0 )
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

                case 'Around':
                    $aspect->addAdvice( new \de\buzz2ee\aop\advice\AroundAdvice( $pointcut, $method->getName(), $reflection->getName() ) );
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
        $methodGenerator = new generator\ProxyMethodGenerator( $adviceGenerator );

        $creator    = new generator\ProxyClassGenerator( $adviceGenerator, $methodGenerator );
        $proxyClass = $creator->create( $className );

        $proxyInstance = new $proxyClass( new $className() );
        foreach ( $proxyInstance->____aop_get_interceptor_configuration() as $name )
        {
            $proxyInstance->____aop_add_interceptor_instance( $name, new $name() );
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