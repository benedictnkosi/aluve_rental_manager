<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileUploaderApi
{
    private $logger;
    private $em;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->em = $entityManager;
    }

    private $destinationPath;
    private $errorMessage;
    private $extensions;
    private $allowAll;
    private $maxSize;
    private $uploadName;
    private $imageSeq = "room";
    private $thumbImageSeq = "thumb";

    function setDir($path)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $this->destinationPath = $path;
        $this->allowAll = false;
    }

    function setMaxSize($sizeMB)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $this->maxSize = $sizeMB * (1024 * 1024);
    }

    function setExtensions($options)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $this->extensions = $options;
    }

    function getExtension($string)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {
            $parts = explode(".", $string);
            $ext = strtolower($parts[count($parts) - 1]);
        } catch (Exception $c) {
            $ext = "";
        }
        return $ext;
    }

    function setMessage($message)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $this->errorMessage = $message;
        $this->logger->debug($message);
    }

    function getMessage()
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        return $this->errorMessage;
    }

    function getUploadName()
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        return $this->uploadName;
    }

    function getRandom()
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        return strtotime(date('Y-m-d H:i:s')) . rand(1111, 9999) . rand(11, 99) . rand(111, 999);
    }

    function uploadFile(): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {
            $newFileName = "";
            /* Location */
            $filename = $_FILES['file']['name'];
            $location = $this->destinationPath . $filename;
            $this->logger->debug("file name is: " . $filename);
            /* Extension */
            $extension = pathinfo($location, PATHINFO_EXTENSION);
            $extension = strtolower($extension);

            $size = $_FILES['file']["size"];
            $this->logger->info("file size is: " . $size);
            $this->logger->info("max size is : " . $this->maxSize);
            if ($size > $this->maxSize) {
                return array(
                    'result_message' => "File exceeds the size limit",
                    'result_code' => 1
                );
            }
            /* Check file extension */
            if (in_array(strtolower($extension), $this->extensions)) {
                /* Upload file */
                $newFileName = $this->getRandom() . "." . $extension;
                if (!move_uploaded_file($_FILES['file']['tmp_name'], $this->destinationPath . $newFileName)) {
                    return array(
                        'result_message' => "Failed to upload file, please try again",
                        'result_code' => 1
                    );
                }
            } else {
                return array(
                    'result_message' => "File format not allowed",
                    'result_code' => 1
                );
            }
        } catch (Exception $ex) {
            $this->logger->error("failed: " . $ex->getMessage());
            return array(
                'result_message' => "Failed to upload file",
                'result_code' => 1
            );
        }

        return array(
            'result_message' => "Successfully uploaded file",
            'result_code' => 0,
            'file_name' => $newFileName
        );
    }


    function uploadImage(): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {
            $newFileName = "";
            /* Location */
            $filename = $_FILES['file']['name'];
            $location = $this->destinationPath . $filename;
            $this->logger->debug("file name is: " . $filename);
            /* Extension */
            $extension = pathinfo($location, PATHINFO_EXTENSION);
            $extension = strtolower($extension);

            $size = $_FILES['file']["size"];
            $this->logger->info("file size is: " . $size);
            $this->logger->info("max size is : " . $this->maxSize);
            if ($size > $this->maxSize) {
                return array(
                    'result_message' => "File exceeds the size limit",
                    'result_code' => 1
                );
            }
            $newFileName = $this->getRandom() . "." . $extension;
            $target_filename = $this->destinationPath . $newFileName;
            /* Check file extension */
            if (in_array(strtolower($extension), $this->extensions)) {
                $maxDim = 800;
                $file_name = $_FILES['file']['tmp_name'];


                list($width, $height, $type, $attr) = getimagesize($file_name);
                $this->logger->info("file size is : " . $size);
                //only convert if size is greater than 1mb
                if (($width > $maxDim || $height > $maxDim) && $size > 1000000) {
                    //  $target_filename = $filename;
                    $ratio = $width / $height;
                    if ($ratio > 1) {
                        $new_width = $maxDim;
                        $new_height = $maxDim / $ratio;
                    } else {
                        $new_width = $maxDim * $ratio;
                        $new_height = $maxDim;
                    }
                    $src = imagecreatefromstring(file_get_contents($file_name));
                    $dst = imagecreatetruecolor($new_width, $new_height);
                    imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                    imagedestroy($src);
                    imagepng($dst, $target_filename); // adjust format as needed
                    imagedestroy($dst);
                } else {
                    /* Upload file */
                    if (!move_uploaded_file($_FILES['file']['tmp_name'], $target_filename)) {
                        return array(
                            'result_message' => "Failed to upload file, please try again",
                            'result_code' => 1
                        );
                    }
                }

            } else {
                return array(
                    'result_message' => "File format not allowed",
                    'result_code' => 1
                );
            }
        } catch
        (Exception $ex) {
            $this->logger->error("failed: " . $ex->getMessage());
            return array(
                'result_message' => "Failed to upload file",
                'result_code' => 1
            );
        }

        return array(
            'result_message' => "Successfully uploaded file",
            'result_code' => 0,
            'file_name' => $newFileName
        );
    }
}