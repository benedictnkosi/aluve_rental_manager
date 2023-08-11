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
        try{
            $twilio = new Client($sid, $token);

            $message = $twilio->messages
                ->create("whatsapp:" . $number, // to
                    array(
                        "from" => "whatsapp:+14155238886",
                        "body" =>$messageString
                    )
                );

            $this->logger->info("Successfully sent message. " . $message->sid);

            return array(
                'result_message' => "Successfully sent message",
                'result_code' => 0,
                'sid' => $message->sid
            );
        }catch (Exception $exception){
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
        try {

            $this->logger->info("Connection to mail worked");

            $message = "testing";

            try {
            imap_mail(
                "nkosi.benedict@gmail.com",
                "Alert: Manual Export of Records Required",
                wordwrap($message, 70),
                $this->createHeaders()
            );
                $this->logger->info("   ---> Admin notified via email!\n");
        }
        catch (Exception $e) {
            $this->logger->info($e->getMessage());
                throw new Exception("Error in notifyAdminForCompleteSet()");
            }

        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return new JsonResponse($exception->getMessage(), 200, array());
        }

        return $responseArray;
    }

    private function createHeaders() {
        return "MIME-Version: 1.0" . "\r\n" .
            "Content-type: text/html; charset=iso-8859-1" . "\r\n" .
            "From: " . "payments@hotelrunner.co.za" . "\r\n";
    }
}