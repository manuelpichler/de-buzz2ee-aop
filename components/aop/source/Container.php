<?php
namespace de\buzz2ee\aop;

use de\buzz2ee\aop\interfaces\PointcutRegistry;
use de\buzz2ee\aop\pointcut\Pointcut;
use de\buzz2ee\aop\pointcut\PointcutExpressionParser;
use de\buzz2ee\aop\pointcut\PointcutMatcherFactory;

use de\buzz2ee\aop\generator\AdviceCodeGenerator;

class Container implements PointcutRegistry
{
    private $_aspects = array();

    public function __construct()
    {
        PointcutMatcherFactory::set( new PointcutMatcherFactory() );
    }

    public function registerAspect( $aspectClassName )
    {
        $aspect = new Aspect( $aspectClassName );

        $reflection = new \ReflectionClass( $aspectClassName );
        foreach ( $reflection->getMethods() as $method )
        {
            if ( preg_match( '(\*\s*@(Pointcut|After|Before)\(\s*"(.*)"\s*\)\s*\*(\r|\n|/))s', $method->getDocComment(), $match ) === 0 )
            {
                continue;
            }

            $pointcutName = $reflection->getName() . '::' . $method->getName() . '()';

            $tagName    = $match[1];
            $expression = $match[2];

            $expression = trim( preg_replace( '(\s*\*\s+)', '', $expression ) );

            $parser   = new PointcutExpressionParser();
            $pointcut = new Pointcut(
                $pointcutName,
                $parser->parse( $expression )
            );

            switch ( $tagName )
            {
                case 'Pointcut':
                    $aspect->addPointcut( $pointcut );
                    break;

                case 'After':
                    $aspect->addAdvice( new \de\buzz2ee\aop\advice\AfterAdvice( $pointcut ) );
                    break;

                case 'Before':
                    $aspect->addAdvice( new \de\buzz2ee\aop\advice\BeforeAdvice( $pointcut ) );
                    break;
            }
        }

        $this->_aspects[] = $aspect;
    }

    public function createObject( $className )
    {
        $creator    = new ProxyClassGenerator( new AdviceCodeGenerator( $this, $this->_aspects ) );
        $proxyClass = $creator->create( $className );

        return new $proxyClass( new $className() );
    }

    public function getPointcutByName( $pointcutName )
    {
        $name = ltrim( $pointcutName, '\\' );
        foreach ( $this->_aspects as $aspect )
        {
            foreach ( $aspect->getPointcuts() as $pointcut )
            {
                if ( $pointcut->getName() === $name )
                {
                    return $pointcut;
                }
            }
        }
        throw new \InvalidArgumentException( 'Unknown pointcut name: ' . $pointcutName  );
    }
}



class ProxyClassGenerator implements \de\buzz2ee\aop\interfaces\ClassGenerator
{
    /**
     *
     * @var \de\buzz2ee\aop\ProxyMethodGenerator
     */
    private $_methodGenerator = null;

    public function  __construct( AdviceCodeGenerator $adviceGenerator )
    {
        $this->_constructorGenerator = new ProxyConstructorGenerator();
        $this->_methodGenerator      = new ProxyMethodGenerator( $adviceGenerator );
    }

    public function create( $className )
    {
        $tempName  = strtr( ltrim( $className, '\\' ), '\\', '_' );
        $proxyName = 'Proxy__' . $tempName;
        $fileName  = getcwd() . '/' . $tempName . '.php';

        if ( true || file_exists( $fileName ) === false )
        {
            file_put_contents( $fileName, $this->_createClass( $proxyName, new \ReflectionClass( $className ) ) );
        }

        include_once $fileName;

        return $proxyName;
    }

    private function _createClass( $proxyName, \ReflectionClass $class )
    {
        if ( $class->isFinal() || $class->isAbstract() )
        {
            throw new \Exception( '#fail' );
        }

        $code  = '<?php' . PHP_EOL;
        if ( $class->inNamespace() )
        {
            //$code .= 'namespace ' . $class->getNamespaceName() . ';' . PHP_EOL .
            //         PHP_EOL;
        }

        $code .= 'class ' . $proxyName .
                 ' extends \\' . $class->getName() .
                 ' implements \de\buzz2ee\aop\interfaces\Proxy' . PHP_EOL .
                 '{' . PHP_EOL;

        $code .= $this->_constructorGenerator->generate( $class );
        foreach ( $class->getMethods() as $method )
        {
            $code .= $this->_methodGenerator->create( $method );
        }
        
        $code .= '}' . PHP_EOL;

        return $code;
    }
}

class ProxyConstructorGenerator
{
    public function generate( \ReflectionClass $class )
    {
        return '    public function __construct( \\' . $class->getName() . ' $subject )' . PHP_EOL .
               '    {' . PHP_EOL .
               '        $this->_subject = $subject;' . PHP_EOL .
               '    }' . PHP_EOL .
               PHP_EOL;
    }
}



class ProxyMethodGenerator
{
    /**
     *
     * @var \de\buzz2ee\aop\ProxyParameterGenerator
     */
    private $_parameterGenerator = null;

    /**
     * @var \de\buzz2ee\aop\AdviceCodeGenerator
     */
    private $_adviceCodeGenerator = null;

    public function __construct( AdviceCodeGenerator $adviceGenerator )
    {
        $this->_adviceCodeGenerator = $adviceGenerator;
        $this->_parameterGenerator  = new ProxyParameterGenerator();
    }

    public function create( \ReflectionMethod $method )
    {
        if ( $method->isAbstract() )
        {
            return;
        }
        if ( $method->isConstructor() )
        {
            return;
        }
        if ( $method->isDestructor() )
        {
            return;
        }
        if ( $method->isFinal() )
        {
            return;
        }
        if ( $method->isPrivate() )
        {
            return;
        }
        if ( $method->isStatic() )
        {
            return;
        }

        $joinPoint = new ReflectionJoinPoint( $method );

        $parameters = $this->_createParameters( $method->getParameters() );

        $code = '    ' .
                $joinPoint->getVisibility() . ' function ' . $method->getName() . '( ' . $parameters . ')' . PHP_EOL .
                '    {' . PHP_EOL .
                $this->_adviceCodeGenerator->generateProlog( $joinPoint ) .
                $this->_adviceCodeGenerator->generateBefore( $joinPoint ) .
                '        return call_user_func_array( array( $this->_subject, "' .
                $method->getName() . '" ), $arguments );' . PHP_EOL .
                '    }' . PHP_EOL .
                PHP_EOL;
        
        return $code;
    }

    private function _createParameters( array $parameters )
    {
        $code = array();
        foreach ( $parameters as $index => $parameter )
        {
            $code[] = $this->_parameterGenerator->generate( $parameter );
        }
        return join( ', ', $code );
    }
}

class ProxyParameterGenerator
{
    public function generate( \ReflectionParameter $parameter )
    {
        $code = '';
        if ( $parameter->isArray() )
        {
            $code .=  'array ';
        } 
        else if ( is_object( $parameter->getClass() ) )
        {
            $code .= '\\' . $parameter->getClass()->getName() . ' ';
        }

        $code .= '$' . $parameter->getName();

        if ( $parameter->isDefaultValueAvailable() )
        {

            $code .= ' = "???"';
        }
        return $code;
    }
}