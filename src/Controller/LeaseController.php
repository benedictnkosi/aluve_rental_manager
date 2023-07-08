<?php

namespace App\Controller;

use App\Entity\Document;
use App\Service\ApplicationsApi;
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

class LeaseController extends AbstractController
{

    /**
     * @Route("api/leases/get/{propertyGuid}")
     */
    public function getLeases($propertyGuid, LeaseApi $leaseApi, Request $request, LoggerInterface $logger): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $response = $leaseApi->getLeases($propertyGuid);
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
    public function createLease(Request $request, LoggerInterface $logger, LeaseApi $leaseApi, TenantApi $tenantApi): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('POST')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $response = $tenantApi->createTenant($request->get('tenantName'), $request->get('phone'), $request->get('email'),$request->get('id_document_type'),  $request->get('application_id_number'), $request->get('salary'), $request->get('occupation'),$request->get('adult_count'),$request->get('child_count'));
        if($response["result_code"] == 1){
            return new JsonResponse($response , 200, array());
        }
        $response = $leaseApi->createLease($response["tenant"], $request->get('unitId'), $request->get('start_date'), $request->get('end_date'), $request->get('deposit'), $request->get('lease_id'), $request->get('payment_rules'));
        return new JsonResponse($response , 200, array());
    }


    /**
     * @Route("public/lease/upload/")
     * @throws \Exception
     */
    public function uploadLeaseDocument( Request $request, LoggerInterface $logger, FileUploaderApi $uploader, LeaseApi $leaseApi): Response
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
        $uploader->setExtensions(array('pdf'));  //allowed extensions list//

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