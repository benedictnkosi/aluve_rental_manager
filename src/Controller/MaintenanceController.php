<?php

namespace App\Controller;

use App\Service\ExpenseApi;
use App\Service\LeaseApi;
use App\Service\MaintenanceApi;
use App\Service\PropertyApi;
use App\Service\TransactionApi;
use App\Service\UnitApi;
use JMS\Serializer\SerializerBuilder;
use PhpImap\Exceptions\ConnectionException;
use Psr\Log\LoggerInterface;
use SecIT\ImapBundle\Service\Imap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MaintenanceController extends AbstractController
{
    /**
     * @Route("public/maintenance/new")
     */
    public function createMaintenance(Request $request, LoggerInterface $logger, MaintenanceApi $maintenanceApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('POST')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $maintenanceApi->logMaintenanceCallByIDNumber($request->get("id_number"), $request->get("phone_number"), $request->get("summary"));
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("public/maintenance/get/{idNumber}/{phoneNumber}")
     */
    public function getMaintenanceCalls($idNumber, $phoneNumber, Request $request, LoggerInterface $logger,  MaintenanceApi $maintenanceApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $maintenanceApi->getMaintenanceCallsByIDNumber($idNumber, $phoneNumber);
        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($response, 'json');
        return new JsonResponse($jsonContent , 200, array(), true);
    }


}