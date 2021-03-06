<?php
namespace de\buzz2ee\aop;

use de\buzz2ee\aop\interfaces\ProceedingJoinPoint;

class RuntimeProceedingJoinPoint extends RuntimeJoinPoint implements ProceedingJoinPoint
{
    private $_activeAdvice = 0;

    private $_adviceLength = 0;

    private $_adviceChain = array();

    public function setAdviceChain( array $adviceChain )
    {
        $this->_adviceChain  = $adviceChain;
        $this->_adviceLength = count( $adviceChain );
    }

    public function proceed()
    {
        if ( $this->_activeAdvice < $this->_adviceLength )
        {
            return call_user_func( $this->_adviceChain[$this->_activeAdvice++], $this );
        }
        else if ( $this->_activeAdvice === $this->_adviceLength )
        {
            return call_user_func_array( $this->getTargetCallback(), $this->getArgs() );
        }
        throw new \RuntimeException( 'Invalid state, no more advices in chain.' );
    }

    private function getTargetCallback()
    {
        return array( $this->getTarget(), $this->getMethodName() );
    }
}