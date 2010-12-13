<?php
namespace scanner\domain;

/**
 * Class ...
 */
class Product
{
    /**
     * @var integer
     */
    private $id = null;

    /**
     * @var string
     */
    private $name = null;

    /** 
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}