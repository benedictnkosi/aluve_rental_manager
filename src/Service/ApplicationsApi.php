<?php

namespace App\Service;

use App\Entity\Application;
use App\Entity\Leases;
use App\Entity\Properties;
use App\Entity\Tenant;
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

    public function getApplications($propertyGuid): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $property = $this->em->getRepository(Properties::class)->findOneBy(array('guid' => $propertyGuid));
            if ($property == null) {
                return array(
                    'result_message' => "Error. Property not found",
                    'result_code' => 1
                );
            }

            $applications = $this->em->getRepository("App\Entity\Application")->createQueryBuilder('a')
                ->where('a.property = :property')
                ->andWhere("a.status = 'new' or a.status = 'docs_uploaded' or a.status = 'accepted'")
                ->setParameter('property', $property->getId())
                ->getQuery()
                ->getResult();

            if (sizeof($applications) < 1) {
                return array(
                    'result_message' => "Error. No applications found",
                    'result_code' => 1
                );
            }
            //get documents
            $documentApi = new DocumentApi($this->em, $this->logger);
            foreach ($applications as $application) {
                $bankStatementDocument = $documentApi->getDocumentName($application->getTenant()->getId(), "Bank Statement");
                $PayslipDocument = $documentApi->getDocumentName($application->getTenant()->getId(), "payslip");
                $coBankStatementDocument = $documentApi->getDocumentName($application->getTenant()->getId(), "Co-Bank Statement");
                $coPayslipDocument = $documentApi->getDocumentName($application->getTenant()->getId(), "Co-payslip");
                $bankStatementDocumentName = "";
                $PayslipDocumentName = "";
                $coBankStatementDocumentName = "";
                $coPayslipDocumentName = "";

                if ($bankStatementDocument["result_code"] == 0) {
                    $bankStatementDocumentName = $bankStatementDocument["name"];
                }

                if ($PayslipDocument["result_code"] == 0) {
                    $PayslipDocumentName = $PayslipDocument["name"];
                }

                if ($coBankStatementDocument["result_code"] == 0) {
                    $coBankStatementDocumentName = $coBankStatementDocument["name"];
                }


                if ($coPayslipDocument["result_code"] == 0) {
                    $coPayslipDocumentName = $coPayslipDocument["name"];
                }
                $responseArray[] = array(
                    "application" => $application,
                    "applicant_bank_statement" => $bankStatementDocumentName,
                    "applicant_payslip" => $PayslipDocumentName,
                    "co_applicant_bank_statement" => $coBankStatementDocumentName,
                    "co_applicant_payslip" => $coPayslipDocumentName,
                );
            }

            return $responseArray;
        } catch (Exception $ex) {
            $this->logger->error("Error " . $ex->getMessage() . $ex->getTraceAsString());
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
            $unit = $this->em->getRepository(Units::class)->findOneBy(array('guid' => $request->get("unit_id")));
            if ($unit == null) {
                return array(
                    'result_message' => "Error: Unit not found",
                    'result_code' => 1
                );
            }

            //validate minimum salary
            if (intval($request->get("application_salary")) < intval($unit->getMinGrossSalary())) {
                return array(
                    'result_message' => "Error: Your combined salary is below the minimum required",
                    'result_code' => 1
                );
            }

            $tenantApi = new TenantApi($this->em, $this->logger);
            $response = $tenantApi->createTenant($request->get("application_name"), $request->get("application_phone"), $request->get("application_email"), $request->get("id_document_type"), $request->get("application_id_number"), $request->get("application_salary"), $request->get("application_occupation"), $request->get("adult_count"), $request->get("child_count"));
            if ($response["result_code"] == 1) {
                return $response;
            }
            $application = new Application();
            $application->setTenant($response["tenant"]);
            $application->setUnit($unit);
            $application->setDate(new DateTime());
            $application->setUpdatedDate(new DateTime());
            $application->setUid($this->generateGuid());
            $application->setStatus("new");
            $application->setProperty($unit->getProperty());
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
            if ($application == null) {
                return array(
                    'result_message' => "Failed to upload document. Application not found",
                    'result_code' => 1
                );
            }
            $tenant = $application->getTenant();
            $documentApi = new DocumentApi($this->em, $this->logger);

            if (strcmp($documentType, "statement") == 0) {
                $documentApi->addDocument($tenant->getId(), "Bank Statement", $fileName);
            } else if (strcmp($documentType, "payslip") == 0) {
                $documentApi->addDocument($tenant->getId(), "payslip", $fileName);
            } else if (strcmp($documentType, "co_statement") == 0) {
                $documentApi->addDocument($tenant->getId(), "Co-Bank Statement", $fileName);
            } else if (strcmp($documentType, "co_payslip") == 0) {
                $documentApi->addDocument($tenant->getId(), "Co-payslip", $fileName);
            } else {
                return array(
                    'result_message' => "Error. Document type not suppoerted",
                    'result_code' => 1
                );
            }

            $bankStatementDocument = $documentApi->getDocumentName($tenant->getId(), "Bank Statement");
            $PayslipDocument = $documentApi->getDocumentName($tenant->getId(), "payslip");
            $allDocsUploaded = $bankStatementDocument["result_code"] == 0 && $PayslipDocument["result_code"] == 0;

            if ($allDocsUploaded) {
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
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    #[ArrayShape(['result_message' => "string", 'result_code' => "int"])]
    public function acceptApplication($applicationId, $startDate, $endDate): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $application = $this->em->getRepository(Application::class)->findOneBy(array('id' => $applicationId));
            if ($application == null) {
                return array(
                    'result_message' => "Application not found",
                    'result_code' => 1
                );
            }

            //send whatsapp with acceptance
//            $leaseLink = $_SERVER['SERVER_PROTOCOL'] . "://" . $_SERVER['HTTP_HOST'] . "/api/document/" . $application->getUnit()->getProperty()->getLeaseFileName();
//            $message = "We are happy to let you know that your application for " . $application->getUnit()->getName() . " @ " . $application->getUnit()->getProperty()->getName() . " has been accepted.
//            Please download and sign the lease. " . $leaseLink;

            $communicationApi = new CommunicationApi($this->em, $this->logger);
            //$response = $communicationApi->sendWhatsApp($application->getPhone(), $message);
//            if ($response["result_code"] !== 0) {
//                return array(
//                    'result_message' => $response["result_message"],
//                    'result_code' => 1
//                );
//            }


            $leaseApi = new LeaseApi($this->em, $this->logger);
            $response = $leaseApi->createLease($application->getTenant(), $application->getUnit()->getGuid(), $startDate, $endDate, "0", "", "pending_docs");
            if ($response["result_code"] !== 0) {
                return $response;
            }

            $application->setStatus("accepted");
            $this->em->persist($application);
            $this->em->flush($application);

            //add application fee to the lease if enabled
            if (intval($application->getUnit()->getProperty()->getApplicationFee()) > 0) {
                $transactionApi = new TransactionApi($this->em, $this->logger);
                $now = new DateTime();
                $transactionApi->addTransaction($response["id"], $application->getUnit()->getProperty()->getApplicationFee(), "Application Fee", $now->format("Y-m-d"));
            }

            //add deposit to the lease if enabled
            if (intval($application->getUnit()->getProperty()->getDepositPecent()) > 0) {
                $transactionApi = new TransactionApi($this->em, $this->logger);
                $now = new DateTime();
                $unitRentalAmount = $application->getUnit()->getRent();
                $deposit = $unitRentalAmount * ($application->getUnit()->getProperty()->getDepositPecent() / 100);
                $transactionApi->addTransaction($response["id"], $deposit, "Unit Deposit", $now->format("Y-m-d"));
            }

            //update unit listed status
            $unitApi = new UnitApi($this->em, $this->logger);
            $unitApi->updateUnit("listed", false, $application->getUnit()->getGuid());

            //send sms to applicant
            $smsApi = new SMSApi($this->em, $this->logger);
            $tenantPortalURL = $_SERVER['SERVER_PROTOCOL'] . "://" . $_SERVER['HTTP_HOST'] . "/tenant";
            $message = "Application for " . $application->getUnit()->getName() . " @ " . $application->getProperty()->getName() . " has been accepted. Download lease " . $tenantPortalURL;
            $isSMSSent = $smsApi->sendMessage("+27" . substr($application->getTenant()->getPhone(), 0, 9), $message);

            if ($isSMSSent) {
                return array(
                    'result_message' => "Successfully created lease from the application.",
                    'result_code' => 0
                );
            } else {
                return array(
                    'result_message' => "Error. Created lease from the application. SMS to Applicant failed",
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
            if ($application == null) {
                return array(
                    'result_message' => "Failed to decline application. Application not found",
                    'result_code' => 1
                );
            }

            $application->setStatus("declined");
            $this->em->persist($application);
            $this->em->flush($application);

            //send sms to applicant
            $smsApi = new SMSApi($this->em, $this->logger);
            $tenantPortalURL = $_SERVER['SERVER_PROTOCOL'] . "://" . $_SERVER['HTTP_HOST'] . "/tenant";
            $message = "Unfortunately your application for " . $application->getUnit()->getName() . " @ " . $application->getProperty()->getName() . " has been declined.";
            $isSMSSent = $smsApi->sendMessage("+27" . substr($application->getTenant()->getPhone(), 0, 9), $message);

            if ($isSMSSent) {
                return array(
                    'result_message' => "Successfully declined the application",
                    'result_code' => 0
                );
            } else {
                return array(
                    'result_message' => "Error. Declined the application. SMS to Applicant failed",
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

}