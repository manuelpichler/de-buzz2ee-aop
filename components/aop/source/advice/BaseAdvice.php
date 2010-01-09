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
 * @package   de\buzz2ee\aop\advice
 * @author    Manuel Pichler <mapi@buzz2ee.de>
 * @copyright 2009-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://buzz2ee.de/
 */

namespace de\buzz2ee\aop\advice;

use de\buzz2ee\aop\interfaces\Advice;
use de\buzz2ee\aop\interfaces\JoinPoint;
use de\buzz2ee\aop\interfaces\Pointcut;

/**
 *
 *
 * @category  Components
 * @package   de\buzz2ee\aop\advice
 * @author    Manuel Pichler <mapi@buzz2ee.de>
 * @copyright 2009-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: @package_version@
 * @link      http://buzz2ee.de/
 */
abstract class BaseAdvice implements Advice
{
    /**
     * The associated pointcut instance.
     *
     * @var \de\buzz2ee\aop\interfaces\Pointcut
     */
    private $_pointcut = null;

    /**
     * Name of the declaring aspect class.
     *
     * @var string
     */
    private $_aspectClassName = null;

    /**
     * Name of the declaring aspect method.
     *
     * @var string
     */
    private $_aspectMethodName = null;

    /**
     * Constructs a new advice instance.
     *
     * @param \de\buzz2ee\aop\interfaces\Pointcut $pointcut
     *        The pointcut instance that describes the join points where this
     *        advice will apply.
     * @param string $aspectMethodName
     *        The name of the declaring aspect method.
     * @param string $aspectClassName
     *        The name of the declaring aspect class.
     */
    public function __construct( Pointcut $pointcut, $aspectMethodName, $aspectClassName )
    {
        $this->_pointcut         = $pointcut;
        $this->_aspectClassName  = $aspectClassName;
        $this->_aspectMethodName = $aspectMethodName;
    }

    public function getAspectClassName()
    {
        return $this->_aspectClassName;
    }

    public function getAspectMethodName()
    {
        return $this->_aspectMethodName;
    }

    public function getName()
    {
        return $this->_pointcut->getName();
    }

    /**
     *
     * @return \de\buzz2ee\aop\interfaces\Pointcut
     */
    public function getPointcut()
    {
        return $this->_pointcut;
    }

    public function __toString()
    {
        return get_class($this) . "::({$this->_pointcut->getName()})";
    }
}