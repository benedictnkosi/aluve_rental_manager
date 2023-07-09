<?php

namespace App\Controller;

use App\Service\DocumentApi;
use App\Service\LeaseApi;
use App\Service\PropertyApi;
use App\Service\TenantApi;
use App\Service\UnitApi;
use JMS\Serializer\SerializerBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TenantController extends AbstractController
{

    /**
     * @Route("api/tenant/get/{guid}")
     */
    public function getTenant($guid, TenantApi $tenantApi, Request $request, LoggerInterface $logger): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $response = $tenantApi->getTenant($guid);
        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($response, 'json');
        return new JsonResponse($jsonContent , 200, array(), true);
    }

    /**
     * @Route("api/tenant/getlease/{idNumber}/{phoneNumber}")
     */
    public function getLeaseByTenantId($idNumber,$phoneNumber, LeaseApi $leaseApi, Request $request, LoggerInterface $logger): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $response = $leaseApi->getLeaseByIdNumber($idNumber, $phoneNumber);
        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($response, 'json');
        return new JsonResponse($jsonContent , 200, array(), true);
    }

    /**
     * @Route("api/tenant/getleaseDocumentName/{idNumber}/{phoneNumber}")
     */
    public function getLeaseDocumentNameByIdNumber($idNumber, $phoneNumber, DocumentApi $documentApi, Request $request, LoggerInterface $logger): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $response = $documentApi->getDocumentNameByIdNumber($idNumber, $phoneNumber,"Lease");
        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($response, 'json');
        return new JsonResponse($jsonContent , 200, array(), true);
    }

}