<?php

namespace sszcore\console\controllers\Maker;

use sszcore\components\abstracts\ConsoleController;
use sszcore\components\Console\Color;
use sszcore\components\Str;

/**
 * Class MakerController
 * @package sszcore\console\controllers\Maker
 * @since 0.2.0
 */
class MakerController extends ConsoleController
{
    public function run()
    {
        if ( !$classname = $this->params->get( 'classname' ) ) {
            $this->echo( 'Missing Parameter: classname', Color::RED_NAME );
            die();
        }

        if ( !$template = $this->params->get( 'template' ) ) {
            $this->echo( 'Missing Parameter: template', Color::RED_NAME );
            $this->echoBreak();
            die();
        }

        $classnameArray = explode( '\\', $classname );

        // create controller class name
        $classname = Str::studly( $classnameArray[ count( $classnameArray ) - 1 ] ) . 'Controller';

        // create namespace
        unset( $classnameArray[ count( $classnameArray ) - 1 ] );
        $namespace = implode( '\\', $classnameArray );

        // get template
        $templateDir = realpath( __DIR__ . '/templates' );
        $template = "{$templateDir}/console/{$template}.txt";
        if ( !file_exists( $template ) ) {
            $this->echo( 'Error: Template not exist!', Color::RED_NAME );
            $this->echoBreak();
            die();
        }

        // create template
        $templateContent = file_get_contents( $template );
        $templateContent = str_replace( '__MAKER_NAMESPACE__', $namespace, $templateContent );
        $templateContent = str_replace( '__MAKER_CONTROLLER_NAME__', $classname, $templateContent );
        $templateContent = str_replace( '__MAKER_CREATED_AT__', date( 'Y-m-d g:i A' ), $templateContent );

        $fileNamespaceDir = str_replace( '\\', '/', $namespace );
        $fileDir = "{$this->config->app_dir}/console/controllers/{$fileNamespaceDir}";
        $file = "{$this->config->app_dir}/console/controllers/{$fileNamespaceDir}/{$classname}.php";

        if ( !file_exists( $fileDir ) ) {
            mkdir( $fileDir, 0755, true );
        }

        if ( file_exists( $file ) ) {
            $this->echo( 'Error: Controller with the same name already exist!', Color::RED_NAME );
            $this->echoBreak();
            die();
        }

        file_put_contents( $file, $templateContent );

        $this->echoBreak();
        $this->echo( "TEMPLATE:  {$template}" );
        $this->echo( "NAMESPACE: {$namespace}" );
        $this->echo( "CLASSNAME: {$classname}" );
        $this->echo( "FILE_DIR:  {$fileDir}" );
        $this->echo( "FILE:      {$file}" );
        $this->echoBreak();
        $this->echoBreak();

        $this->echo( "File {$classname}.php created at directory {$fileDir}", Color::GREEN_NAME );
        $this->echoBreak();
    }
}