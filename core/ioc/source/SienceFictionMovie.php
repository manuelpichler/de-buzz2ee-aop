<?php

namespace com\example\ioc;

require_once 'Movie.php';

class SienceFictionMovie extends Movie
{
    const TYPE = __CLASS__;

    public function __construct() { var_dump(func_get_args()); }
}
