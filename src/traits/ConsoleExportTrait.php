<?php

namespace sszcore\traits;

use sszcore\components\Console\Color;
use sszcore\components\Str;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Exception;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Trait ConsoleExportTrait
 * @package app\traits
 * @since 0.2.6
 *
 * @property string export_file_name
 * @property string export_dir
 * @property string export_dir_path
 * @property string export_file_url
 * @property string export_file_path
 */
trait ConsoleExportTrait
{
    use ConsoleComponentTrait;
    use ConfigPropertyTrait;
    use UrlPropertyTrait;

    public $configuration = [];

    /**
     * @return false
     */
    public function getExportAsCsv()
    {
        return false;
    }

    public function getHeaders()
    {
        return [];
    }

    /**
     * @return Collection
     */
    public function getItems()
    {
        return new Collection();
    }

    /**
     * @return array
     */
    public function getRecords()
    {
        $this->records = [];
        $this->records[] = $this->getHeaders();

        $this->loopBefore();

        if ( $items = $this->getItems() ) {
            foreach ( $items as $item ) {
                $this->loopItem = $item;
                $this->loopRecord( $item );
                $this->loopIndex++;
            }
        }

        $this->loopAfter();

        return $this->records;
    }

    public function getDefaultName()
    {
        $reflection = new \ReflectionClass( $this );
        $name = Str::replace( 'Controller', '', $reflection->getName() );
        $name = Str::replace( 'console', '', $name );
        $name = Str::replace( '\\', '', $name );
        return Str::snake( $name, '-' );
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->getDefaultName();
    }

    /**
     * @return string
     */
    public function getExportFileNameAttribute()
    {
        if ( !isset( $this->attributes[ 'export_file_name' ] ) ) {
            $date = date('Y-m-d-H-i-s');
            return $this->attributes[ 'export_file_name' ] = $this->getFileName() . "-{$date}" . ( $this->getExportAsCsv() ? '.csv' : '.xlsx' );
        }
        return $this->attributes[ 'export_file_name' ] ?? '';
    }

    /**
     * @return string
     */
    public function getExportDirAttribute()
    {
        if ( !isset( $this->attributes[ 'export_dir' ] ) ) {
            return $this->attributes[ 'export_dir' ] = 'export/' . $this->config->get( 'app.id' ) . '/' . $this->getFileName() . '/' . date( 'Y/m' );
        }
        return $this->attributes[ 'export_dir' ] ?? '';
    }

    /**
     * @return string
     */
    public function getExportDirPathAttribute()
    {
        if ( !isset( $this->attributes[ 'export_dir_path' ] ) && $this->export_dir ) {
            return $this->attributes[ 'export_dir_path' ] = $this->config->get( 'app.root' ) . '/public/' . $this->export_dir;
        }
        return $this->attributes[ 'export_dir_path' ] ?? '';
    }

    /**
     * @return string
     */
    public function getExportFileUrlAttribute()
    {
        if ( !isset( $this->attributes[ 'export_file_url' ] ) && $this->export_dir && $this->export_file_name ) {
            return $this->attributes[ 'export_file_url' ] = $this->url->base() . '/' . $this->export_dir . '/' . $this->export_file_name;
        }
        return $this->attributes[ 'export_file_url' ] ?? '';
    }

    /**
     * @return string
     */
    public function getExportFilePathAttribute()
    {
        if ( !isset( $this->attributes[ 'export_file_path' ] ) && $this->export_dir_path && $this->export_file_name ) {
            return $this->attributes[ 'export_file_path' ] = $this->export_dir_path . '/' . $this->export_file_name;
        }
        return $this->attributes[ 'export_file_path' ] ?? '';
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return [
            'export_file_name'  => $this->export_file_name,
            'export_dir'        => $this->export_dir,
            'export_dir_path'   => $this->export_dir_path,
            'export_file_url'   => $this->export_file_url,
            'export_file_path'  => $this->export_file_path,
        ];
    }

    public function loopBefore()
    {

    }

    public function loopAfter()
    {

    }

    public function debugAfter()
    {

    }

    public $loopIndex = 1;
    public $loopItem = 1;
    public $records = [];

    public function loopRecord( $item )
    {
    }

    public $error = [];

    public function getDataExportKey()
    {
        return '';
    }

    public function getDataExportModel()
    {
        return null;
    }

    /**
     * @return null
     */
    public function updateDataExport()
    {
        if ( !$this->getDataExportKey() ) {
            return null;
        }

        if ( !$this->getDataExportModel() ) {
            return null;
        }

        $dx             = $this->getDataExportModel();
        $dx->key        = $this->getDataExportKey();
        $dx->path       = $this->export_dir_path;
        $dx->url        = $this->export_file_url;
        $dx->save();

        return $dx;
    }

    public function run()
    {
        if ( !$this->getHeaders() ) {
            $this->echo( 'Error: Headers are not specified!', Color::RED_NAME );
            die();
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray( $this->getRecords() );

        if ( !file_exists( $this->export_dir_path ) ) {
            mkdir( $this->export_dir_path, 0755, true );
        }

        if ( $this->getExportAsCsv() ) {
            $writer = new Csv( $spreadsheet );
        } else {
            $writer = new Xlsx( $spreadsheet );
        }

        try {
            $writer->save( $this->export_file_path );
        } catch ( Exception $e ) {
            $this->error[] = $e->getMessage();
        } catch ( \Error $e ) {
            $this->error[] = $e->getMessage();
        }

        if ( $dx = $this->updateDataExport() ) {
            print_r( $dx->toArray() );
        }

        $this->afterExport();
    }

    public function afterExport()
    {

    }
}