<?php
    declare(strict_types=1);
        
    namespace pctlib\filescanner\traits;

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    require_once(__DIR__ . "/FileScannerInterface.php");

    require_once("vendor/autoload.php");

    trait ArraysTrait {

        protected function DONTWORK_ArrayRef(&$returnRef, array &$arr) : bool {
            $args = func_get_args();
            
            array_shift($args);
            array_shift($args);

            $ptr = &$arr;
    
            foreach ($args as $arg) {
                if (!isset($ptr[$arg]))
                    return false;
                    
                $ptr = &$ptr[$arg];
            }
            
            $returnRef = $ptr;

            return true;
        }

        protected function DONTWORK_ArrayVal(&$returnValue, array &$arr) : bool {
            $ref = null;

            if ($this->ArrayRef($ref, $arr)) {
                $tmp = $ref;
                $returnValue = $tmp;

                return true;
            }

            return false;

        }


        
        protected function &ArrayReference(array &$arr) {
            
            $args = func_get_args();

            
            
            array_shift($args);
            

            $ptr = &$arr;
    
            foreach ($args as $arg) {
                if (!array_key_exists($arg, $ptr)) {
                    echo "args: " . print_r($args, true) . "\n";
                    echo "arg: " . print_r($arg, true) . "\n";
                    echo "ptr: " . print_r($ptr, true) . "\n";
                    
                    throw new \Exception("GetArrayReference(" . implode(", ", $args) . ") failed");
                }

                $ptr = &$ptr[$arg];
            }
    
            return $ptr;
        }

        protected function ArrayValue(array &$arr) {
            $args = func_get_args();
            array_shift($args);
            
            
            return $this->ArrayReference($arr, ...$args);
            //return call_user_func_array([$this, 'ArrayReference'], $args);
        }

        protected function ArrayKeys(array &$arr) : ?array {
            $args = func_get_args();

            array_shift($args);

            $ptr = &$arr;

            foreach ($args as $arg) {
                if (!is_array($ptr))
                    return null;

                if (count($ptr) == 0)
                    return null;

                if (!array_key_exists($arg, $ptr))
                    return null;

                $ptr = &$ptr[$arg];
            }

            if (!is_array($ptr))
                return null;

            return array_keys($ptr);
        }

        protected function ArrayKeyExists(array &$arr, $arrKey) : ?bool{  
            $args = func_get_args();
            $args[0] = &$arr;        

            if (count($args) == 2)
                return array_key_exists($arrKey, $arr);

            $findKey = strval(array_pop($args));

            //if (is_null($arrKeys = call_user_func_array([$this, 'ArrayKeys'], $args)))
            if (is_null($arrKeys = $this->ArrayKeys(...$args)))
                return null;

            foreach ($arrKeys as $k) {
                if (strval($k) == $findKey)
                    return true;
            }

            return false;            
        }

        protected function ArrayKeyN(int $keyIndex, array &$arr) {
            $args = func_get_args();

            array_shift($args);
            $args[0] = &$arr;
            //array_shift($args);

            if (is_null($arrKeys = $this->ArrayKeys(...$args)))
                return null;

            if ($keyIndex >= count($arrKeys))
                return null;

            if (abs($keyIndex) > count($arrKeys))
                return null;

            //if (abs($keyIndex) >= count($arrKeys))
              //  return null;

            if ($keyIndex < 0)
                $keyIndex += count($arrKeys);

            return $arrKeys[$keyIndex];
        }

        protected function ArrayKeyLast(array &$arr) {
            $args = array_merge([-1], func_get_args());
            
            $args[1] = &$arr;

            return $this->ArrayKeyN(...$args);
            //return call_user_func_array([$this, 'ArrayKeyN'], $args);
        }

        protected function ArrayKeyFirst(array &$arr) {
            $args = array_merge([0], func_get_args());
            $args[1] = &$arr;

            return $this->ArrayKeyN(...$args);
            //return call_user_func_array([$this, 'ArrayKeyN'], $args);
        }

        
    }    
?>