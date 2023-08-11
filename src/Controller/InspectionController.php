<?php

namespace App\Controller;

use App\Service\ApplicationsApi;
use App\Service\FileUploaderApi;
use App\Service\LeaseApi;
use App\Service\PropertyApi;
use App\Service\UnitApi;
use JMS\Serializer\SerializerBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InspectionController extends AbstractController
{
    /**
     * @Route("api/inspection/{leaseGuid}")
     */
    public function getLatestInspection($leaseGuid, LeaseApi $leaseApi, Request $request, LoggerInterface $logger): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $response = $leaseApi->getLatestInspection($leaseGuid);

        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($response, 'json');
        return new JsonResponse($jsonContent , 200, array(), true);
    }

    /**
     * @Route("api/inspection/upload/image/")
     * @throws \Exception
     */
    public function uploadInspectionImage( Request $request, LoggerInterface $logger, FileUploaderApi $uploader, LeaseApi $leaseApi): Response
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

        $uploadDir = __DIR__ . '/../../files/inspection_images/';
        $uploader->setDir($uploadDir);
        $uploader->setExtensions(array('jpeg', 'jpg', 'png'));  //allowed extensions list//

        $uploader->setMaxSize(10);//set max file size to be allowed in MB//

        $response = $uploader->uploadImage();
        if($response["result_code"] == 1){
            //upload failed
            header("HTTP/1.1 500 Internal Server Error");
            return new Response($response["result_message"],
                Response::HTTP_NOT_ACCEPTABLE, ['content-type' => 'text/plain']);
        }

        //write to DB
        $response = $leaseApi->addInspectionImage($request->get("inspection_guid"), $response["file_name"]);
        if($response["result_code"] == 1){
            return new JsonResponse($response, 200, array());
        }else{
            return new JsonResponse($response, 201, array());
        }
    }

    /**
     * @Route("api/lease/create/inspection")
     */
    public function createInspection(Request $request, LoggerInterface $logger, LeaseApi $leaseApi): Response{
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('POST')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }

        $response = $leaseApi->createInspection($request->get('lease_guid'), $request->get('inspection_guid'), $request->get('inspection'), $request->get('status'));
        return new JsonResponse($response , 200, array());
    }

    /**
     * @Route("api/inspection_image/{name}")
     */
    public function getDocument($name, LoggerInterface $logger): BinaryFileResponse
    {
        $logger->info("Starting Method: " . __METHOD__);
        $documentDir = __DIR__ . '/../../files/inspection_images/';

        return new BinaryFileResponse($documentDir . $name);
    }
}