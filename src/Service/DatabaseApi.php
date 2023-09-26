<?php

namespace App\Service;


use mysqli;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DatabaseApi extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;

        if (session_id() === '') {
            $logger->info("Session id is empty" . __METHOD__);
            session_start();
        }
    }

    public function queryDatabase($sql)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);

        $conn = new mysqli ("localhost", "root", "4IHPB33N35WC77KU$", "hoteloav_property_manager");
        // Check connection
        if ($conn->connect_error) {
            $this->logger->debug("failed to connect to the database");
            die ("Connection failed: " . $conn->connect_error);
        }
        $result = $conn->query($sql);
        $conn->close();
        if (!empty($result) && $result->num_rows > 0) {
            $this->logger->debug("results found");
            return $result;
        } else {
            $this->logger->debug("No results found");
            return false;
        }
    }


}