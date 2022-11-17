<?php
    declare(strict_types=1);
        
    namespace pctlib\filescanner\progressbar;

    use pctlib\filescanner\FileScannerProgressBarInterface;

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    require_once(__DIR__ . "/../FileScannerProgressBarInterface.php");

    class DefaultProgressBar implements FileScannerProgressBarInterface {
        protected int $startTime = 0;

        protected $width = 15;
        protected $includeSpinner = true;
        protected $includeETA = true;

        protected $maxIndex = 0;
        protected int $currentIndex = 0;

        public function __construct(int $width, bool $includeSpinner = true, bool $includeETA = true) {
            $this->width = max(10, min(50, $width));
            $this->includeSpinner = $includeSpinner;
            $this->includeETA     = $includeETA;
        }
        /**
         * @return bool
         */
        public function Reset(int $maxIndex): bool {
            $this->maxIndex = $maxIndex;

            $this->startTime = time();

            return true;
        }

        public function SetIndex(int $currentIndex) : bool {
            $this->currentIndex = $currentIndex;

            return true;
        }
        /**
         *
         * @param int $currentIndex
         * @param int $maxIndex
         * @param int $width
         * @return string
         */
        public function ProgressBarOutput(): string {

            $eta = "0:00:00";

            $rotations = ['|','/','-','\\'];

            if ($this->maxIndex > 0) {
                $progress = floatval($this->currentIndex+1) / floatval($this->maxIndex);

                $eta = "";

                if ($this->includeETA) {
                    $etime = floatval(time() - $this->startTime);

                    $seconds = intval(round(($etime / $progress) - $etime));
                    $minutes = 0;
                    $hours = 0;

                    if ($seconds > 60) {
                        $minutes = intval($seconds / 60);
                        $seconds = $seconds - ($minutes * 60);                    
                    }

                    if ($minutes > 60) {
                        $hours = intval($minutes / 60);
                        $minutes = $minutes - ($hours * 60);                    
                    }

                    $eta = "|";
                    
                    //if ($hours)
                        $eta .= sprintf("%02d", $hours) . ":";

                    //if ($minutes)
                        $eta .= sprintf("%02d", $minutes) . ":";
                    
                    $eta .= sprintf("%02d", $seconds);
                    
                }
            }

            $width = $this->width - strlen($eta) - ($this->includeSpinner ? 1 : 0);

            $spinnerSymbol = "";

            if ($this->includeSpinner && $progress < 1.0)
                $spinnerSymbol = $rotations[$this->currentIndex % 4];

            return "[" . 
                str_repeat("#", intval($progress * $width)) .
                $spinnerSymbol . 
                str_repeat(".", max(0, ($width - intval($progress * $width)-strlen($spinnerSymbol)))) .
                $eta .
                "]";
        }
    }
    


?>