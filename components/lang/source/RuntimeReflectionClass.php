<?php
namespace de\buzz2ee\lang;

use \de\buzz2ee\lang\interfaces\ReflectionClass;

class RuntimeReflectionClass extends \ReflectionClass implements ReflectionClass
{

}

$r = new \ReflectionClass( "RuntimeReflectionClass" );
var_dump( $r->getName() );
