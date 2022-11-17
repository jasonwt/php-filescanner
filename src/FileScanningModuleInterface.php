<?php
    declare(strict_types=1);
        
    namespace pctlib\filescanner;

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    require_once("vendor/autoload.php");

    interface FileScanningModuleInterface {
        public function GetCLIArguments() : array;
    }


?>