<?php
namespace de\buzz2ee\aop\generator;

use de\buzz2ee\aop\interfaces\AdviceRegistry;
use de\buzz2ee\aop\interfaces\GeneratorFactory;

class ProxyGeneratorFactory implements GeneratorFactory
{
    /**
     *
     * @var \de\buzz2ee\aop\interfaces\AdviceRegistry
     */
    private $adviceRegistry = null;

    private $methodGenerator = null;

    private $adviceGenerator = null;

    public function __construct( AdviceRegistry $adviceRegistry )
    {
        $this->adviceRegistry = $adviceRegistry;
    }

    public function createClassGenerator()
    {
        return new ProxyClassGenerator( $this );
    }

    public function createMethodGenerator()
    {
        if ($this->methodGenerator === null) {
            $this->methodGenerator = new ProxyMethodGenerator( $this->adviceRegistry, $this->createAdviceGenerator() );
        }
        return $this->methodGenerator;
    }

    public function createAdviceGenerator()
    {
        if (null === $this->adviceGenerator) {
            $this->adviceGenerator = new AdviceCodeGenerator( $this->adviceRegistry );
        }
        return $this->adviceGenerator;
    }
}