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
    private $authApi;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
        $this->authApi = new AuthApi($this->em, $this->logger);

        if (session_id() === '') {
            $logger->info("Session id is empty" . __METHOD__);
            session_start();
        }
    }

    public function getApplications($propertyGuid): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
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
                ->andWhere("a.status = 'financials uploaded' or a.status = 'declined' or a.status = 'accepted' or a.status = 'lease uploaded' or a.status = 'tenant'")
                ->setParameter('property', $property->getId())
                ->getQuery()
                ->getResult();

            if (sizeof($applications) < 1) {
                return array(
                    'result_message' => "Error. No applications found",
                    'result_code' => 1
                );
            }

            return $applications;
        } catch (Exception $ex) {
            $this->logger->error("Error " . $ex->getMessage() . $ex->getTraceAsString());
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }


    public function getTenantApplications(): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {
            $emailAddress = $this->getUser()->getUserIdentifier();
            $tenant = $this->em->getRepository(Tenant::class)->findOneBy(array('email' => $emailAddress));
            if ($tenant == null) {
                return array(
                    'result_message' => "Error. Tenant not found",
                    'result_code' => 1
                );
            }

            $applications = $this->em->getRepository("App\Entity\Application")->createQueryBuilder('a')
                ->where('a.tenant = :property')
                ->setParameter('property', $tenant->getId())
                ->getQuery()
                ->getResult();

            if (sizeof($applications) < 1) {
                return array(
                    'result_message' => "Error. No applications found",
                    'result_code' => 1
                );
            }

            return $applications;
        } catch (Exception $ex) {
            $this->logger->error("Error " . $ex->getMessage() . $ex->getTraceAsString());
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    public function getApplication($applicationGuid): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {
            $application = $this->em->getRepository(Application::class)->findOneBy(array('uid' => $applicationGuid));
            if ($application == null) {
                return array(
                    'result_message' => "Error. Application not found",
                    'result_code' => 1
                );
            }

            //get documents
            $documentApi = new DocumentApi($this->em, $this->logger);

            $documents = $documentApi->getApplicationDocuments($application->getId());

            return array(
                "application" => $application,
                "documents" => $documents
            );
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

            $guid = $this->generateGuid();
            $this->logger->info("guid is " . $guid);
            $application = new Application();
            $application->setTenant($response["tenant"]);
            $application->setUnit($unit);
            $application->setDate(new DateTime());
            $application->setUpdatedDate(new DateTime());
            $application->setUid($guid);
            $application->setStatus("new");
            $application->setProperty($unit->getProperty());
            $this->em->persist($application);
            $this->em->flush($application);

            //send email
            $message = "New application received for " . $unit->getProperty()->getName() . " - " . $unit->getName();
            $subject = "Aluve App - New Application";

            $link =  "https://" . $_SERVER['HTTP_HOST'] . "/landlord";
            $linkText = "View Application";
            $template = "generic";
            $communicationApi = new CommunicationApi($this->em, $this->logger);
            $communicationApi->sendEmail($unit->getProperty()->getEmail(), $unit->getProperty()->getName(), $subject, $message, $link, $linkText, $template);


            return array(
                'result_message' => "Successfully created application. Please upload documents",
                'result_code' => 0,
                'id' => $application->getUid(),
            );

        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    public function addFinancialsDoc($applicationGuid, $documentType, $fileName): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $application = $this->em->getRepository(Application::class)->findOneBy(array('uid' => $applicationGuid));
            if ($application == null) {
                return array(
                    'result_message' => "Failed to upload document. Application not found",
                    'result_code' => 1
                );
            }
            $tenant = $application->getTenant();
            $documentApi = new DocumentApi($this->em, $this->logger);

            if (strcmp($documentType, "statement") == 0) {
                $documentApi->addDocument($application->getId(), "Bank Statement", $fileName);
            } else if (strcmp($documentType, "payslip") == 0) {
                $documentApi->addDocument($application->getId(), "payslip", $fileName);
            } else if (strcmp($documentType, "co_statement") == 0) {
                $documentApi->addDocument($application->getId(), "Co-Bank Statement", $fileName);
            } else if (strcmp($documentType, "co_payslip") == 0) {
                $documentApi->addDocument($application->getId(), "Co-payslip", $fileName);
            } else {
                return array(
                    'result_message' => "Error. Document type not suppoerted",
                    'result_code' => 1
                );
            }

            $bankStatementDocument = $documentApi->getDocumentName($application->getId(), "Bank Statement");
            $PayslipDocument = $documentApi->getDocumentName($application->getId(), "payslip");
            $allDocsUploaded = $bankStatementDocument["result_code"] == 0 && $PayslipDocument["result_code"] == 0;

            if ($allDocsUploaded) {
                $application->setStatus("financials uploaded");
                //send email
                $message = "All supporting documents uploaded for  " . $tenant->getName() . " - " . $application->getUnit()->getProperty()->getName() . ", " . $application->getUnit()->getName();
                $subject = "Aluve App - Financials Uploaded";

                $link =  "https://" . $_SERVER['HTTP_HOST'] . "/landlord";
                $linkText = "View Application";
                $template = "generic";
                $communicationApi = new CommunicationApi($this->em, $this->logger);
                $communicationApi->sendEmail($application->getUnit()->getProperty()->getEmail(), $application->getUnit()->getProperty()->getName(), $subject, $message, $link, $linkText, $template);
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
    public function acceptApplication($applicationGuid): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $application = $this->em->getRepository(Application::class)->findOneBy(array('uid' => $applicationGuid));
            if ($application == null) {
                return array(
                    'result_message' => "Application not found",
                    'result_code' => 1
                );
            }

            $auth = $this->authApi->isAuthorisedToChangeUnit($application->getUnit()->getId());
            if ($auth["result_code"] == 1) {
                return $auth;
            }

            $application->setStatus("accepted");
            $this->em->persist($application);
            $this->em->flush($application);

            //send email
            $message = "Application for " . $application->getUnit()->getName() . " @ " . $application->getProperty()->getName() . " has been accepted. Please sign lease and upload your ID document.";
            $subject = "Aluve App - Application Accepted";

            $link =  "https://" . $_SERVER['HTTP_HOST'] . "/tenant";
            $linkText = "Sign Lease";
            $template = "generic";
            $communicationApi = new CommunicationApi($this->em, $this->logger);
            $communicationApi->sendEmail($application->getTenant()->getEmail(), $application->getTenant()->getName(), $subject, $message, $link, $linkText, $template);

            return array(
                'result_message' => "Successfully accepted application.",
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

    #[ArrayShape(['result_message' => "string", 'result_code' => "int"])]
    public function convertApplicationToLease($applicationGuid, $startDate, $endDate): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $application = $this->em->getRepository(Application::class)->findOneBy(array('uid' => $applicationGuid));
            if ($application == null) {
                return array(
                    'result_message' => "Application not found",
                    'result_code' => 1
                );
            }

            $auth = $this->authApi->isAuthorisedToChangeUnit($application->getUnit()->getId());
            if ($auth["result_code"] == 1) {
                return $auth;
            }
            //send whatsapp with acceptance
//            $leaseLink =  "https://" . $_SERVER['HTTP_HOST'] . "/api/document/" . $application->getUnit()->getProperty()->getLeaseFileName();
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
            $response = $leaseApi->createLease($application->getTenant(), $application->getUnit()->getGuid(), $startDate, $endDate, "0", "", "active");
            if ($response["result_code"] !== 0) {
                return $response;
            }

            $application->setStatus("tenant");
            $this->em->persist($application);
            $this->em->flush($application);

            //update tenant status
            $tenant = $application->getTenant();
            $tenant->setStatus("active");
            $this->em->persist($tenant);
            $this->em->flush($tenant);

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

            //send email
            $message = "Application for " . $application->getUnit()->getName() . " @ " . $application->getProperty()->getName() . " has bee converted to a lease. Congratulations you are officially a tenant!!!";
            $subject = "Aluve App - Official Tenant";

            $link =  "https://" . $_SERVER['HTTP_HOST'] . "/tenant";
            $linkText = "View Tenant Portal";
            $template = "generic";
            $communicationApi = new CommunicationApi($this->em, $this->logger);
            $communicationApi->sendEmail($tenant->getEmail(), $tenant->getName(), $subject, $message, $link, $linkText, $template);

            return array(
                'result_message' => "Successfully created lease from the application.",
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

    #[ArrayShape(['result_message' => "string", 'result_code' => "int"])]
    public function declineApplication($applicationGuid): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $application = $this->em->getRepository(Application::class)->findOneBy(array('uid' => $applicationGuid));
            if ($application == null) {
                return array(
                    'result_message' => "Failed to decline application. Application not found",
                    'result_code' => 1
                );
            }

            $auth = $this->authApi->isAuthorisedToChangeUnit($application->getUnit()->getId());
            if ($auth["result_code"] == 1) {
                return $auth;
            }

            $application->setStatus("declined");
            $this->em->persist($application);
            $this->em->flush($application);

            //send email
            $message = "Unfortunately, your application for " . $application->getUnit()->getName() . " @ " . $application->getProperty()->getName() . " has been declined. Thank you very much for your interest.";
            $subject = "Aluve App - Application Declined";

            $link = "https://property24.com";
            $linkText = "Search For Property";
            $template = "generic";
            $communicationApi = new CommunicationApi($this->em, $this->logger);
            $communicationApi->sendEmail($application->getTenant()->getEmail(), $application->getTenant()->getName(), $subject, $message, $link, $linkText, $template);


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