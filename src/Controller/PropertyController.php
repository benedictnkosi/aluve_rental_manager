<?php

namespace App\Controller;

use App\Service\PropertyApi;
use JMS\Serializer\SerializerBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PropertyController extends AbstractController
{

    /**
     * @Route("api/properties")
     */
    public function getProperties(PropertyApi $propertyApi, Request $request, LoggerInterface $logger): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $response = $propertyApi->getProperties();
        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($response, 'json');
        return new JsonResponse($jsonContent , 200, array(), true);
    }

    /**
     * @Route("api/properties/{id}")
     */
    public function getProperty($id, PropertyApi $propertyApi, Request $request, LoggerInterface $logger): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $response = $propertyApi->getProperty($id);
        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($response, 'json');
        return new JsonResponse($jsonContent , 200, array(), true);
    }


    /**
     * @Route("api/property/update")
     */
    public function updateProperty(Request $request,  LoggerInterface $logger, PropertyApi $propertyApi): Response{
        $logger->info("Starting Method fail: " . __METHOD__);
        if (!$request->isMethod('put')) {
            return new JsonResponse("Method Not Allowed here" , 405, array());
        }

        $response = $propertyApi->updateProperty($request->get('field'), $request->get('value'), $request->get('id'));
        return new JsonResponse($response , 200, array());
    }

    /**
     * @Route("api/properties/create")
     */
    public function createProperty(Request $request, LoggerInterface $logger, PropertyApi $propertyApi): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('post')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $propertyId = 0;
        if($request->get('id') !== null){
            $propertyId = $request->get('id');
        }

        $response = $propertyApi->createProperty($request->get('name'), $request->get('address'), $propertyId);
        return new JsonResponse($response , 200, array());
    }

}