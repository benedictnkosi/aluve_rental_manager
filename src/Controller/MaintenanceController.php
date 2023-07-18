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
     * @Route("no_auth/maintenance/new")
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
     * @Route("api/maintenance/new")
     */
    public function createMaintenanceAdmin(Request $request, LoggerInterface $logger, MaintenanceApi $maintenanceApi, UnitApi $unitApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('POST')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $maintenanceApi->logMaintenance($request->get("summary"), $request->get("unit_guid"), $request->get("property_guid"));
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/maintenance/close")
     */
    public function closeMaintenance(Request $request, LoggerInterface $logger, MaintenanceApi $maintenanceApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('PUT')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $maintenanceApi->closeMaintenanceCall($request->get("unit_id"));
        return new JsonResponse($response, 200, array());
    }


    /**
     * @Route("no_auth/maintenance/get/{idNumber}/{phoneNumber}")
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

    /**
     * @Route("api/maintenance/get/{propertyGuid}")
     */
    public function getPropertyMaintenanceCalls($propertyGuid, Request $request, LoggerInterface $logger,  MaintenanceApi $maintenanceApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $maintenanceApi->getMaintenanceCalls($propertyGuid);
        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($response, 'json');
        return new JsonResponse($jsonContent , 200, array(), true);
    }


}