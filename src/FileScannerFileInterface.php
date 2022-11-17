<?php
    declare(strict_types=1);
        
    namespace pctlib\filescanner;

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    interface FileScannerFileInterface {
        public function Path() : string;
        public function ScanStatus() : int;
        public function Size() : int;
        public function ChangeTime() : int;
        public function ModifyTime() : int;
        public function MD5() : string;
        public function OwnerId() : int;
        public function GroupId() : int;
        public function MimeType() : string;    
    }


?>