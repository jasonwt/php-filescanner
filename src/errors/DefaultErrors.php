<?php
    declare(strict_types=1);
    
    namespace pctlib\filescanner\errors;

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    require_once(__DIR__ . "/../FileScannerErrorsInterface.php");
    require_once(__DIR__ . "/../debugging/Debugging.php");
    
    use pctlib\filescanner\FileScannerErrorsInterface;
    use pctlib\filescanner\debugging\Debugging;

    class DefaultErrors implements FileScannerErrorsInterface {
        private array $errors = [];     

        public function GetError(?int $index = null) : ?string {
            if (count($this->errors) == 0)
                return null;

            $errorRecord = [];

            if (is_null($index)) {
                $errorRecord = array_shift($this->errors);
            } else {
                if ($index < 0 || $index >= count($this->errors)) {
                    $this->AddError(self::ERROR_TYPE_WARNING + self::ERROR_TYPE_OUT_OF_BOUNDS, "index '$index' is out of bounds.");
                    return null;
                }

                $errorRecord = $this->errors[$index];                
            }

            return $errorRecord["error"];
        }
        public function GetErrors(int $types = 0) : ?array {
            if ($types < 0 || $types >= (self::ERROR_TYPES[array_keys(self::ERROR_TYPES)[count(self::ERROR_TYPES)-1]] * 2)) {
                $this->AddError(
                    self::ERROR_TYPE_WARNING + self::ERROR_TYPE_OUT_OF_BOUNDS,
                    "types '$types' is out of bounds.",
                    true,
                    true
                );

                return null;
            }

            $returnValue = [];

            for ($cnt = 0; $cnt < count($this->errors); $cnt ++) {
                foreach (self::ERROR_TYPES as $errorTypeName => $errorTypeValue) {
                    if ($types == 0 || $this->errors[$cnt]["types"] & $errorTypeValue) {
                        $returnValue[] = $this->GetError($cnt);
                        break;
                    }                        
                }
            }

            return $returnValue;            
        }

        public function GetErrorCount(int $types = 0) : ?int {
            if (is_null($getErrors = $this->GetErrors()))
                return null;

            return count($getErrors);
        }

        public function AddError(int $types, string $error, bool $includeTypesDescription = true, bool $includeStackTrace = true) : ?bool {
            if ($types < 0 || $types >= (self::ERROR_TYPES[array_keys(self::ERROR_TYPES)[count(self::ERROR_TYPES)-1]] * 2)) {
                $this->AddError(
                    self::ERROR_TYPE_WARNING + self::ERROR_TYPE_OUT_OF_BOUNDS,
                    "types '$types' is out of bounds.\n\nOriginal AddError\ntypes: $types\n$error",
                    true,
                    true
                );

                return null;
            } else {
                $newError = $error;

                if ($includeTypesDescription) {
                    $errorTypesArray = [];

                    foreach (self::ERROR_TYPES as $errorTypeName => $errorTypeValue) {
                        if ($types == 0 || $types & $errorTypeValue)
                            $errorTypesArray[] = $errorTypeName;
                    }
                    
                    $newError = "[" . implode("|", $errorTypesArray) . "] " . $newError;
                }

                if ($includeStackTrace) {
                    $dstring = Debugging::DString(2,0,[$newError]);
                } else {
                    $dstring = Debugging::DString(2,1,[$newError]);
                }

                $this->errors[] = [
                    "types" => $types,
                    "error" => $dstring
                ];
            }

            if ($types & self::ERROR_TYPE_FATAL) {
                echo "\n";

                foreach ($this->GetErrors() as $error)
                    echo $error . "\n";

                die();
            }

            return true;
        }

        public function ClearError(int $index) : ?bool {
            if ($index < 0 || $index >= count($this->errors)) {
                $this->AddError(self::ERROR_TYPE_WARNING + self::ERROR_TYPE_OUT_OF_BOUNDS, "index '$index' is out of bounds.");
                return null;
            }

            unset($this->errors[$index]);
            
            return true;
        }

        public function ClearErrors(int $types = 0) : ?bool {
            if ($types < 0 || $types >= (self::ERROR_TYPES[array_keys(self::ERROR_TYPES)[count(self::ERROR_TYPES)-1]] * 2)) {
                $this->AddError(
                    self::ERROR_TYPE_WARNING + self::ERROR_TYPE_OUT_OF_BOUNDS,
                    "types '$types' is out of bounds.",
                    true,
                    true
                );
                return null;
            }

            $newErrorsArray = [];

            for ($cnt = 0; $cnt < count($this->errors); $cnt ++) {
                foreach (self::ERROR_TYPES as $errorTypeName => $errorTypeValue) {
                    if ($types == 0 || $this->errors[$cnt]["types"] & $errorTypeValue)
                        continue;

                    $newErrorsArray[] = $this->errors[$cnt];
                }
            }

            $this->errors = $newErrorsArray;

            return true;
        }
    }
    

?>