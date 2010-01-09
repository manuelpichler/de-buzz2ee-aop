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

use de\buzz2ee\aop\interfaces\PointcutMatcher;
use de\buzz2ee\aop\exceptions\InvalidPointcutExpressionException;

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
class PointcutExpressionParser
{
    const REGEXP_POINTCUT_NAMED = '(^
                (?P<class>([\\\\]?[a-z_\x7f-\xff][a-z0-9_\x7f-\xff]*)+)::
                (?P<method>[a-z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)\(\)
            $)xi',
          REGEXP_COMBINE_OPERATORS   = '((&&|\|\|))',
          REGEXP_POINTCUT_DESIGNATOR = '(^
                (?P<designator>execution)\((?P<expression>.+)\)
            $)x',
          REGEXP_POINTCUT_EXPRESSION = '(^
                ((?P<visibility>public|protected|private|\*)\s+)?
                ((?P<class>([\\]?[a-z_\x7f-\xff\*][a-z0-9_\x7f-\xff\*]*)+)::)
                ((?P<method>([\\]?[a-z_\x7f-\xff\*][a-z0-9_\x7f-\xff\*]*)+)\(\))
            $)xi';

    private $_expression = null;

    private $_offset = -1;

    private $_tokens = array();

    /**
     * @param string $expression
     *
     * @return PointcutExpression
     */
    public function parse( $expression )
    {
        $this->_expression = $expression;

        $this->_offset = -1;
        $this->_tokens = $this->_splitCombinedExpressions( $expression );

        if ( count( $this->_tokens ) % 2 === 0 )
        {
            throw new InvalidPointcutExpressionException( $expression );
        }
        return $this->_parsePointcutBinaryMatcher();
    }

    /**
     * @param string $expression
     *
     * @return array(string)
     */
    private function _splitCombinedExpressions( $expression )
    {
        return array_map(
            'trim',
            preg_split(
                self::REGEXP_COMBINE_OPERATORS,
                $expression,
                -1,
                PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
            )
        );
    }

    private function _parsePointcutBinaryMatcher()
    {
        $expression = $this->_parsePointcutNotMatcher( $this->_tokens[++$this->_offset] );

        if ( isset( $this->_tokens[++$this->_offset] ) )
        {
            if ( $this->_tokens[$this->_offset] === '&&' )
            {
                $expression = PointcutMatcherFactory::get()->createAndMatcher(
                    $expression, $this->_parsePointcutBinaryMatcher()
                );
            }
            else if ( $this->_tokens[$this->_offset] === '||' )
            {
                $expression = PointcutMatcherFactory::get()->createOrMatcher(
                    $expression, $this->_parsePointcutBinaryMatcher()
                );
            }
        }

        return $expression;
    }

    /**
     * @param string $expression
     *
     * @return PointcutMatcher
     */
    private function _parsePointcutNotMatcher( $expression )
    {
        if ( substr( $expression, 0, 1 ) === '!' )
        {
            return PointcutMatcherFactory::get()->createNotMatcher(
                $this->_parsePointcutNotMatcher(
                    trim( substr( $expression, 1 ) )
                )
            );
        }
        return $this->_parsePointcutMatcher( $expression );
    }

    /**
     * @param string $expression
     *
     * @return PointcutMatcher
     */
    private function _parsePointcutMatcher( $expression )
    {
        if ( preg_match( self::REGEXP_POINTCUT_NAMED, $expression, $match ) )
        {
            return PointcutMatcherFactory::get()->createNamedMatcher(
                $match['class'],
                $match['method']
            );
        }
        else if ( preg_match( self::REGEXP_POINTCUT_DESIGNATOR, $expression, $match ) )
        {
            return $this->_parseDesignator( 
                $match['designator'],
                $match['expression']
            );
        }
        throw new InvalidPointcutExpressionException( $this->_expression );
    }

    /**
     * @param string $designator
     * @param string $expression
     *
     * @return PointcutMatcher
     */
    private function _parseDesignator( $designator, $expression )
    {
        switch ( $designator )
        {
            case 'execution':
                $matcher = $this->_parseExecutionDesignator( $expression );
                break;
        }
        return $matcher;
    }

    /**
     * @param string $expression
     *
     * @return PointcutExecutionMatcher
     */
    private function _parseExecutionDesignator( $expression )
    {
        if ( preg_match( self::REGEXP_POINTCUT_EXPRESSION, $expression, $match ) )
        {
            return PointcutMatcherFactory::get()->createExecutionMatcher(
                $match['class'],
                $match['method'],
                $match['visibility']
            );
        }
        throw new InvalidPointcutExpressionException( $this->_expression );
    }
}