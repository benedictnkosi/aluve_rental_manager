<?php

namespace App\Service;

use App\Entity\Application;
use App\Entity\Leases;
use App\Entity\Units;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApplicationsApi extends AbstractController
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


    public function getApplications($propertyId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $applications = $this->em->getRepository("App\Entity\Application")->createQueryBuilder('a')
                ->where('a.property = :property')
                ->andWhere("a.status = 'new' or a.status = 'docs_uploaded' or a.status = 'accepted'")
                ->setParameter('property', $propertyId)
                ->getQuery()
                ->getResult();

            if (sizeof($applications) < 1) {
                return array(
                    'result_message' => "No applications found",
                    'result_code' => 1
                );
            }

            return $applications;
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    public function createApplication($request): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $unit = $this->em->getRepository(Units::class)->findOneBy(array('idunits' => $request->get("unit_id")));
            if($unit == null){
                return array(
                    'result_message' => "Error: Unit not found",
                    'result_code' => 1
                );
            }

            //validate minimum salary
            if(intval($request->get("application_salary")) < intval($unit->getMinGrossSalary())){
                return array(
                    'result_message' => "Error: Your combined salary is below the minimum required",
                    'result_code' => 1
                );
            }

            $application = new Application();
            $application->setUnit($unit);
            $application->setName($request->get("application_name"));
            $application->setPhone($request->get("application_phone"));
            $application->setEmail($request->get("application_email"));
            $application->setIdNumber($request->get("application_id_number"));
            $application->setSalary($request->get("application_salary"));
            $application->setOccupation($request->get("application_occupation"));
            $application->setDate(new DateTime());
            $application->setUpdatedDate(new DateTime());
            $application->setUid($this->generateGuid());
            $application->setStatus("new");
            $application->setProperty($unit->getProperty());
            $application->setChildren(intval($request->get("child_count")));
            $application->setAdults(intval($request->get("adult_count")));

            $this->em->persist($application);
            $this->em->flush($application);

            return array(
                'result_message' => "Successfully created application",
                'result_code' => 0,
                'id' => $application->getId()
            );

        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    public function addSupportingDoc($applicationId, $documentType, $fileName): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $application = $this->em->getRepository(Application::class)->findOneBy(array('id' => $applicationId));
            if($application == null){
                return array(
                    'result_message' => "Failed to upload documents. Application not found",
                    'result_code' => 1
                );
            }

            if(strcmp($documentType, "statement")==0){
                $application->setBankStatement($fileName);
            }else if(strcmp($documentType, "payslip")==0){
                $application->setPayslip($fileName);
            }else if(strcmp($documentType, "co_statement")==0){
                $application->setCoApplicantBankStatement($fileName);
            }else if(strcmp($documentType, "co_payslip")==0){
                $application->setCoApplicantPayslip($fileName);
            }else{
                return array(
                    'result_message' => "Document type not suppoerted",
                    'result_code' => 1
                );
            }

            $allDocsUploaded = $application->getBankStatement() !== null && $application->getPayslip() !== null;

            if($allDocsUploaded){
                $application->setStatus("docs_uploaded");
            }

            $this->em->persist($application);
            $this->em->flush($application);
            return array(
                'result_message' => "Successfully uploaded file",
                'result_code' => 0,
                'alldocs_uploaded' => $allDocsUploaded,
                'application_id' => "AL-APP-" . $application->getId()
            );
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    function generateGuid(): string
    {
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    #[ArrayShape(['result_message' => "string", 'result_code' => "int"])]
    public function acceptApplication($applicationId, $startDate, $endDate, $deposit): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $application = $this->em->getRepository(Application::class)->findOneBy(array('id' => $applicationId));
            if($application == null){
                return array(
                    'result_message' => "Failed to upload documents. Application not found",
                    'result_code' => 1
                );
            }

            $leaseApi = new LeaseApi($this->em, $this->logger);
            $response = $leaseApi->createLease($application->getName(), $application->getPhone(), $application->getEmail(), $application->getUnit()->getIdunits(), $startDate, $endDate, $deposit, "0", "",$application->getAdults(), $application->getChildren(),  "pending_docs", );
            $application->setStatus("accepted");
            $this->em->persist($application);
            $this->em->flush($application);

            if($response["result_code"] == 0){
                return array(
                    'result_message' => "Successfully created lease from the application",
                    'result_code' => 0
                );
            }else{
                return array(
                    'result_message' => "Failed to convert application to lease. " . $response["result_message"],
                    'result_code' => 1
                );
            }

        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }


    #[ArrayShape(['result_message' => "string", 'result_code' => "int"])]
    public function declineApplication($applicationId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $application = $this->em->getRepository(Application::class)->findOneBy(array('id' => $applicationId));
            if($application == null){
                return array(
                    'result_message' => "Failed to decline application. Application not found",
                    'result_code' => 1
                );
            }

            $application->setStatus("declined");
            $this->em->persist($application);
            $this->em->flush($application);

            return array(
                'result_message' => "Successfully declined the application",
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