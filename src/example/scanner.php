#!/usr/bin/env php
<?php
spl_autoload_register(
    function( $class )
    {
        if ( 0 === strpos( $class, 'de\buzz2ee' ) )
        {
            include __DIR__ . '/../main/php/' . strtr( $class, '\\', '/' ) . '.php';
        }
        else
        {
            include __DIR__ . '/' . strtr( $class, '\\', '/' ) . '.php';
        }
    }
);

interface Scanner
{
    function scan();
}

abstract class AbstractScannerDecorator implements Scanner
{
    
}

class CacheScannerDecorator extends AbstractScannerDecorator
{
    private $cacheDir = null;

    public function __construct( $cacheDir )
    {
        $this->cacheDir = $cacheDir;
    }

    public function scan()
    {
        
    }
}

class ModificationScanner implements Scanner
{
    private $timestamp = 0;

    private $directory = null;

    private $aspects = array();

    public function __construct( $directory )
    {
        $this->timestamp = (int) @file_get_contents( '/tmp/timestamp' );
        $this->directory = $directory;
    }

    public function scan()
    {
        if ( $this->scanDirectory( $this->directory ) )
        {
            $this->scanFiles( $this->directory );

            file_put_contents( '/tmp/timestamp', time() );

            return true;
        }
        return false;
    }

    public function getAspects()
    {
        return $this->aspects;
    }

    private function scanDirectory( $path )
    {
        foreach ( new DirectoryIterator( $path ) as $file )
        {
            $name = $file->getFilename();
            if ( '.' === $name || '..' === $name || false === $file->isDir() )
            {
                continue;
            }
            else if ( fileatime( $file->getRealpath() ) > $this->timestamp )
            {
                return true;
            }
        }
        return false;
    }

    private function scanFiles( $path )
    {
        foreach ( new DirectoryIterator( $path ) as $file )
        {
            $name = $file->getFilename();
            if ( '.' === $name || '..' === $name )
            {
                continue;
            }
            else if ( $file->isDir() )
            {
                $this->scanFiles( $file->getRealpath() );
            }
            else
            {
                $this->scanFile( $file->getRealpath() );
            }
        }
    }

    private function scanFile( $path )
    {
        $source = file_get_contents( $path );
        if ( false === strpos( $source, '@Aspect' ) )
        {
            return;
        }

        $className = '';
        $namespace = '';
        $isAspect  = false;

        $tokens = token_get_all( $source );
        while ( $token = next( $tokens ) )
        {
            if ( false === is_array( $token ) )
            {
                continue;
            }

            if ( $token[0] === T_DOC_COMMENT )
            {
                $className = '';
                $isAspect  = ( strpos( $token[1], '@Aspect' ) > 0 );
            }
            else if ( $token[0] === T_NAMESPACE )
            {
                $namespace = '\\';
                while ( $token = (array) next( $tokens ) )
                {
                    if ( in_array( $token[0], array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ) ) )
                    {
                        continue;
                    }

                    if ( $token[0] === ';' || $token[0] === '{' )
                    {
                        $namespace .= '\\';
                        break;
                    }
                    $namespace .= $token[1];
                }
            }
            else if ( $token[0] === T_CLASS )
            {
                $className = '';
                while ( $token = (array) next( $tokens ) )
                {
                    if ( in_array( $token[0], array( T_WHITESPACE, T_COMMENT, T_DOC_COMMENT ) ) )
                    {
                        continue;
                    }
                    $className = $token[1];
                    break;
                }
            }

            if ( $isAspect && $className )
            {
                $this->aspects[] = $namespace . $className;
                
                $className = '';
                $namespace = '';
                $isAspect  = false;
            }
        }
    }
}

$s = microtime( true );

for ( $i = 0; $i < 1; ++$i )
{
    $registry = new \de\buzz2ee\aop\DefaultAdviceRegistry();
    $scanner = new ModificationScanner( __DIR__ . '/scanner', $registry );
    if ( $scanner->scan() )
    {
        var_dump( $scanner->getAspects() );
    }
}



printf( "Scanning took %.8f seconds\n", ( microtime( true ) - $s ) );
