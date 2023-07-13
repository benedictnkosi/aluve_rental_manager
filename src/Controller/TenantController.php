<?php

namespace App\Controller;

use App\Service\DocumentApi;
use App\Service\FileUploaderApi;
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
     * @Route("public/tenant/get/{guid}")
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
     * @Route("public/tenant/get/{id}/{phone}")
     */
    public function getTenantById($id, $phone, TenantApi $tenantApi, Request $request, LoggerInterface $logger): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $response = $tenantApi->getTenantById($id, $phone);
        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($response, 'json');
        return new JsonResponse($jsonContent , 200, array(), true);
    }

    /**
     * @Route("public/tenant/lease_to_sign/{applicationGuid}")
     */
    public function getLeaseToSign($applicationGuid, TenantApi $tenantApi, Request $request, LoggerInterface $logger): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $response = $tenantApi->getLeaseToSign($applicationGuid);
        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($response, 'json');
        return new JsonResponse($jsonContent , 200, array(), true);
    }

    /**
     * @Route("public/tenant/getlease/{idNumber}/{phoneNumber}")
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
     * @Route("public/tenant/getleaseDocumentName/{idNumber}/{phoneNumber}")
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

    /**
     * @Route("public/tenant/upload/lease")
     * @throws \Exception
     */
    public function uploadLeaseDocument( Request $request, LoggerInterface $logger, FileUploaderApi $uploader, LeaseApi $leaseApi, TenantApi $tenantApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('post')) {
            return new JsonResponse("Internal server errors" , 500, array());
        }

        $file = $request->files->get('file');
        if (empty($file))
        {
            $logger->info("No file specified");
            return new Response("No file specified",
                Response::HTTP_UNPROCESSABLE_ENTITY, ['content-type' => 'text/plain']);
        }

        $uploadDir = __DIR__ . '/../../files/application_documents/';
        $uploader->setDir($uploadDir);
        $uploader->setExtensions(array('pdf','jpg','png','bmp','jpeg' ));  //allowed extensions list//

        $uploader->setMaxSize(5);//set max file size to be allowed in MB//

        $response = $uploader->uploadFile();
        if($response["result_code"] == 1){
            //upload failed
            header("HTTP/1.1 500 Internal Server Error");
            return new Response($response["result_message"],
                Response::HTTP_NOT_ACCEPTABLE, ['content-type' => 'text/plain']);
        }

        //write to DB
        $response = $leaseApi->addLeaseDoc($request->get("guid"), $request->get("document_type"), $response["file_name"]);
        if($response["result_code"] == 1){
            return new JsonResponse($response, 200, array());
        }else{
            return new JsonResponse($response, 201, array());

        }

    }
}