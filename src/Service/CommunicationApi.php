<?php

namespace App\Service;

use App\Entity\Leases;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PhpImap\Exceptions\ConnectionException;
use Psr\Log\LoggerInterface;
use SecIT\ImapBundle\Service\Imap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Twilio\Rest\Client;

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
     * @throws ConnectionException
     */
    function sendEmail(): JsonResponse|array
    {
        $this->logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();


        $this->logger->info("Connection to mail worked");
        $Parameters = array(
            "client_name" => "Benedict",
            "client_surname" => "Nkosi",
        );

        $message = $this->generate_email_body("welcome", $Parameters);

        try {
            imap_mail(
                "payments@hotelrunner.co.za",
                "Alert: Manual Export of Records Required",
                wordwrap($message, 70),
                $this->createHeaders()
            );
            $responseArray[] = "   ---> Admin notified via email!\n";
            $this->logger->info("   ---> Admin notified via email!\n");
        } catch (Exception $e) {
            $this->logger->info($e->getMessage());
            $responseArray[] = $e->getMessage();
        }


        return $responseArray;
    }

    private function createHeaders()
    {
        return "MIME-Version: 1.0" . "\r\n" .
            "Content-type: text/html; charset=iso-8859-1" . "\r\n" .
            "From: " . "payments@hotelrunner.co.za" . "\r\n";
    }


//generate_email_body("password_reset", $Parameters);

    function generate_email_body($templateName, $Parameters){
        $templateString = $this->readTemplateFile($templateName);
        return $this->replaceParameters($templateString, $Parameters);
    }

    function replaceParameters($templateString, $Parameters){
        try{
            $bodytag = $templateString;

            foreach ($Parameters as $key => $value) {
                $bodytag = str_replace("<<" . $key . ">>", $value , $bodytag);
            }

            return $bodytag;
        }catch (Exception $e) {
            return $e->getMessage();
        }
    }


    function readTemplateFile($templateName){
        try{
            $myfile = fopen(__DIR__.'/../../templates/email/' . $templateName . ".html", "r") or die("Unable to open file!");
            $templateString =  fread($myfile,filesize(__DIR__.'/../../templates/email/' . $templateName . ".html"));
            fclose($myfile);
            return $templateString;
        }catch (Exception $e) {
            return $e->getMessage();
        }
    }
}