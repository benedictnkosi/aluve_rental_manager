<?php

namespace App\Service;

use App\Entity\Application;
use App\Entity\Document;
use App\Entity\DocumentTypeLookup;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DocumentApi extends AbstractController
{

    private $em;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
        if (session_id() === '') {
            $logger->info("Session id is empty" . __METHOD__);
            session_start();
        }
    }

    public function addDocument($applicationId, $documentType, $fileName): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $application = $this->em->getRepository(Application::class)->findOneBy(array('id' => $applicationId));
            if($application == null){
                return array(
                    'result_message' => "Error. Application not found",
                    'result_code' => 1
                );
            }

            $documentType = $this->em->getRepository(DocumentTypeLookup::class)->findOneBy(array('name' => $documentType));
            if($documentType == null){
                return array(
                    'result_message' => "Error. Document type invalid",
                    'result_code' => 1
                );
            }

            //remove old document for application and id type
            $document = $this->em->getRepository(Document::class)->findOneBy(array('application' => $application->getId(), 'documentType' => $documentType->getId()));
            if($document !== null){
                $this->em->remove($document);
                $this->em->flush($document);
            }

            $document = new Document();
            $document->setApplication($application);
            $document->setDocumentType($documentType);
            $document->setName($fileName);
            $this->em->persist($document);
            $this->em->flush($document);

            return array(
                'result_message' => "Successfully added document",
                'result_code' => 0
            );
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }


    public function getDocumentName($applicationId, $documentType): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $documentType = $this->em->getRepository(DocumentTypeLookup::class)->findOneBy(array('name' => $documentType));
            if($documentType == null){
                return array(
                    'result_message' => "Error. Document type invalid",
                    'result_code' => 1
                );
            }

            $document = $this->em->getRepository(Document::class)->findOneBy(array('application' => $applicationId, 'documentType' => $documentType, 'status' => 'active'));

            if($document == null){
                return array(
                    'result_message' => "Error. Document not found",
                    'result_code' => 1
                );
            }

            return array(
                'name' => $document->getName(),
                'result_code' => 0
            );

        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    public function getApplicationDocuments($applicationId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $application = $this->em->getRepository(Application::class)->findOneBy(array('id' => $applicationId));
            if($application == null){
                return array(
                    'result_message' => "Error. Application not found",
                    'result_code' => 1
                );
            }

            return $this->em->getRepository(Document::class)->findBy(array('application' => $application, 'status' => 'active'));
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }


}