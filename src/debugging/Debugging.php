<?php
    declare(strict_types=1);
    
    namespace pctlib\filescanner\debugging;

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    class Debugging {
        public static function GetFunctionArgumentsString(array $args, int $maxArgumentDisplayLength = 0) : string {
            $newArgs = [];

            for ($acnt = 0; $acnt < count($args); $acnt ++) {
                $arg = $args[$acnt];

                if (is_null($arg))
                    $newArgs[$acnt] = "NULL";
                else if (is_bool($arg))
                    $newArgs[$acnt] = ($arg == true ? "TRUE" : "FALSE");
                else if (is_numeric($arg))
                    $newArgs[$acnt] = strval($arg);
                else if (is_string($arg))
                    $newArgs[$acnt] = '"' . strval($arg) . '"';
                else if (is_object($arg)) 
                    $newArgs[$acnt] = "OBJECT::" . get_class($arg);
                else if (is_array($arg))
                    $newArgs[$acnt] = "ARRAY[" . count($arg) . "]";
                else
                    $newArgs[$acnt] = gettype($arg);

                if ($maxArgumentDisplayLength > 0) {
                    if (strlen($newArgs[$acnt]) > $maxArgumentDisplayLength)
                        $newArgs[$acnt] = substr($newArgs[$acnt], 0, $maxArgumentDisplayLength - 5) . " ... ";                        
                }
            }

            return implode(", ", $newArgs);

        }
        public static function GetDebugBacktrace(int $startingIndex = 0, int $maxRecords = 0) : array {
            $debugBacktrace = debug_backtrace();

            if ($startingIndex > 0) {
                if (count($debugBacktrace) <= $startingIndex)
                    $startingIndex = count($debugBacktrace) - 1;

                for ($cnt = 0; $cnt < $startingIndex; $cnt ++)
                    array_shift($debugBacktrace);
            }

            if ($maxRecords > 0) {
                if (count($debugBacktrace) > $maxRecords)
                    $debugBacktrace = array_slice($debugBacktrace, 0, $maxRecords);                
            }

            return $debugBacktrace;
        }

        public static function DString(int $startingIndex, int $maxRecords, array $arguments) : string {
            $debugBacktrace = self::GetDebugBacktrace($startingIndex, $maxRecords);

            $returnValue = "";

            for ($dbcnt = 0; $dbcnt < count($debugBacktrace); $dbcnt ++) {
                $lineInfo = $debugBacktrace[$dbcnt]["file"] . "(" . $debugBacktrace[$dbcnt]["line"] . ") ";
                            
                if ($dbcnt == 0) {                
                    $returnValue .= $lineInfo . "\n";
    
                    foreach ($arguments as $arg)
                        $returnValue .= print_r($arg, true);

                    if (count($debugBacktrace) > 1)
                        $returnValue .= "\n\n*************** Stack Trace ***************";
                } else {
                    if (isset($debugBacktrace[$dbcnt]["class"])) {
                        $args = $debugBacktrace[$dbcnt]["args"];
    
                        $functionArgumentsDisplay = self::GetFunctionArgumentsString($args, 50);

                        $lineInfo .= $debugBacktrace[$dbcnt]["class"] . $debugBacktrace[$dbcnt]["type"] . $debugBacktrace[$dbcnt]["function"] . "(" . $functionArgumentsDisplay . ")";
    
                        $returnValue .= "\n#" . ($dbcnt-1) . " " . $lineInfo;                    
                    }
                }
            }
    
            return $returnValue;
        }
    }

    function dstring(...$arguments) : string {
        return Debugging::DString(1,1,func_get_args());
    }    

    function dprint() {
        echo Debugging::DString(1,1,func_get_args());
    }

    function dtstring() : string {
        return Debugging::DString(2,0,func_get_args());
    }

    function dtprint() {
        echo Debugging::DString(2,0,func_get_args());
    }


?>