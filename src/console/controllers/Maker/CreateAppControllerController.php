<?php

namespace sszcore\console\controllers\Maker;

use sszcore\components\abstracts\ConsoleController;
use sszcore\components\Console\Color;
use sszcore\components\Str;
use sszcore\traits\CreateTemplateNameFromClassname;

/**
 * Class CreateAppControllerController
 * @package sszcore\console\controllers\Maker
 * @since 0.2.0
 */
class CreateAppControllerController extends ConsoleController
{
    use CreateTemplateNameFromClassname;

    /**
     * @return string
     */
    public function getTemplateDir()
    {
        return realpath( __DIR__ . '/templates' );
    }

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

        $viewTemplateNameMapper = [
            'Controller' => 'controller',
            'ManagerController' => 'manager',
            'CreateUpdateController' => 'create-update',
        ];
        $viewTemplateName = $viewTemplateNameMapper[ $template ] ?? '';

        $managerModel = $this->params->get( 'model', 'DummyModel' );

        $classnameArray = explode( '\\', $classname );

        // create controller class name
        $classname = Str::studly( $classnameArray[ count( $classnameArray ) - 1 ] ) . 'Controller';

        // create namespace
        unset( $classnameArray[ count( $classnameArray ) - 1 ] );
        $namespace = implode( '\\', $classnameArray );

        // get controller template
        $templateDir = $this->getTemplateDir();
        $template = "{$templateDir}/app/controllers/{$template}.txt";
        if ( !file_exists( $template ) ) {
            $this->echo( 'Error: Template not exist!', Color::RED_NAME );
            $this->echoBreak();
            die();
        }

        // create controller template
        $templateContent = file_get_contents( $template );
        $templateContent = str_replace( '__MAKER_NAMESPACE__', $namespace, $templateContent );
        $templateContent = str_replace( '__MAKER_CONTROLLER_NAME__', $classname, $templateContent );
        $templateContent = str_replace( '__MAKER_MANAGER_MODEL__', $managerModel, $templateContent );
        $templateContent = str_replace( '__MAKER_CREATED_AT__', date( 'Y-m-d g:i A' ), $templateContent );

        $fileNamespaceDir = str_replace( '\\', '/', $namespace );
        $fileDir = "{$this->config->app_dir}/controllers/{$fileNamespaceDir}";
        $file = "{$this->config->app_dir}/controllers/{$fileNamespaceDir}/{$classname}.php";

        if ( !file_exists( $fileDir ) ) {
            mkdir( $fileDir, 0755, true );
        }

        $override = (int)$this->params->get( 'override' );
        if ( !$override && file_exists( $file ) ) {
            $this->echo( 'Error: Controller with the same name already exist!', Color::RED_NAME );
            $this->echoBreak();
            die();
        }

        file_put_contents( $file, $templateContent );

        // create view template
        $viewFile = "{$this->config->app_dir}/views/" . $this->createTemplateNameFromClassname( "{$namespace}\\{$classname}" ) . '.php';
        $viewFileDirArr = explode( '/', $viewFile );
        $viewFileName = $viewFileDirArr[ count( $viewFileDirArr ) - 1 ];
        unset( $viewFileDirArr[ count( $viewFileDirArr ) - 1 ] );
        $viewFileDir = implode( '/', $viewFileDirArr );

        if ( !file_exists( $viewFileDir ) ) {
            mkdir( $viewFileDir, 0755, true );
        }

        $viewTemplate = "{$templateDir}/app/views/{$viewTemplateName}.txt";

        // create view template
        $templateViewContent = file_get_contents( $viewTemplate );
        $templateViewContent = str_replace( '__MAKER_CONTROLLER_NAME__', $classname, $templateViewContent );
        $templateViewContent = str_replace( '__MAKER_CREATED_AT__', date( 'Y-m-d g:i A' ), $templateViewContent );

        $override = (int)$this->params->get( 'override' );
        if ( !$override && file_exists( $viewFile ) ) {
            $this->echo( 'Error: View with the same name already exist!', Color::RED_NAME );
            $this->echoBreak();
            die();
        }

        file_put_contents( $viewFile, $templateViewContent );

        $this->echoBreak();
        $this->echo( "TEMPLATE:        {$template}" );
        $this->echo( "NAMESPACE:       {$namespace}" );
        $this->echo( "CLASSNAME:       {$classname}" );
        $this->echo( "CLASS_FILE_DIR:  {$fileDir}" );
        $this->echo( "CLASS_FILE:      {$file}" );
        $this->echo( "VIEW_TEMPLATE:   {$viewTemplate}" );
        $this->echo( "VIEW_FILE_DIR:   {$viewFileDir}" );
        $this->echo( "VIEW_FILE:       {$viewFile}" );

        $namespaceToUri = strtolower( str_replace( '\\', '/', $namespace ) );
        $namespaceToRouteName = strtolower( str_replace( '\\', ' ', $namespace ) );

        $routeName          = Str::camel( "{$namespaceToRouteName}-{$classname}" );
        $routeUri           = '/' . $namespaceToUri . '/' . Str::snake( $classname, '-' );
        $routeController    = '\\\app\\\controllers\\\\' . $namespace . '\\\\' . $classname;
        $routeCode          = '\'' . $routeName . '\' => [ \''. $routeUri .'\', [ \''. $routeController .'\', \'index\' ] ]';

        $this->echo( "ROUTE NAME:      {$routeName}" );
        $this->echo( "ROUTE URI:       {$routeUri}" );
        $this->echo( "ROUTE CODE:" );
        $this->echo( "                 {$routeCode}", Color::YELLOW_NAME );

        $this->echoBreak();
        $this->echoBreak();

        $this->echo( "File {$classname}.php created at directory {$fileDir}", Color::GREEN_NAME );
        $this->echo( "File {$viewFileName} created at directory {$viewFileDir}", Color::GREEN_NAME );
        $this->echoBreak();
    }
}