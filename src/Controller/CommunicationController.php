<?php

namespace App\Controller;

use App\Service\ApplicationsApi;
use App\Service\CommunicationApi;
use App\Service\FileUploaderApi;
use App\Service\LeaseApi;
use App\Service\PropertyApi;
use App\Service\TransactionApi;
use App\Service\UnitApi;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerBuilder;
use PhpImap\Exceptions\ConnectionException;
use Psr\Log\LoggerInterface;
use SecIT\ImapBundle\Service\Imap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twilio\Rest\Client;

class CommunicationController extends AbstractController
{

    /**
     * @Route("api/comms/send")
     */
    public function test(Request $request, LoggerInterface $logger, CommunicationApi $communicationApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('GET')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        //$response = $communicationApi->sendWhatsApp("+27837917430", "Your appointment is coming up on July 21 at 3PM bro");
        $response = $communicationApi->sendWhatsApp("+27837917430", "We are happy to let you know that your application for Unit 1 @ Cosmo City has been accepted.");
        return new JsonResponse($response, 200, array());
    }

}