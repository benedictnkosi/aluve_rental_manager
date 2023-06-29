<?php

namespace App\Controller;

use App\Service\PropertyApi;
use App\Service\UnitApi;
use JMS\Serializer\SerializerBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UnitController extends AbstractController
{

    /**
     * @Route("api/units/get/{propertyId}")
     */
    public function getUnits($propertyId, UnitApi $unitApi, Request $request, LoggerInterface $logger): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $response = $unitApi->getUnits($propertyId);
        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($response, 'json');
        return new JsonResponse($jsonContent , 200, array(), true);
    }

    /**
     * @Route("api/units/update")
     */
    public function updateUnit(Request $request,  LoggerInterface $logger, UnitApi $unitApi): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('put')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $response = $unitApi->updateUnit($request->get('field'), $request->get('value'), $request->get('id'));
        return new JsonResponse($response , 200, array());
    }

    /**
     * @Route("api/units/create")
     */
    public function createUnit(Request $request, LoggerInterface $logger, UnitApi $unitApi): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('POST')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $propertyId = 0;
        if($request->get('id') !== null){
            $propertyId = $request->get('id');
        }

        $response = $unitApi->createUnit($request->get('name'), $propertyId);
        return new JsonResponse($response , 200, array());
    }


}