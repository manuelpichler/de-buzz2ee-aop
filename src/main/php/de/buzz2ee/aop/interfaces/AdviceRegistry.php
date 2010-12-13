<?php
namespace de\buzz2ee\aop\interfaces;

interface AdviceRegistry
{
    function getMatchingAdvices( JoinPoint $joinPoint );
}