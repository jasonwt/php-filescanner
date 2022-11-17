<?php
    declare(strict_types=1);
        
    namespace pctlib\filescanner\filescanner;

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    require_once(__DIR__ . "/../FileScannerInterface.php");
    require_once(__DIR__ . "/../FileScannerProgressBarInterface.php");
    require_once(__DIR__ . "/../FileScannerFileInterface.php");    
    require_once(__DIR__ . "/../FileScannerRuntimeIOInterface.php");
    require_once(__DIR__ . "/../FileScannerErrorsInterface.php");

    require_once(__DIR__ . "/../errors/DefaultErrors.php");
    require_once(__DIR__ . "/../runtimeio/DefaultRuntimeIO.php");
    require_once(__DIR__ . "/../progressbar/DefaultProgressBar.php");
    require_once(__DIR__ . "/../filescannerfile/DefaultFile.php");

    require_once("vendor/autoload.php");

    use pctlib\cliarguments\Argument;
    use pctlib\cliarguments\Arguments;
    use pctlib\cliarguments\ArgumentableInterface;

    use pctlib\filescanner\FileScannerRuntimeIOInterface;
    use pctlib\filescanner\FileScannerInterface;
    use pctlib\filescanner\FileScannerErrorsInterface;
    use pctlib\filescanner\FileScannerProgressBarInterface;
    use pctlib\filescanner\FileScannerFileInterface;
    use pctlib\filescanner\FileScanningModuleInterface;

    use pctlib\filescanner\runtimeio\DefaultRuntimeIO;
    use pctlib\filescanner\filescannerfile\DefaultFile;
    use pctlib\filescanner\progressbar\DefaultProgressBar;    
    use pctlib\filescanner\errors\DefaultErrors;

    class FileScanner implements FileScannerInterface, ArgumentableInterface {
        protected FileScannerErrorsInterface $errorsInterface;
        protected FileScannerProgressBarInterface $progressBarInterface;
        protected FileScannerRuntimeIOInterface $runtimeIOInterface;

        protected bool $running     = true;
        protected array $parameters = [];
        protected array $modules    = [];

        protected array $runtime = [
            "filesHistory" => []
        ];

        public function __construct(?FileScannerErrorsInterface $errorsInterface = null, ?FileScannerProgressBarInterface $progressBarInterface = null, ?FileScannerRuntimeIOInterface $runtimeIOInterface = null) {
            if (is_null($runtimeIOInterface))
                $runtimeIOInterface = new DefaultRuntimeIO();

            $this->runtimeIOInterface = $runtimeIOInterface;

            if (is_null($progressBarInterface))
                $progressBarInterface = new DefaultProgressBar(50, true, true);

            $this->progressBarInterface = $progressBarInterface;

            if (is_null($errorsInterface))
                $errorsInterface = new DefaultErrors();

            $this->errorsInterface = $errorsInterface;
        }

        public function GetCLIArguments() : array {
            return [
                "loadModule"        => new Argument("loadModule", "Scanning module to load.", []),
                "logPath"           => new Argument("logPath", "The directory to write the mscan.log file.", "/var/log/", Argument::REQUIRED + Argument::EXISTING_PATH),                
                "scanPath"          => new Argument("scanPath", "The directory to scan.", "", Argument::REQUIRED + Argument::EXISTING_PATH),                
                "updateCacheFreq"   => new Argument("updateCacheFreq", "How often in seconds to update the find files and files info cache. -1 for no update. ", 0, Argument::REQUIRED + Argument::INT),
                "maxHistoryRecords" => new Argument("maxHistoryRecords", "The maximum number of files info Runtime history to keep.", 3, Argument::REQUIRED + Argument::INT),
                "includePath"       => new Argument("include", "Include file/folder wildcard path.  Include is processed before exclude.", []),
                "excludePath"       => new Argument("exclude", "Exclude file/folder wildcard path.", []),
                "cachePath"         => new Argument("cachePath", "The directory to write the runtime cache files.", "cache/", Argument::REQUIRED + Argument::EXISTING_PATH),
                "cacheCompression"  => new Argument("cacheCompression", "Cache file compression.  gzip, bzip or none.", "gzip", Argument::REQUIRED)
            ];
        }

        public function SetParameters(array $parameters) : bool {
            foreach ($parameters as $k => $v)
                $this->parameters[$k] = $v;            

            return true;
        }
        
        protected function ErrorsInterface() : FileScannerErrorsInterface {
            return $this->errorsInterface;
        }

        protected function ProgressBarInterface() : FileScannerProgressBarInterface {
            return $this->progressBarInterface;            
        }

        protected function RuntimeIOInterface() : FileScannerRuntimeIOInterface {
            return $this->runtimeIOInterface;            
        }

        protected function GetArrayKeyN(array $arr, int $index) : string {
            if ($index < 0)
                $index += count($arr);

            if ($index < 0)
                return "0";

            if ($index >= count($arr))
                return "0";

            return strval(array_keys($arr)[$index]);
        }   
//
        protected function ReadByte(string $prompt = "", $showInput = false) : string {
            readline_callback_handler_install($prompt, function() {});
            $char = stream_get_contents(STDIN, 1);
            readline_callback_handler_remove();

            if ($showInput)
                echo $char;

            return $char;
        }
//
        protected function ReadLine(string $prompt, bool $showInput = true, array $terminatingCharacters = array("\n")) : ?string {
            echo $prompt;

            $dataString = "";

            while (true) {
                $char = $this->ReadByte("", false);

                if (in_array($char, $terminatingCharacters)) {
                    break;                    
                } else if (ord($char) == 127) {
                    if (strlen($dataString) > 0) {
                        if ($showInput)
                            echo $char;

                        $dataString = substr($dataString, 0, strlen($dataString) - 1);
                    }
                } else if (ord($char) == 27) {

                    echo ord($char) . " ";
                } else {
                    if ($showInput)
                        echo $char;
                                                
                    $dataString .= $char;
                }
            }

            return $dataString;
        }
//
        protected function Output(string $message) {
            echo $message;
        }
//        
        protected function LoadRuntime(): bool {
            if (($loadRuntimeValue = $this->RuntimeIOInterface()->LoadRuntime()) === true)
                return true;

            if (is_string($loadRuntimeValue)) {
                die ($this->ErrorsInterface()->AddError($this->ErrorsInterface()::ERROR_TYPE_FATAL, $loadRuntimeValue));
            } else if (!is_array($loadRuntimeValue)) {
                die($this->ErrorsInterface()->AddError($this->ErrorsInterface()::ERROR_TYPE_FATAL, "LoadRuntime Failed.  unexpected data."));
            }

            foreach ($loadRuntimeValue as $k => $v)
                $this->runtime[$k] = $v;

            return true;
        }
//
        protected function SaveRuntime(): bool {
            if (($saveRuntimeResults = $this->RuntimeIOInterface()->SaveRuntime($this->runtime)) === true)
                return true;

            $this->ErrorsInterface()->AddError($this->ErrorsInterface()::ERROR_TYPE_WARNING + $this->ErrorsInterface()::ERROR_TYPE_FAILURE, $saveRuntimeResults);

            return false;            
        }
//
        protected function Shutdown() {
            $this->running = false;
        }
//
        protected function ModifiedFileHook(FileScannerFileInterface $fileObject) {}
//
        protected function NewFileHook(FileScannerFileInterface $fileObject) {}
//
        protected function RemovedFileHook(FileScannerFileInterface $fileObject) {}
//
        protected function NewFileObject(string $path) : ?FileScannerFileInterface {
            return new DefaultFile($path);
        }
//
        protected function GetFilesList(string $path) : ?string {
            echo $this->Output("Finding Files in '$path'\n");

            $returnValue = "";

            if (!$dh = opendir($path))
                $this->ErrorsInterface()->AddError($this->ErrorsInterface()::ERROR_TYPE_FATAL + $this->ErrorsInterface()::ERROR_TYPE_FAILURE, "GetFilesList($path) failed.");

            while (($file = readdir($dh)) !== false) {
                if ($file == "." || $file == "..")
                    continue;

                $fileType = filetype($path . $file);

                if ($fileType == "file") {
                    $returnValue .= $path . $file . "\n";
                } else if ($fileType == "dir") {
                    $returnValue .= $this->GetFilesList($path . $file . "/");                    
                }                 
            }
            
            closedir($dh);

            return $returnValue;
        }
//
        public function LoadFileScanningModule(FileScanningModuleInterface $module) : bool {
            throw new \Exception("Not implemented.");
        }
//      
        protected function UpdateFilesInfo() {
            $filesHistory = &$this->runtime["filesHistory"];

            $thisTimestamp = time();

            $filesList = trim($this->GetFilesList($this->parameters["scanPath"]));

            if ($filesList == "") {
                $this->ErrorsInterface()->AddError($this->ErrorsInterface()::ERROR_TYPE_WARNING + $this->ErrorsInterface()::ERROR_TYPE_RUNTIME, "No files found to scan.");
                $this->Shutdown();
                return false;
            }
                
            $thisFilesArray = [];

            $lastTimestamp = $this->GetArrayKeyN($this->runtime["filesHistory"], -1);

            $filesListArray = explode("\n", $filesList);

            $this->progressBarInterface->Reset(count($filesListArray));

            for ($flcnt = 0; $flcnt < count($filesListArray); $flcnt ++) {
                $this->ProgressBarInterface()->SetIndex($flcnt);

                $filePath = $filesListArray[$flcnt];

                $this->Output($this->ProgressBarInterface()->ProgressBarOutput() . " Loading File Info for '" . $filePath . "'\n");
       
                $thisFilesArray[$filePath] = $this->NewFileObject($filePath);

                if ($lastTimestamp > 0) {
                    if (isset($filesHistory[$lastTimestamp][$filePath])) {
                        if (serialize($thisFilesArray[$filePath]) != serialize($filesHistory[$lastTimestamp][$filePath]))
                            $this->ModifiedFileHook($thisFilesArray[$filePath]);                                
                    } else {
                        $this->NewFileHook($thisFilesArray[$filePath]);                            
                    }
                }                    
            }

            if (count($thisFilesArray) > 0)                    
                $filesHistory[$thisTimestamp] = $thisFilesArray;                

            if ($lastTimestamp > 0) {
                foreach ($filesHistory[$lastTimestamp] as $filePath => $fileInfo) {
                    if (!isset($thisFilesArray[$filePath]))
                        $this->RemovedFileHook($fileInfo);                            
                }                    
            }
        }
//
        protected function InitCustomParameters(array $cliArguments) : bool {
            $cacheCompression = strtolower($this->parameters["cacheCompression"]);

            if ($cacheCompression != "none" && $cacheCompression != "bzip" && $cacheCompression != "gzip")
                die (Arguments::Usage($cliArguments, "Invalid cacheCompression '$cacheCompression'") . "\n");

            $scanSet = strtolower($this->parameters["scanSet"]);

            if ($scanSet != "all" && $scanSet != "modified" && $scanSet != "new")
                die (Arguments::Usage($cliArguments, "Invalid scanSet '$scanSet'") . "\n");

            return true;
        }
//
        protected function Init() : bool {
            $cliArguments = [
                "FILESCANNER" => $this,
                "ERRORS_INTERFACE" => $this->errorsInterface,
                "PROGRESS_BAR_INTERFACE" => $this->progressBarInterface,
                "RUNTIME_IO_INTERFACE" => $this->runtimeIOInterface
            ];

            for ($acnt = 1; $acnt < count($_SERVER["argv"]); $acnt ++) {
                $arg = $_SERVER["argv"][$acnt];

                if (strtolower(substr($arg, 0, 13)) == "--loadmodule=") {
                    $moduleName = substr($arg, 13);

                    echo "loadModule($moduleName)\n";
                }
            }

            if (($processArgumentsResults = Arguments::Process($cliArguments)) !== true)
                die (Arguments::Usage($cliArguments, $processArgumentsResults) . "\n");

            if (!$this->InitCustomParameters($cliArguments))
                return false;

            if (!$this->LoadRuntime())
                return false;

            $filesHistory = &$this->runtime["filesHistory"];

            $lastScanTimestamp = $this->GetArrayKeyN($this->runtime["filesHistory"], -1);            

            if (time() > $lastScanTimestamp + $this->parameters["updateCacheFreq"])
                $this->UpdateFilesInfo();

            while (count($filesHistory) > $this->parameters["maxHistoryRecords"])
                array_shift($this->runtime["filesHistory"]);                
            
            return true;
        }
//
        protected function ScanFile(FileScannerFileInterface $fileObject) {
            
        }
//
        protected function SkipFileScan(FileScannerFileInterface $fileObject) : bool {
            return false;
        }
//
        protected function StartScan() : bool {
            if (($thisTimestamp = $this->GetArrayKeyN($this->runtime["filesHistory"], -1)) == "0")
                $this->ErrorsInterface()->AddError($this->ErrorsInterface()::ERROR_TYPE_DOES_NOT_EXIST + $this->ErrorsInterface()::ERROR_TYPE_FATAL, "Could not load current runtime timestamp.");

            $thisFilesHistory = &$this->runtime["filesHistory"][$thisTimestamp];

            while ($this->running) {
                $thisFileHistoryKeys = array_keys($thisFilesHistory);
                
                $this->ProgressBarInterface()->Reset(count($thisFileHistoryKeys));

                for ($fcnt = 0; $fcnt < count($thisFileHistoryKeys); $fcnt ++) {
                    $this->ProgressBarInterface()->SetIndex($fcnt);

                    $fileObject = $thisFilesHistory[$thisFileHistoryKeys[$fcnt]];

                    if ($this->SkipFileScan($fileObject))
                        continue;

                    $this->Output($this->ProgressBarInterface()->ProgressBarOutput() . " Scanning File '" . $fileObject->Path() . "'\n");

                    $this->ScanFile($fileObject);
                }

                $this->Shutdown();
            }

            return true;
        }        
//
        public function Execute() : bool {            
            if (!$this->Init()) {
                return false;
            } else {
                $this->StartScan();
            }

            $this->SaveRuntime();

//            print_r($this);

            return true;
        }
    }
?>