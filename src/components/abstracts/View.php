<?php

namespace sszcore\components\abstracts;

use Illuminate\Support\Collection;
use sszcore\traits\AdaptizerPropertyTrait;
use sszcore\traits\CachePropertyTrait;
use sszcore\traits\ComponentTrait;
use sszcore\traits\ConfigPropertyTrait;
use sszcore\traits\UrlPropertyTrait;
use sszcore\traits\ViewHtmlElementRenderTrait;
use voku\helper\HtmlMin;
use sszcore\components\Request;
use sszcore\components\Str;
use sszcore\models\Component\View\Pagination;
use sszcore\models\Component\View\Manager;

/**
 * Class View
 * @package sszcore\components
 * @since 0.1.5
 *
 * @property Request request
 * @property Collection mutate_properties
 */
abstract class View
{
    use ComponentTrait;
    use ConfigPropertyTrait;
    use CachePropertyTrait;
    use UrlPropertyTrait;
    use AdaptizerPropertyTrait;
    use ViewHtmlElementRenderTrait;

    const BLOCK_HEAD                = 'blockHead';
    const BLOCK_BODY_START          = 'blockBodyStart';
    const BLOCK_BODY_END            = 'blockBodyEnd';
    const BLOCK_BODY_END_SCRIPT     = 'blockBodyEndScript';
    const BLOCK_CONTENT             = 'blockBodyContent';

    /**
     * @var Request
     */
    public $request;

    // properties
    public $view_dir                = '';
    public $app_view_dir            = '';
    public $headContent             = [];
    public $footContent             = [];
    public $layoutName              = 'main';
    public $layout                  = '';
    public $params                  = [];
    public $bodyClass               = [];
    public $pageTitle               = [];
    public $pageDescription         = [];
    public $breadcrumb              = [];
    public $htmlClass               = [];
    public $layoutClass             = [];
    public $blockContent            = [];
    public $blockHead               = [];
    public $blockBodyStart          = [];
    public $blockBodyEnd            = [];
    public $blockBodyEndScript      = [];
    public $templates               = [];
    public $templateError           = [];

    public $user = null;
    public $helper = null;

    /**
     * @param $name
     * @return array|mixed|null
     */
    public function __get( $name )
    {
        $method = 'get' . Str::studly( $name ) . 'Attribute';
        $property = "_{$name}";

        if ( method_exists( $this, $method ) ) {
            return $this->$method();
        }

        if ( property_exists( $this, $property ) ) {
            return $this->$property;
        }

        if ( isset( $this->attributes[ $name ] ) ) {
            return $this->attributes[ $name ];
        }

        if ( !property_exists( $this, $name ) ) {

            /**
             * To make property prefix with `param` like paramBusiness or paramCompany to come from the getParams()
             */
            if ( Str::startsWith( $name, 'param' ) ) {
                $paramName = Str::camel( str_replace( 'param', '', $name ) );
                return $this->getParams( $paramName );
            }
        }

        return null;
    }

    /**
     * Initialize Component Auth
     *
     * @TODO Need to configurize this into Configs
     * @return mixed
     */
    public function initializeComponentAuth( array $configs = [] )
    {
        $class = "\\sszcore\\component\\Auth";
        return $class::getInstance( $configs );
    }

    /**
     * Initialize ViewBootstrap
     *
     * @TODO Need to configurize this into Configs
     * @return mixed
     */
    public function initializeViewBootstrap( array $configs = [] )
    {
        $class = "\\app\\component\\ViewBootstrap";
        return new $class( $configs );
    }

    /**
     * Initialize ViewHelper
     *
     * @TODO Need to configurize this into Configs
     * @param View $componentView
     * @return mixed
     */
    public function initializeViewHelper( $componentView )
    {
        $class = "\\app\\component\\ViewHelper\\ViewHelper";
        return new $class( $componentView );
    }

    /**
     * Initialize ViewWidget
     *
     * @TODO Need to configurize this into Configs
     * @param View $componentView
     * @return mixed
     */
    public function initializeViewWidget( $componentView )
    {
        $class = "\\app\\component\\ViewWidget\\ViewWidget";
        return new $class( $componentView );
    }

    /**
     * @param array $config
     */
    public function __construct( array $config = [] )
    {
        if ( !method_exists( $this, 'getInstance' ) ) {
            throw new \Error( 'Missing Singleton! This class should be use with Singleton!' );
        }

        set_error_handler( [ $this, 'errorHandler' ] );

        // components
        $this->auth = $this->initializeComponentAuth( $config );
        $this->request = Request::createFromGlobals();

        // project specific properties
        $this->view_dir = "{$this->site_dir}/views";

        // set properties
        $this->app_view_dir = "{$this->app_dir}/views";
        $this->setLayout( 'main' );
        $this->setPageTitle( $this->config->getAppConfig( 'name' ) );
        $this->setupCommonBlock();

        $this->bootstrap = $this->initializeViewBootstrap( $config );

        /**
         * Setup View Helper
         * To register View Helper, go to `app/components/ViewHelper` and create new components and add property for
         * that component in app/components/ViewHelper/ViewHelper.php
         */
        $this->helper = $this->initializeViewHelper( $this );

        /**
         * @since 0.1.5 View Widget - Process and render view in the same component, triggered on use, and only render once-reusable
         */
        if ( $initViewWidget = $this->initializeViewWidget( $this ) ) {
            $this->widget = $initViewWidget;
        }

        // set user if logged-in
        $this->user = $this->auth->user;
    }

    /**
     * Set custom error handler
     *
     * @see https://www.php.net/manual/en/function.set-error-handler.php
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     *
     * @return bool
     */
    public function errorHandler( $errno, $errstr, $errfile, $errline )
    {
        if ( !( error_reporting() & $errno ) ) {
            // This error code is not included in error_reporting, so let it fall
            // through to the standard PHP error handler
            return false;
        }

        $message = $errstr;
        $line = $errline;
        $file = str_replace( '.php', '', $errfile );
        $fileParse = explode( '/views/', $file );
        if ( $fileParse[ 1 ] ?? false ) {
            $file = $fileParse[ 1 ];
        }
        $file = "{$file}:{$line}";
        $this->addTemplateError( "<div>{$message}</div><div>{$file}</div><hr />" );
        error_log( "{$message} {$file}" );
        // error_log( json_encode( debug_backtrace() ) );

        /* Don't execute PHP internal error handler */
        // return true;
    }

    /**
     * @param string $message
     * @param string $key
     */
    public function addTemplateError( string $message, string $key = '' )
    {
        if ( $key ) {
            $this->templateError[ $key ] = $message;
        } else {
            $this->templateError[] = $message;
        }
    }

    /**
     * @return string
     */
    public function renderTemplateError()
    {
        return implode( '<br />', $this->templateError );
    }

    /**
     * @param array $class
     * @param string $key
     */
    public function setLayoutClass( array $class, string $key = 'default' )
    {
        $this->layoutClass[ $key ] = $class;
    }

    /**
     * @param array $class
     * @param string $key
     */
    public function addLayoutClass( array $class, string $key = 'default' )
    {
        if ( !isset( $this->layoutClass[ $key ] ) ) {
            $this->layoutClass[ $key ] = [];
        }

        $this->layoutClass[ $key ] = array_merge( $this->layoutClass[ $key ], $class );
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getLayoutClass( $key = 'default' )
    {
        return $this->layoutClass[ $key ];
    }

    /**
     * @param string $key
     * @param array $default
     * @return string
     */
    public function renderLayoutClass( $key = 'default', array $default = [] )
    {
        if ( !isset( $this->layoutClass[ $key ] ) ) {
            return $this->renderClassNames( $default );
            // throw new Exception( "Class layout key \"{$key}\" is not found!" );
        }

        return $this->renderClassNames( $this->layoutClass[ $key ] );
    }

    public function setupCommonBlock()
    {
        if ( $gtmCode = $this->config->getAppConfig( 'gtmCode' ) ) {
            $key = 'google-tag-manager';
            $this->blockHead[ $key ] = $this->getComponent( 'scripts/google-tag-manager/head' );
            $this->blockBodyStart[ $key ] = $this->getComponent( 'scripts/google-tag-manager/body', [ 'gtmCode' => $gtmCode ] );

            if ( $user = $this->auth->user ) {
                $key = 'ga-data-layer-logged-in-user';
                $this->blockBodyEnd[ $key ] = $this->getComponent( 'scripts/google-analytics/data-layer/logged-in-user', [
                    'userId' => $user->id,
                    'username' => $user->username,
                    'userType' => $user->role
                ] );
            }
        }
    }

    public function block()
    {
        // Apparently the system has become slower over the days (with so many changes during MCO lol)
        // I thing I found that making the system slow are the rendering of the views - HTML output to browser
        // @see https://www.php.net/manual/en/function.ob-gzhandler.php
        // @see https://stackoverflow.com/questions/6010403/how-to-determine-wether-ob-start-has-been-called-already
        if ( !in_array( 'ob_gzhandler', ob_list_handlers() ) ) {
            ob_start( 'ob_gzhandler' );
        } else {
            ob_start();
        }
    }

    /**
     * @param string $block
     * @param string $key
     */
    public function blockEnd( string $block = '', string $key = '' )
    {
        $render = ob_get_contents();
        ob_end_clean();

        if ( !$block ) {
            echo $render;
        }

        if ( $key ) {
            $this->$block[ $key ] = $render;
        } else {
            $this->$block[] = $render;
        }
    }

    /**
     * @param string $contentName
     */
    public function blockEndContent( string $contentName )
    {
        $render = ob_get_contents();
        ob_end_clean();
        $this->blockContent[ $contentName ] = $render;
    }

    /**
     * Set view params
     *
     * @param array $params
     */
    public function setParams( array $params )
    {
        $this->params = $params;
    }

    /**
     * Replace or merge view params
     *
     * @param array $params
     */
    public function addParams( array $params )
    {
        $this->params = array_merge( $this->params, $params );
    }

    /**
     * Set layout template name and directory
     *
     * @param string $templateName
     */
    public function setLayout( string $templateName )
    {
        $this->layoutName = 'layouts/' . $templateName;
        $template = $this->view_dir . '/' . $this->layoutName . '.php';
        if ( !file_exists( $template ) ) {
            $template = $this->app_view_dir . '/' . $this->layoutName . '.php';
            if ( !file_exists( $template ) ) {
                throw new \Error( 'Template file ' . $template . ' not exist!' );
            }
        }

        $this->layout = $template;
    }

    /**
     * @return string
     */
    public function getLayoutName()
    {
        if ( !$this->layoutName ) {
            return '';
        }
        return str_replace( '/', '--', $this->layoutName );
    }

    /**
     * @param array $htmlClass
     */
    public function setHtmlClass( array $htmlClass )
    {
        if ( is_array( $htmlClass ) && $htmlClass ) {
            $this->htmlClass = $htmlClass;
        }
    }

    /**
     * @param string $htmlClass
     */
    public function addHtmlClass( string $htmlClass )
    {
        $this->htmlClass[] = $htmlClass;
    }

    /**
     * @return string
     */
    public function getHtmlClass()
    {
        /**
         * Inject theme class if available
         */
        if ( $theme = $this->config->get( 'app.theme' ) ) {
            $this->htmlClass[ 'theme' ] = 'html-theme-' . $this->config->get( 'app.theme', '' );
        }
        return implode( ' ', $this->htmlClass );
    }

    /**
     * Set head content
     *
     * @param string $content
     */
    public function setHeadContent( string $content )
    {
        $this->headContent = [];
        $this->headContent[] = $content;
    }

    /**
     * @param string $content
     */
    public function addHeadContent( string $content )
    {
        $this->headContent[] = $content;
    }

    /**
     * @return string
     */
    public function getHeadContent()
    {
        return implode( $this->headContent, '' );
    }

    /**
     * @param string $content
     */
    public function setFootContent( string $content )
    {
        $this->footContent = [];
        $this->footContent[] = $content;
    }

    /**
     * @param string $content
     */
    public function addFootContent( string $content )
    {
        $this->footContent[] = $content;
    }

    /**
     * @return string
     */
    public function getFootContent()
    {
        return implode( $this->footContent, '' );
    }

    /**
     * @param array $bodyClass
     */
    public function setBodyClass( array $bodyClass )
    {
        if ( is_array( $bodyClass ) && $bodyClass ) {
            $this->bodyClass = $bodyClass;
        }
    }

    /**
     * @param string $bodyClass
     */
    public function addBodyClass( string $bodyClass )
    {
        $this->bodyClass[] = $bodyClass;
    }

    /**
     * @return string
     */
    public function getBodyClass()
    {
        return implode( $this->bodyClass, ' ' );
    }

    /**
     * @param string $pageTitle
     */
    public function setPageTitle( string $pageTitle )
    {
        $this->pageTitle = [];
        $this->pageTitle[] = $pageTitle;
    }

    /**
     * @param string $pageTitle
     */
    public function addPageTitle( string $pageTitle )
    {
        $this->pageTitle[] = $pageTitle;
    }

    /**
     * @param string $delimiter
     * @return string
     */
    public function getPageTitle( string $delimiter = ' - ' )
    {
        $titles = $this->pageTitle;
        krsort( $titles );
        return implode( $titles, $delimiter );
    }

    /**
     * @param string $pageDescription
     */
    public function setPageDescription( string $pageDescription )
    {
        $this->pageDescription = [];
        $this->pageDescription[] = $pageDescription;
    }

    /**
     * @param string $pageDescription
     */
    public function addPageDescription( string $pageDescription )
    {
        $this->pageDescription[] = $pageDescription;
    }

    /**
     * @param string $delimiter
     * @return string
     */
    public function getPageDescription( string $delimiter = '. ' )
    {
        return trim( implode( $this->pageDescription, $delimiter ) );
    }

    /**
     * @param array $breadcrumb\
     */
    public function setBreadcrumb( array $breadcrumb )
    {
        $this->breadcrumb = [];
        $this->breadcrumb = array_merge( $this->breadcrumb, $breadcrumb );
    }

    /**
     * @param string $key
     * @param string $label
     * @param string $url
     */
    public function addBreadcrumb( string $key, string $label, string $url )
    {
        $this->breadcrumb[ $key ] = [
            'label' => $label,
            'url' => $url
        ];
    }

    /**
     * @return array
     */
    public function getBreadcrumb()
    {
        return $this->breadcrumb;
    }

    /**
     * @return string
     */
    public function getBlockHead()
    {
        return implode( '', $this->blockHead );
    }

    /**
     * @return string
     */
    public function getBlockBodyStart()
    {
        return implode( '', $this->blockBodyStart );
    }

    /**
     * @return string
     */
    public function getBlockBodyEnd()
    {
        return implode( '', $this->blockBodyEnd );
    }

    /**
     * @return string
     */
    public function getBlockBodyEndScript()
    {
        return implode( '', $this->blockBodyEndScript );
    }

    /**
     * @param $contentName
     * @return string
     */
    public function getBlockContent( $contentName ) : string
    {
        if ( isset( $this->blockContent[ $contentName ] ) ) {
            return $this->blockContent[ $contentName ];
        }

        return '';
    }

    /**
     * @param $e
     */
    public function staticRenderErrorHandler( $e )
    {
        $message = $e->getMessage();
        $line = $e->getLine();
        $file = str_replace( '.php', '', $e->getFile() );
        $fileParse = explode( '/views/', $file );
        if ( $fileParse[ 1 ] ?? false ) {
            $file = $fileParse[ 1 ];
        }
        $file = "{$file}:{$line}";
        $this->addTemplateError( "<div>{$message}</div><div>{$file}</div>" );
        error_log( $e );
    }

    /**
     * @param string $templateName
     * @return string
     */
    public function render( string $templateName )
    {
        // render content first
        $content = '';
        try {
            $content = $this->staticRender( $templateName, $this->getData() );
        } catch ( \Exception $e ) {
            $this->staticRenderErrorHandler( $e );
        } catch ( \Error $e ) {
            $this->staticRenderErrorHandler( $e );
        }

        // render content with layout via content param
        return $this->staticRender( $this->layoutName, array_merge( $this->getData(), [
            'content' => $content
        ] ) );
    }

    /**
     * @return array
     */
    public function getData()
    {
        return [
            'config'            => $this->config,
            'params'            => $this->params,
            'layout'            => $this->layout,
            'headContent'       => $this->getHeadContent(),
            'footContent'       => $this->getFootContent(),
            'bodyClass'         => $this->getBodyClass(),
            'pageTitle'         => $this->getPageTitle(),
            'pageDescription'   => $this->getPageDescription(),
            'breadcrumb'        => $this->getBreadcrumb(),
            'htmlClass'         => $this->getHtmlClass()
        ];
    }

    /**
     * @param string $templateName
     * @param array $params
     * @return string
     */
    public function staticRenderMinify( string $templateName, array $params = [] )
    {
        $output = $this->staticRender( $templateName, $params );
        $htmlMin = new HtmlMin;
        return $htmlMin->minify( $output );
    }

    /**
     * @param string $templateName
     * @param array $params
     * @param string $cacheKey
     * @param false $expiry
     * @return false|mixed|string
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function staticRenderCached( string $templateName, array $params = [], string $cacheKey, $expiry = false )
    {
        if ( !$output = $this->cache->get( $cacheKey ) ) {
            $output = $this->staticRender( $templateName, $params );
            $this->cache->set( $cacheKey, $output, $expiry );
        }

        return $output;
    }

    /**
     * @param string $templateName
     * @return string
     */
    public function getAdaptiveTemplateName( string $templateName )
    {
        $mode = !$this->adaptizer->isDesktop ? $this->adaptizer->getMode() : '';
        $paths = explode( '/', $templateName );
        $lastKey = count( $paths ) - 1;
        $paths[ $lastKey ] = ( $mode ? $mode . '/' : '' ) . $paths[ $lastKey ];
        return implode( '/', $paths );
    }

    /**
     * @param string $templateName
     * @return string
     */
    public function getAuthTemplateName( string $templateName )
    {
        $auth = $this->auth->isAuth() ? 'private' : 'public';
        $paths = explode( '/', $templateName );
        $lastKey = count( $paths ) - 1;
        $paths[ $lastKey ] = $auth . '/' . $paths[ $lastKey ];
        return implode( '/', $paths );
    }

    /**
     * @param   string  $templateName
     * @param   array   $params
     * @return  string
     */
    public function staticRender( string $templateName, array $params = [] )
    {
        if ( !isset( $this->templates[ $templateName ] ) ) {

            $authTemplateName = $this->getAuthTemplateName( $templateName );
            $authModeTemplateName = $this->getAdaptiveTemplateName( $authTemplateName );
            $modeTemplateName = $this->getAdaptiveTemplateName( $templateName );
            $appId = $this->config->get( 'app.id', 'unset-app-id' );

            $role = 'role-unset';
            if ( $this->auth->isAuth() && $this->auth->user->role ) {
                $role = Str::slug( $this->auth->user->role );
            }

            $templatePaths = [
                "{$this->view_dir}/{$appId}/{$role}/{$authModeTemplateName}.php",
                "{$this->view_dir}/{$appId}/{$role}/{$modeTemplateName}.php",
                "{$this->view_dir}/{$appId}/{$role}/{$templateName}.php",
                "{$this->view_dir}/{$appId}/{$authModeTemplateName}.php",
                "{$this->view_dir}/{$appId}/{$modeTemplateName}.php",
                "{$this->view_dir}/{$appId}/{$templateName}.php",
                "{$this->view_dir}/{$role}/{$authModeTemplateName}.php",
                "{$this->view_dir}/{$role}/{$modeTemplateName}.php",
                "{$this->view_dir}/{$role}/{$templateName}.php",
                "{$this->view_dir}/{$authModeTemplateName}.php",
                "{$this->view_dir}/{$modeTemplateName}.php",
                "{$this->view_dir}/{$templateName}.php",

                "{$this->app_view_dir}/{$appId}/{$role}/{$authModeTemplateName}.php",
                "{$this->app_view_dir}/{$appId}/{$role}/{$modeTemplateName}.php",
                "{$this->app_view_dir}/{$appId}/{$role}/{$templateName}.php",
                "{$this->app_view_dir}/{$appId}/{$authModeTemplateName}.php",
                "{$this->app_view_dir}/{$appId}/{$modeTemplateName}.php",
                "{$this->app_view_dir}/{$appId}/{$templateName}.php",
                "{$this->app_view_dir}/{$authModeTemplateName}.php",
                "{$this->app_view_dir}/{$modeTemplateName}.php",
                "{$this->app_view_dir}/{$templateName}.php",
            ];

            $template = '';
            foreach ( $templatePaths as $path ) {
                if ( file_exists( $path ) ) {
                    $template = $path;
                    break;
                }
            }

            /**
             * Throw error if template file is not exist
             */
            if ( !$template ) {
                $this->addTemplateError( "Template file {$templateName} not exist!" );
                return '';
            }

            $this->templates[ $templateName ] = $template;
        }

        $template = $this->templates[ $templateName ];
        $params = array_merge( $params, [
            'template' => $template
        ] );

        extract( $params, EXTR_SKIP );

        // Apparently the system has become slower over the days (with so many changes during MCO lol)
        // I thing I found that making the system slow are the rendering of the views - HTML output to browser
        // @see https://www.php.net/manual/en/function.ob-gzhandler.php
        // @see https://stackoverflow.com/questions/6010403/how-to-determine-wether-ob-start-has-been-called-already
        if ( !in_array( 'ob_gzhandler', ob_list_handlers() ) ) {
            ob_start( 'ob_gzhandler' );
        } else {
            ob_start();
        }
        require( $template );
        $renderOutput = ob_get_contents();

        /**
         * @TODO Need to check, for some reason ob_end_clean() throwing error `failed to delete buffer. No buffer to delete`
         */
        ob_end_clean();

        return $renderOutput;
    }

    public function getParams( string $key = '', $default = null )
    {
        if ( $key ) {
            return data_get( $this->params, $key ) ?: $default;
        }

        return $default;
    }

    /**
     * To be use to render REST responses
     *
     * @param array $array
     * @param int $httpResponseCode
     */
    public function renderAsJson( array $array = [], int $httpResponseCode = 200 )
    {
        http_response_code( $httpResponseCode );
        header( 'Content-Type: application/json' );
        echo json_encode( $array, JSON_UNESCAPED_SLASHES );
    }

    /**
     * Check if controller name is exist
     *
     * @param  string $name
     * @return bool
     */
    public function isController( string $name ) : bool
    {
        return $this->getParams( 'controllerName' ) === $name;
    }

    public function isControllerName( string $name ) : bool
    {
        return $this->getParams( 'controllerNameLong' ) === $name;
    }

    /**
     * Check if action name is exist
     *
     * @param  string $name
     * @return bool
     */
    public function isAction( string $name ) : bool
    {
        return $this->getParams( 'actionName' ) === $name;
    }

    /**
     * Check if action ID is exist
     *
     * @param  string $name
     * @return bool
     */
    public function isActionId( string $name ) : bool
    {
        return $this->getParams( 'actionId' ) === $name;
    }

    /**
     * Check if action ID long is exist
     *
     * @param  string $name
     * @return bool
     */
    public function isActionIdLong( string $name ) : bool
    {
        return $this->getParams( 'actionIdLong' ) === $name;
    }

    /**
     * Make pagination data
     *
     * @param  string|null  $uri
     * @param  integer $current         page number
     * @param  int     $list            total item showing/found
     * @param  int     $pages           total available pages
     * @param  int     $total           total available items
     *
     * @return array
     */
    public function makePagination( string $uri = null, int $current = 1, int $list, int $pages, int $total ) : array
    {
        $pagination = [];
        $current = $current ?: 1;

        $pagination['next'] = [
            'disabled' => ( $current >= $pages  ),
            'url' => ( $uri ? $uri . '?page=' . ( $current + 1 ) : $this->url->current( [ 'page' => ( $current + 1 ) ] ) )
        ];
        $pagination['prev'] = [
            'disabled' => ( $current === 1 ),
            'url' => ( $uri ? $uri . '?page=' . ( $current - 1 ) : $this->url->current( [ 'page' => ( $current - 1 ) ] ) )
        ];

        $pagination[ 'current' ] = $current;
        $pagination[ 'pages' ] = $pages;
        $pagination[ 'total' ] = $total;
        $pagination[ 'list' ] = $list < $total ? $list : $total;
        $pagination[ 'page' ] = $current;

        return $pagination;
    }


    /**
     * Keep Component Template Name
     * @var string
     */
    public $componentRenderTemplateName = '';

    /**
     * Keep Component Template Parameters
     * @var array
     */
    public $componentRenderTemplateParams = [];

    /**
     * Advanced static render with content wrapping support
     *
     * @param string $templateName
     * @param array $params
     */
    public function componentRender( string $templateName, array $params = [] )
    {
        $this->componentRenderTemplateName = $templateName;
        $this->componentRenderTemplateParams = $params;
        ob_start();
    }

    /**
     * End for advanced static render with content wrapping support
     */
    public function componentRenderEnd()
    {
        $children = ob_get_contents();
        ob_end_clean();
        $children = trim( $children );
        echo $this->staticRender( $this->componentRenderTemplateName, array_merge( [ 'children' => $children ], $this->componentRenderTemplateParams ) );
    }

    /**
     * @deprecated
     * @param string $templateName
     * @param array $params
     * @return string
     */
    public function getComponent( string $templateName, array $params = [] )
    {
        $templateName = "components/{$templateName}";
        return $this->staticRender( $templateName, $params );
    }

    /**
     * @deprecated
     * @param string $templateName
     * @param array $params
     * @return string
     */
    public function getWidget( string $templateName, array $params = [] )
    {
        $templateName = "components/widgets/$templateName";
        return $this->staticRender( $templateName, $params );
    }

    /**
     * @deprecated
     * Get Form Component
     *
     * @param  array  $params
     * @return string
     */
    public function getFormComponent( array $params = [] ) : string
    {
        return $this->bootstrap->form->get( $params );
    }

    public $varDumperData = [];

    /**
     * @param $dump
     * @param string $key
     */
    public function addVarDumper( $dump, string $key = '' )
    {
        if ( $key ) {
            $this->varDumperData[ $key ] = $dump;
        } else {
            $this->varDumperData[] = $dump;
        }
    }

    /**
     * @return array
     */
    public function getVarDumper()
    {
        return $this->varDumperData;
    }

    public function renderVarDumper()
    {
        if ( $this->varDumperData ) {
            foreach ( $this->varDumperData as $key => $data ) {
                echo "<div class=\"opacity-point-5 font-weight-bold mb-3\">Var Dump: {$key}</div>";
                echo "<pre class=\"mb-5 text-white\">";
                echo htmlentities( print_r( $data, true ) );
                echo "</pre>";
            }
        }
    }

    /**
     * @deprecated
     * @return Collection
     */
    public function getManagerQueryItems()
    {
        return $this->getParams( 'queryItems', new Collection );
    }

    /**
     * @return Manager
     */
    public function getManager()
    {
        $manager = new Manager();
        $manager->setAttributes( [
            'parameterKeyword'          => $this->getParams( 'parameterKeyword', '' ),
            'parameterPaged'            => $this->getParams( 'parameterPaged', 0 ),
            'parameterTotalAvailable'   => $this->getParams( 'parameterTotalAvailable', 0 ),
            'parameterTotalShowing'     => $this->getParams( 'parameterTotalShowing', 0 ),
            'parameterPagesAvailable'   => $this->getParams( 'parameterPagesAvailable', 0 ),
            'parameterOffset'           => $this->getParams( 'parameterOffset', 0 ),
            'parameterLimit'            => $this->getParams( 'parameterLimit', 0 ),
            'queryItems'                => $this->getParams( 'queryItems' ),
            'pagination'                => $this->getParams( 'pagination' ),
        ] );
        return $manager;
    }

    /**
     * @return Pagination
     */
    public function getPagination()
    {
        $pagination = new Pagination();
        $pagination->setAttributes( $this->getParams( 'pagination' ) );
        return $pagination;
    }

    /**
     * Get Mutate Properties
     * @return Collection
     */
    public function getMutatePropertiesAttribute()
    {
        return collect( $this->getParams( 'mutateProperties', [] ) );
    }
}
