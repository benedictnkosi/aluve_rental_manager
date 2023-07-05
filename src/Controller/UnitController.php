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
     * @Route("api/unit/get/{unitId}")
     */
    public function getUnit($unitId, UnitApi $unitApi, Request $request, LoggerInterface $logger): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $response = $unitApi->getUnit($unitId);
        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($response, 'json');
        return new JsonResponse($jsonContent , 200, array(), true);
    }



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

        $unitId = 0;
        if($request->get('id')){
            $unitId = $request->get('id');
        }
        $response = array();
        $errors = false;
        //check if is bulk create
        if(strcmp($request->get('bulkCreate'), "true")== 0 && !$request->get('id')){
            $numberOfUnitsToCreate = intval($request->get('numberOfUnits'));
            for ($x = 0; $x < $numberOfUnitsToCreate; $x++) {
                $roomName = $request->get('name') . " " . $x + 1;
                $response = $unitApi->createUnit($roomName, $unitId, $request->get('listed'), $request->get('parkingProvided'), $request->get('childrenAllowed'), $request->get('maxOccupants'), $request->get('minGrossSalary'), $request->get('rent'), $request->get('bedrooms'), $request->get('bathrooms'));
                if($response["result_code"] == 1){
                    $errors = true;
                }
            }
        }else{
            $response = $unitApi->createUnit($request->get('name'), $unitId, $request->get('listed'), $request->get('parkingProvided'), $request->get('childrenAllowed'), $request->get('maxOccupants'), $request->get('minGrossSalary'), $request->get('rent'), $request->get('bedrooms'), $request->get('bathrooms'));
        }

        if($errors){
            $response = array(
                'result_code' => 1,
                'result_message' => "Failed to create some of the units"
            );
        }
        return new JsonResponse($response , 200, array());
    }


}