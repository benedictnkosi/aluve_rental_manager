<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PhpImap\Exceptions\ConnectionException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twilio\Rest\Client;
require_once(__DIR__ . '/../app/application.php');

class CommunicationApi extends AbstractController
{

    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;

        if (session_id() === '') {
            $logger->info("Session id is empty" . __METHOD__);
            session_start();
        }
    }

    public function sendWhatsApp($number, $messageString): array
    {
        $this->logger->info("Starting Method: " . __METHOD__);

        //validate the number
//        if(strlen($number) <> 10 || !str_starts_with($number, "0")){
//            return array(
//                'result_message' => "Error. Phone number is not valid",
//                'result_code' => 1,
//            );
//        }

        // $number = "+27" . substr($number, 1, strlen($number) -1 );

        $sid = "";
        $token = "";
        try {
            $twilio = new Client($sid, $token);

            $message = $twilio->messages
                ->create("whatsapp:" . $number, // to
                    array(
                        "from" => "whatsapp:+14155238886",
                        "body" => $messageString
                    )
                );

            $this->logger->info("Successfully sent message. " . $message->sid);

            return array(
                'result_message' => "Successfully sent message",
                'result_code' => 0,
                'sid' => $message->sid
            );
        } catch (Exception $exception) {
            return array(
                'result_message' => $exception->getMessage(),
                'result_code' => 1
            );
        }
    }

    /**
     */
    function sendEmail($toEmail, $recipientName, $subject, $message, $link, $linkText, $template): bool
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $this->logger->debug("Email Parameters: $toEmail");
        $this->logger->debug("Email Parameters: $recipientName");
        $this->logger->debug("Email Parameters: $subject");
        $this->logger->debug("Email Parameters: $message");
        $this->logger->debug("Email Parameters: $link");
        $this->logger->debug("Email Parameters: $linkText");
        $this->logger->debug("Email Parameters: $template");

        $Parameters = array(
            "recipient_name" => $recipientName,
            "message" => $message,
            "main_button_link" => $link,
            "main_button_link_text" => $linkText,
        );


        $message = $this->generate_email_body($template, $Parameters);

        try {
            imap_mail(
                $toEmail,
                $subject,
                wordwrap($message, 70),
                $this->createHeaders()
            );
            $this->logger->info("Email sent successfully to $toEmail");
            return true;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }

    private function createHeaders(): string
    {
        return "MIME-Version: 1.0" . "\r\n" .
            "Content-type: text/html; charset=iso-8859-1" . "\r\n" .
            "From: " . EMAIL_FROM . "\r\n";
    }

    function generate_email_body($templateName, $Parameters): array|bool|string
    {
        $templateString = $this->readTemplateFile($templateName);
        if($templateString !== false){
            return $this->replaceParameters($templateString, $Parameters);
        }else{
            return false;
        }

    }

    function replaceParameters($templateString, $Parameters): array|bool|string
    {
        try{
            $body = $templateString;

            foreach ($Parameters as $key => $value) {
                $body = str_replace("<<" . $key . ">>", $value , $body);
            }

            return $body;
        }catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }

    function readTemplateFile($templateName){
        try{
            $emailFile = fopen(__DIR__.'/../../templates/email/' . $templateName . ".html", "r") or die("Unable to open file!");
            $templateString =  fread($emailFile,filesize(__DIR__.'/../../templates/email/' . $templateName . ".html"));
            fclose($emailFile);
            return $templateString;
        }catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }
}