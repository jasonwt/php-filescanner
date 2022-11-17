<?php
    declare(strict_types=1);
        
    namespace pctlib\filescanner;

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    interface FileScannerProgressBarInterface {
        public function Reset(int $maxIndex) : bool;
        public function SetIndex(int $currentIndex) : bool;
        public function ProgressBarOutput() : string;
    }


?>