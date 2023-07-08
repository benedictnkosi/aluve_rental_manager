<?php

namespace App\Service;

use App\Entity\Application;
use App\Entity\Document;
use App\Entity\DocumentTypeLookup;
use App\Entity\Inspection;
use App\Entity\InspectionImage;
use App\Entity\Leases;
use App\Entity\PaymentRule;
use App\Entity\Properties;
use App\Entity\Propertyusers;
use App\Entity\Tenant;
use App\Entity\Units;
use App\Entity\User;
use Cassandra\Date;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
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

    public function addDocument($tenantId, $documentType, $fileName): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $tenant = $this->em->getRepository(Tenant::class)->findOneBy(array('id' => $tenantId));
            if($tenant == null){
                return array(
                    'result_message' => "Tenant not found",
                    'result_code' => 1
                );
            }

            $documentType = $this->em->getRepository(DocumentTypeLookup::class)->findOneBy(array('name' => $documentType));
            if($documentType == null){
                return array(
                    'result_message' => "Document type invalid",
                    'result_code' => 1
                );
            }
            $document = new Document();
            $document->setTenant($tenant);
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


    public function getDocumentName($tenantId, $documentType): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $tenant = $this->em->getRepository(Tenant::class)->findOneBy(array('id' => $tenantId));
            if($tenant == null){
                return array(
                    'result_message' => "Tenant not found",
                    'result_code' => 1
                );
            }

            $documentType = $this->em->getRepository(DocumentTypeLookup::class)->findOneBy(array('name' => $documentType));
            if($documentType == null){
                return array(
                    'result_message' => "Document type invalid",
                    'result_code' => 1
                );
            }

            $document = $this->em->getRepository(Document::class)->findOneBy(array('tenant' => $tenantId, 'documentType' => $documentType, 'status' => 'active'));

            if($document == null){
                return array(
                    'result_message' => "Document not found",
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
}