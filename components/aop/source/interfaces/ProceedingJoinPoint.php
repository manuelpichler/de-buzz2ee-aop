<?php
namespace de\buzz2ee\aop\interfaces;

interface ProceedingJoinPoint extends JoinPoint
{
    function proceed();
}