<?php
    declare(strict_types=1);
    
    namespace pctlib\filescanner;

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    interface FileScannerErrorsInterface {
        const ERROR_TYPES = [
            "ERROR_TYPE_WARNING" => 1,
            "ERROR_TYPE_FATAL" => 2,
            "ERROR_TYPE_RUNTIME" => 4,
            "ERROR_TYPE_OUT_OF_BOUNDS" => 8,
            "ERROR_TYPE_LOGIC" => 16,
            "ERROR_TYPE_INVALID_ARGUMENT" => 32,
            "ERROR_TYPE_ALREADY_EXISTS" => 64,
            "ERROR_TYPE_DOES_NOT_EXIST" => 128,
            "ERROR_TYPE_FAILURE"        => 256
        ]; 
        
        const ERROR_TYPE_WARNING          = 1;
        const ERROR_TYPE_FATAL            = 2;
        const ERROR_TYPE_RUNTIME          = 4;
        const ERROR_TYPE_OUT_OF_BOUNDS    = 8;
        const ERROR_TYPE_LOGIC            = 16;
        const ERROR_TYPE_INVALID_ARGUMENT = 32;
        const ERROR_TYPE_ALREADY_EXISTS   = 64;
        const ERROR_TYPE_DOES_NOT_EXIST   = 128;
        const ERROR_TYPE_FAILURE          = 128;

        public function GetError(?int $index = null) : ?string;
        public function GetErrors(int $types = 0) : ?array;
        public function GetErrorCount(int $types = 0) : ?int;
        public function AddError(int $types, string $error, bool $includeTypesDescription = true, bool $includeStackTrace = true) : ?bool;
        public function ClearError(int $index) : ?bool;
        public function ClearErrors(int $types = 0) : ?bool;

        
    }
?>