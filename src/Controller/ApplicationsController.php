<?php

namespace App\Controller;

use App\Service\ApplicationsApi;
use App\Service\FileUploaderApi;
use App\Service\LeaseApi;
use App\Service\PropertyApi;
use App\Service\TransactionApi;
use App\Service\UnitApi;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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

class ApplicationsController extends AbstractController
{

    /**
     * @Route("api/applications/get/{propertyGuid}")
     */
    public function getApplications($propertyGuid, Request $request, LoggerInterface $logger, ApplicationsApi $applicationsApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('GET')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $applicationsApi->getApplications($propertyGuid);
        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($response, 'json');
        return new JsonResponse($jsonContent , 200, array(), true);
    }


    /**
     * @Route("no_auth/application/new")
     */
    public function createApplication(Request $request, LoggerInterface $logger, ApplicationsApi $applicationsApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('POST')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $applicationsApi->createApplication($request);
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/application/accept")
     */
    public function acceptApplication(Request $request, LoggerInterface $logger, ApplicationsApi $applicationsApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('PUT')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $applicationsApi->acceptApplication($request->get("id"));
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/application/convert_to_lease")
     */
    public function convertApplicationToLease(Request $request, LoggerInterface $logger, ApplicationsApi $applicationsApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('PUT')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $applicationsApi->convertApplicationToLease($request->get("id"), $request->get("start_date"), $request->get("end_date"));
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/document/{name}")
     */
    public function getDocument($name, LoggerInterface $logger): BinaryFileResponse
    {
        $logger->info("Starting Method: " . __METHOD__);
        $documentDir = __DIR__ . '/../../files/application_documents/';

        return new BinaryFileResponse($documentDir . $name);
    }

    /**
     * @Route("no_auth/lease_document/{name}")
     */
    public function getLease($name, LoggerInterface $logger): BinaryFileResponse
    {
        $logger->info("Starting Method: " . __METHOD__);
        $documentDir = __DIR__ . '/../../files/application_documents/';
        try{
            $file = new BinaryFileResponse($documentDir . $name);
        }catch(Exception){
            $file = new BinaryFileResponse($documentDir . "File Not Found.jpg");
        }
        return $file;
    }


    /**
     * @Route("api/application/decline")
     */
    public function declineApplication(Request $request, LoggerInterface $logger, ApplicationsApi $applicationsApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('PUT')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $applicationsApi->declineApplication($request->get("id"));
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("no_auth/application/upload/")
     * @throws \Exception
     */
    public function uploadSupportingDocument( Request $request, LoggerInterface $logger, FileUploaderApi $uploader, ApplicationsApi $applicationsApi): Response
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
        $uploader->setExtensions(array('pdf', 'jpeg', 'jpg', 'bmp', 'png'));  //allowed extensions list//

        $uploader->setMaxSize(5);//set max file size to be allowed in MB//

        $response = $uploader->uploadFile();
        if($response["result_code"] == 1){
            //upload failed
            header("HTTP/1.1 500 Internal Server Error");
            return new Response($response["result_message"],
                Response::HTTP_NOT_ACCEPTABLE, ['content-type' => 'text/plain']);
        }

        //write to DB
        $response = $applicationsApi->addSupportingDoc($request->get("application_id"), $request->get("document_type"), $response["file_name"]);
        if($response["result_code"] == 1){
            return new JsonResponse($response, 200, array());
       }else{
            return new JsonResponse($response, 201, array());

        }

    }
}