<?php
    declare(strict_types=1);
        
    namespace pctlib\filescanner;

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    require_once(__DIR__ . "/FileScanningModuleInterface.php");

    require_once("vendor/autoload.php");

    interface FileScannerInterface {
        public function LoadFileScanningModule(FileScanningModuleInterface $module) : bool;

        public function GetCLIArguments() : array;

        public function Execute() : bool;
    }


?>