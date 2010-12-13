<?php
namespace de\buzz2ee\aop\advice;

use de\buzz2ee\aop\interfaces\Advice;

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