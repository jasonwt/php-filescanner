<?php
    declare(strict_types=1);
        
    namespace pctlib\filescanner\filescannerfile;

    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    require_once(__DIR__ . "/../FileScannerFileInterface.php");

    use pctlib\filescanner\FileScannerFileInterface;

    class DefaultFile implements FileScannerFileInterface {
        protected string $path = "";
        protected int $scanStatus = 0;
        protected int $size = 0;
        protected int $changeTime = 0;
        protected int $modifyTime = 0;
        protected string $md5 = "";
        protected int $ownerId = 0;
        protected int $groupId = 0;
        protected string $mimeType = "";

        public function __construct(string $filePath) {
            if (!is_file($filePath))
                throw new \Exception("'$filePath' is not a file or does not exist.");

            $this->path       = $filePath;
            $this->scanStatus = 0;
            $this->size       = filesize($filePath);
            $this->changeTime = filectime($filePath);
            $this->modifyTime = filemtime($filePath);
            $this->md5        = md5_file($filePath);
            $this->ownerId    = fileowner($filePath);
            $this->groupId    = filegroup($filePath);
            $this->mimeType   = mime_content_type($filePath);
        }

        public function Path() : string {
            return $this->path;
        }
        public function ScanStatus() : int {
            return $this->scanStatus;
        }
        public function Size() : int {
            return $this->size;
        }
        public function ChangeTime() : int {
            return $this->changeTime;
        }
        public function ModifyTime() : int {
            return $this->modifyTime;
        }
        public function MD5() : string {
            return $this->md5;
        }
        public function OwnerId() : int {
            return $this->ownerId;
        }
        public function GroupId() : int {
            return $this->groupId;
        }

        public function MimeType() : string {
            return $this->mimeType;
        }
    }
    


?>