<?php
    declare(strict_types=1);
    
    namespace pctlib\filescanner\modules;

    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    
    require_once(__DIR__ . "/../FileScanningModuleInterface.php");

    require_once("vendor/autoload.php");

    use pctlib\cliarguments\Argument;
    use pctlib\cliarguments\ArgumentableInterface;

    use pctlib\filescanner\FileScanningModuleInterface;

    class WordpressFileScannerModule implements FileScanningModuleInterface, ArgumentableInterface {
        protected array $parameters = array();

        public function GetCLIArguments() : array {
            return [
                "scanPath" => new Argument("scanPath", "The directory to scan.", "", Argument::REQUIRED + Argument::EXISTING_PATH)                
            ];
        }   
    	/**
         * @param array $parameters
         * @return bool
         */
        public function SetParameters(array $parameters): bool {
            $this->parameters = $parameters;

            return true;
        }
    }
?>