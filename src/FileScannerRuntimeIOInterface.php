<?php
    declare(strict_types=1);
        
    namespace pctlib\filescanner;

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    interface FileScannerRuntimeIOInterface {
        public function SaveRuntime(array $runtime);
        public function LoadRuntime();
    }



?>