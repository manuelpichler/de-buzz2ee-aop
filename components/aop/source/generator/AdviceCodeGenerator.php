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

use de\buzz2ee\aop\advice\AfterAdvice;
use de\buzz2ee\aop\advice\AfterReturningAdvice;
use de\buzz2ee\aop\advice\AfterThrowingAdvice;
use de\buzz2ee\aop\advice\AroundAdvice;
use de\buzz2ee\aop\advice\BeforeAdvice;

use de\buzz2ee\aop\interfaces\Advice;
use de\buzz2ee\aop\interfaces\JoinPoint;
use de\buzz2ee\aop\interfaces\PointcutRegistry;

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

    public function __construct( PointcutRegistry $registry, array $aspects )
    {
        $this->_adviceRegistry = new DefaultAdviceRegistry( $registry, $aspects );
    }

    public function generateClassInterceptCode()
    {
        $code = '    private $____aop_interception_target = null;' .
                PHP_EOL . PHP_EOL .
                '    private $_aop_interceptor_instances__ = array();' .
                PHP_EOL . PHP_EOL .
                '    private $_aop_interceptor_configuration__ = array(' .
                PHP_EOL;
        
        foreach ( array_unique( $this->_interceptors ) as $interceptor )
        {
            $code .= "        '" . $interceptor . "'," . PHP_EOL;
        }
        $code .= '    );' . 
                 PHP_EOL . PHP_EOL .
                 '    public function ____aop_get_interceptor_configuration()' . PHP_EOL .
                 '    {' . PHP_EOL .
                 '        return $this->_aop_interceptor_configuration__;' . PHP_EOL .
                 '    }' . 
                 PHP_EOL . PHP_EOL .
                 '    public function ____aop_add_interceptor_instance( $name, $interceptor )' . PHP_EOL .
                 '    {' . PHP_EOL .
                 '        $this->_aop_interceptor_instances__[$name] = $interceptor;' . PHP_EOL .
                 '    }' . PHP_EOL;

        return $code;
    }

    public function generateMethodInterceptionCode( JoinPoint $joinPoint, $methodName )
    {
        $advices = $this->_adviceRegistry->getMatchingAdvices( $joinPoint );

        return '        $__aop_interception_method    = ' . "'" . $methodName . "';" . PHP_EOL .
               '        $__aop_interception_callback  = array( $this->____aop_interception_target, $__aop_interception_method );' . PHP_EOL .
               '        $__aop_interception_arguments = func_get_args();' .
               PHP_EOL . PHP_EOL .
               $this->generateProlog( $joinPoint, $advices ) .
               $this->generateBefore( $advices ) .
               $this->_generateTryCatchCode( $advices ) .
               $this->generateAfterReturning( $advices ) .
               '        return $__aop_interception_result;' . PHP_EOL;
    }

    public function generateProlog( JoinPoint $joinPoint, Advices $advices )
    {
        if ( $advices->hasAroundAdvice() )
        {
            return $this->_generateProceedingJoinPointInitializationCode( $joinPoint, $advices );
        }
        else if ( $advices->hasAdvice() )
        {
            return $this->_generateJoinPointInitializationCode( $joinPoint, $advices );
        }
        return '';
    }

    private function _generateJoinPointInitializationCode( JoinPoint $joinPoint, Advices $advices )
    {
        return $this->_generateJoinPointConstructorInvocationCode( $joinPoint, '\de\buzz2ee\aop\RuntimeJoinPoint' ) .
               PHP_EOL;
    }

    private function _generateProceedingJoinPointInitializationCode( JoinPoint $joinPoint, Advices $advices )
    {
        $code = $this->_generateJoinPointConstructorInvocationCode( $joinPoint, '\de\buzz2ee\aop\RuntimeProceedingJoinPoint' ) .
                '        $joinPoint->setAdviceChain( array( ' . PHP_EOL;
        foreach ( $advices->getAroundAdvices() as $advice )
        {
            $code .= "            array( \$this->_aop_interceptor_instances__['" . $advice->getAspectClassName() . "'], '" . $advice->getAspectMethodName() . "')," . PHP_EOL;
        }
        $code .= '        ) );' . PHP_EOL .
                 '        $joinPoint->setAdvicedTarget( $__aop_interception_callback );' . PHP_EOL .
                 PHP_EOL;

        return $code;
    }

    private function _generateJoinPointConstructorInvocationCode( JoinPoint $joinPoint, $className )
    {
        return '        $joinPoint = new ' . $className . '( $this->____aop_interception_target, $this, ' .
               "'" . $joinPoint->getClassName() . "', " .
               "'" . $joinPoint->getMethodName() . "', " .
               '$__aop_interception_arguments );' . PHP_EOL;
    }

    public function generateBefore( Advices $advices )
    {
        $code = '';
        foreach ( $advices->getBeforeAdvices() as $advice )
        {
            $code .= $this->_generateAdviceInvocationCode( $advice );
        }
        return $code;
    }

    private function _generateTryCatchCode( Advices $advices )
    {
        if ( $advices->hasAfterAdvice() || $advices->hasAfterThrowingAdvice() )
        {
            return '        try' . PHP_EOL .
                   '        {' . PHP_EOL .
                   $this->_generateAroundAdviceCode( $advices ) .
                   $this->generateAfter( $advices ) .
                   '        }' . PHP_EOL .
                   '        catch ( \Exception $e )' . PHP_EOL .
                   '        {' . PHP_EOL .
                   $this->generateAfterThrowing( $advices ) .
                   $this->generateAfter( $advices ) .
                   '            throw $e;' . PHP_EOL .
                   '        }' . PHP_EOL;
        }
        return $this->_generateAroundAdviceCode( $advices );
    }

    private function _generateAroundAdviceCode( Advices $advices )
    {
        if ( $advices->hasAroundAdvice() )
        {
            return '        $__aop_interception_result = $joinPoint->proceed();' . PHP_EOL;
        }
        return $this->_generateTargetMethodInvocationCode();
    }

    private function _generateTargetMethodInvocationCode()
    {
        return '        $__aop_interception_result = call_user_func_array( $__aop_interception_callback, $__aop_interception_arguments );' . PHP_EOL;
    }

    public function generateAfter( Advices $advices )
    {
        $code = '';
        foreach ( $advices->getAfterAdvices() as $advice )
        {
            $code .= $this->_generateAdviceInvocationCode( $advice );
        }
        return $code;
    }

    public function generateAfterThrowing( Advices $advices )
    {
        $code = '';
        foreach ( $advices->getAfterThrowingAdvices() as $advice )
        {
            $code .= $this->_generateAdviceInvocationCode( $advice );
        }
        return $code;
    }

    public function generateAfterReturning( Advices $advices )
    {
        $code = '';
        foreach ( $advices->getAfterReturningAdvices() as $advice )
        {
            $code .= $this->_generateAdviceInvocationCode( $advice );
        }
        return $code;
    }

    private function _generateAdviceInvocationCode( Advice $advice )
    {
        $this->_interceptors[] = $advice->getAspectClassName();
        return '        $this->_aop_interceptor_instances__["' . $advice->getAspectClassName() . '"]->' . $advice->getAspectMethodName() . '( $joinPoint );' . PHP_EOL;
    }
}

class AdviceCodeGeneratorOptions
{
    protected $properties = array(
        'interceptorInstancesPropertyName'      =>  '_aop_interceptor_instances__',
        'interceptorConfigurationPropertyName'  =>  ''
    );
}

interface AdviceRegistry
{
    function getMatchingAdvices( JoinPoint $joinPoint );
}

class DefaultAdviceRegistry implements AdviceRegistry
{
    /**
     *
     * @var \de\buzz2ee\aop\interfaces\PointcutRegistry
     */
    private $_registry = null;

    /**
     *
     * @var array(\de\buzz2ee\aop\Aspect)
     */
    private $_aspects = array();

    public function __construct( PointcutRegistry $registry, array $aspects )
    {
        $this->_registry = $registry;
        $this->_aspects  = $aspects;
    }

    /**
     * @return \de\buzz2ee\aop\generator\Advices
     */
    public function getMatchingAdvices( JoinPoint $joinPoint )
    {
        return $this->_getAdvicesForJoinPoint( $joinPoint );
    }

    private function _getAdvicesForJoinPoint( JoinPoint $joinPoint )
    {
        $advices = new Advices();
        foreach ( $this->_aspects as $aspect )
        {
            foreach ( $aspect->getAdvices() as $advice )
            {
                if ( $advice->getPointcut()->match( $joinPoint, $this->_registry ) )
                {
                    $advices->add( $advice );
                }
            }
        }
        return $advices;
    }
}

class Advices
{
    private $_empty = true;

    private $_advices = array(
        AfterAdvice::TYPE           =>  array(),
        AfterReturningAdvice::TYPE  =>  array(),
        AfterThrowingAdvice::TYPE   =>  array(),
        AroundAdvice::TYPE          =>  array(),
        BeforeAdvice::TYPE          =>  array(),
    );

    public function hasAdvice()
    {
        return !$this->_empty;
    }

    public function hasAfterAdvice()
    {
        return ( count( $this->_advices[AfterAdvice::TYPE] ) > 0 );
    }

    public function getAfterAdvices()
    {
        return $this->_advices[AfterAdvice::TYPE];
    }

    public function hasAfterReturningAdvice()
    {
        return ( count( $this->_advices[AfterReturningAdvice::TYPE] ) > 0 );
    }

    public function getAfterReturningAdvices()
    {
        return $this->_advices[AfterReturningAdvice::TYPE];
    }

    public function hasAfterThrowingAdvice()
    {
        return ( count( $this->_advices[AfterThrowingAdvice::TYPE] ) > 0 );
    }

    public function getAfterThrowingAdvices()
    {
        return $this->_advices[AfterThrowingAdvice::TYPE];
    }

    public function hasAroundAdvice()
    {
        return ( count( $this->_advices[AroundAdvice::TYPE] ) > 0 );
    }

    public function getAroundAdvices()
    {
        return $this->_advices[AroundAdvice::TYPE];
    }

    public function hasBeforeAdvice()
    {
        return ( count( $this->_advices[BeforeAdvice::TYPE] ) > 0 );
    }

    public function getBeforeAdvices()
    {
        return $this->_advices[BeforeAdvice::TYPE];
    }

    public function add( Advice $advice )
    {
        if ( isset( $this->_advices[$advice::TYPE] ) )
        {
            $this->_empty                    = false;
            $this->_advices[$advice::TYPE][] = $advice;
        }
        else
        {
            throw new \ErrorException( '...' );
        }
    }
}