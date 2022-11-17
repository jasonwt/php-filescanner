<?php
    declare(strict_types=1);
    
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    
    require_once(__DIR__ . "/../src/FileScannerInterface.php");
    require_once(__DIR__ . "/../src/filescanner/FileScanner.php");
    require_once(__DIR__ . "/../src/FileScanningModuleInterface.php");
    require_once(__DIR__ . "/../src/FileScannerFileInterface.php");

    require_once("vendor/autoload.php");

    use pctlib\cliarguments\Argument;
    use pctlib\cliarguments\Arguments;

    use pctlib\filescanner\filescanner\FileScanner;
    use pctlib\filescanner\FileScannerFileInterface;

    class TestScanner extends FileScanner {
        protected $modifiedFilesArray = [];
        protected $newFilesArray = [];
        protected $removedFilesArray = [];

        public function GetCLIArguments() : array {
            $cliArguments = parent::GetCLIArguments();

            $cliArguments["scanSet"]= new Argument("scanSet", "Which set to scan.  new, modified or all", "all", Argument::REQUIRED);

            return $cliArguments;
        }

        protected function ModifiedFileHook(FileScannerFileInterface $fileObject) {
            $this->modifiedFilesArray[$fileObject->Path()] = $fileObject;
        }

        protected function NewFileHook(FileScannerFileInterface $fileObject) {
            $this->newFilesArray[$fileObject->Path()] = $fileObject;
        }

        protected function RemovedFileHook(FileScannerFileInterface $fileObject) {
            $this->removedFilesArray[$fileObject->Path()] = $fileObject;
        }

        protected function InitCustomParameters(array $cliArguments) : bool {
            if (!parent::InitCustomParameters($cliArguments))
                return false;
                
            $cacheCompression = strtolower($this->parameters["cacheCompression"]);

            if ($cacheCompression != "none" && $cacheCompression != "bzip" && $cacheCompression != "gzip")
                die (Arguments::Usage($cliArguments, "Invalid cacheCompression '$cacheCompression'") . "\n");

            $scanSet = strtolower($this->parameters["scanSet"]);

            if ($scanSet != "all" && $scanSet != "modified" && $scanSet != "new")
                die (Arguments::Usage($cliArguments, "Invalid scanSet '$scanSet'") . "\n");

            return true;
        }

        protected function Init() : bool {
            
            if (!isset($this->runtime["newFiles"]))
                $this->runtime["newFiles"] = [];

            if (!isset($this->runtime["modifiedFiles"]))
                $this->runtime["modifiedFiles"] = [];

            if (!isset($this->runtime["removedFiles"]))
                $this->runtime["removedFiles"] = [];

            if (!parent::Init())
                return false;

            $thisTimestamp = $this->GetArrayKeyN($this->runtime["filesHistory"], -1);

            if (count($this->newFilesArray) > 0)
                $this->runtime["newFiles"][$thisTimestamp] = $this->newFilesArray;

            if (count($this->modifiedFilesArray) > 0)
                $this->runtime["modifiedFiles"][$thisTimestamp] = $this->modifiedFilesArray;

            return true;
        }

        protected function SkipFileScan(FileScannerFileInterface $fileObject) : bool {
            $thisTimestamp = $this->GetArrayKeyN($this->runtime["filesHistory"], -1);

            $scanSet = $this->parameters["scanSet"];

            if ($scanSet == "modified") {
                if (!isset($this->runtime["modifiedFiles"][$thisTimestamp][$fileObject->Path()]))
                    return true;
            } else if ($scanSet == "new") {
                if (!isset($this->runtime["newFiles"][$thisTimestamp][$fileObject->Path()]))
                    return true;
            }

            return parent::SkipFileScan($fileObject);
        }

        protected function ScanFile(FileScannerFileInterface $fileObject) {
            parent::ScanFile($fileObject);
        }
    }





    $test = new TestScanner();

    $test->Execute();
    
    //print_r($test);
?>