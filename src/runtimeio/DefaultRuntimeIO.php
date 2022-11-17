<?php
    declare(strict_types=1);
        
    namespace pctlib\filescanner\runtimeio;

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    require_once(__DIR__ . "/../FileScannerRuntimeIOInterface.php");

    require_once("vendor/autoload.php");

    use pctlib\filescanner\FileScannerRuntimeIOInterface;
    use pctlib\cliarguments\ArgumentableInterface;
    use pctlib\cliarguments\Argument;

    class DefaultRuntimeIO implements FileScannerRuntimeIOInterface, ArgumentableInterface {
        protected array $parameters = [];
        public function SaveRuntime(array $runtime) {
            $scanPath    = $this->parameters["scanPath"];
            $runtimePath = $this->parameters["runtimePath"];
            $runtimeFile = md5($runtimePath) . ".runtime";

            $fileContents = $scanPath . "\n";

            $runtimeCompression = &$this->parameters["runtimeCompression"]            ;

            if ($runtimeCompression == "bzip") {
                $fileContents .= bzcompress(serialize($runtime));
            } else if ($runtimeCompression == "gzip") {
                $fileContents .= gzcompress(serialize($runtime));
            } else {
                $fileContents .= serialize($runtime);
            }

            if (!file_put_contents($runtimePath . $runtimeFile, $fileContents))
                return "file_put_contents($runtimePath$runtimeFile) failed.";

            return true;
        }

        public function LoadRuntime() {
            $scanPath    = $this->parameters["scanPath"];
            $runtimePath = $this->parameters["runtimePath"];
            $runtimeFile = md5($runtimePath) . ".runtime";

            if (file_exists($runtimePath . $runtimeFile)) {
                if (($fileContents = file_get_contents($runtimePath . $runtimeFile)) === false)
                    return "file_get_contents($runtimePath$runtimeFile) failed.";

                list ($fileScanPath, $data) = explode("\n", $fileContents, 2);

                if ($fileScanPath != $scanPath)
                    return "$runtimePath$runtimeFile first line does not match $scanPath.";
                
                if (bin2hex(substr($data, 0, 2)) == "789c") {
                    return unserialize(gzuncompress($data));
                } else if (substr($data, 0, 2) == "BZ") {
                    return unserialize(bzdecompress($data));
                } else {
                    return unserialize($data);
                }                
            }

            return true;
        }

    	/**
         * @return array
         */
        
        public function GetCLIArguments(): array {
            return [
                "scanPath"           => new Argument("scanPath", "The directory to scan.", "", Argument::REQUIRED + Argument::EXISTING_PATH),                
                "runtimePath"        => new Argument("runtimePath", "The directory to write the runtime files.", "cache/", Argument::REQUIRED + Argument::EXISTING_PATH),
                "runtimeCompression" => new Argument("runtimeCompression", "Runtime file compression.  gzip, bzip or none.", "gzip", Argument::REQUIRED)
            ];
        }
        
        /**
         *
         * @param array $parameters
         * @return bool
         */

        public function SetParameters(array $parameters): bool {
            $this->parameters = $parameters;

            return true;
        }
    }
?>