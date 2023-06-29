<?php

namespace App\Controller;

use App\Service\LeaseApi;
use App\Service\PropertyApi;
use App\Service\UnitApi;
use JMS\Serializer\SerializerBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LeaseController extends AbstractController
{

    /**
     * @Route("api/leases/get/{propertyId}")
     */
    public function getLeases($propertyId, LeaseApi $leaseApi, Request $request, LoggerInterface $logger): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $response = $leaseApi->getLeases($propertyId);
        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($response, 'json');
        return new JsonResponse($jsonContent , 200, array(), true);
    }


    /**
     * @Route("public/lease/{guid}")
     */
    public function getLease($guid, LeaseApi $leaseApi, Request $request, LoggerInterface $logger): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $response = $leaseApi->getLease($guid);
        return new JsonResponse($response , 200, array());
    }

    /**
     * @Route("api/lease/update")
     */
    public function updateLease(Request $request,  LoggerInterface $logger, LeaseApi $leaseApi): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('put')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $response = $leaseApi->updateLease($request->get('field'), $request->get('value'), $request->get('id'));
        return new JsonResponse($response , 200, array());
    }

    /**
     * @Route("api/lease/raiselatefee")
     */
    public function raiseLateFee(Request $request,  LoggerInterface $logger, LeaseApi $leaseApi): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $response = $leaseApi->raiseLateFees();
        return new JsonResponse($response , 200, array());
    }


    /**
     * @Route("api/lease/create")
     */
    public function createLease(Request $request, LoggerInterface $logger, LeaseApi $leaseApi): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('POST')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $response = $leaseApi->createLease($request->get('tenantName'), $request->get('phone'), $request->get('email'), $request->get('unitId'), $request->get('start_date'), $request->get('end_date'), $request->get('deposit'), $request->get('lease_id'));
        return new JsonResponse($response , 200, array());
    }


}