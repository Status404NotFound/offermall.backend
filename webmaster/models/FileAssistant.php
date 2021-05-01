<?php


namespace webmaster\models;


class FileAssistant
{
    public $filePath = '';
    public $fileName = '';

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @return string
     */
    public function getFilePath(): string
    {
        return $this->filePath;
    }

    /**
     * @param string $fileName
     */
    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    /**
     * @param string $filePath
     */
    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function create(string $domain) : void
    {
        if (!file_exists("/var/www/$domain")) {
            mkdir("/var/www/$domain", 0777, true);
        }
    }
}