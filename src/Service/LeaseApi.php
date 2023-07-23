<?php

namespace App\Service;

use App\Entity\Application;
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
use Symfony\Component\HttpFoundation\JsonResponse;

class LeaseApi extends AbstractController
{

    private $em;
    private $logger;
    private $transactionApi;
    private $authApi;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
        $this->authApi = new AuthApi($this->em, $this->logger);
        $this->transactionApi = new TransactionApi($entityManager, $logger);
        if (session_id() === '') {
            $logger->info("Session id is empty" . __METHOD__);
            session_start();
        }
    }

    public function getLeases($propertyGuid): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {

            //validate property id
            if (strlen($propertyGuid) !== 36) {
                return array(
                    'result_message' => "Error. Property GUID is invalid",
                    'result_code' => 1
                );
            }

            $property = $this->em->getRepository(Properties::class)->findOneBy(array('guid' => $propertyGuid));
            if ($property == null) {
                return array(
                    'result_message' => "Error. Property not found",
                    'result_code' => 1
                );
            }


            $leases = $this->em->getRepository("App\Entity\Leases")->createQueryBuilder('l')
                ->leftJoin('l.unit', 'u')
                ->where('l.property = :property')
                ->andWhere("l.status = 'active'")
                ->setParameter('property', $property->getId())
                ->orderBy('u.name', 'ASC')
                ->getQuery()
                ->getResult();
            if (sizeof($leases) < 1) {
                return array(
                    'result_message' => "Error. No leases found",
                    'result_code' => 1
                );
            }
            $documentApi = new DocumentApi($this->em, $this->logger);
            foreach ($leases as $lease) {
                $leaseDocumentName = "";
                $idDocumentName = "";
                $popDocumentName = "";

                $tenant = $lease->getTenant();
                $due = $this->transactionApi->getBalanceDue($lease->getId());

                $LeaseDocument = $documentApi->getDocumentName($tenant->getId(), "Signed Lease");
                $idDocument = $documentApi->getDocumentName($tenant->getId(), "ID Document");
                $popDocument = $documentApi->getDocumentName($tenant->getId(), "Proof OF Payment");

                if ($LeaseDocument["result_code"] == 0) {
                    $leaseDocumentName = $LeaseDocument["name"];
                }

                if ($idDocument["result_code"] == 0) {
                    $idDocumentName = $idDocument["name"];
                }

                if ($popDocument["result_code"] == 0) {
                    $popDocumentName = $popDocument["name"];
                }

                $inspection = $this->em->getRepository(Inspection::class)->findBy(array('lease' => $lease, 'status' => "active"));
                $inspectionExist = false;
                if (sizeof($inspection) > 0) {
                    $inspectionExist = true;
                }

                $documentApi = new DocumentApi($this->em, $this->logger);
                $responseArray[] = array(
                    'unit_name' => $lease->getUnit()->getName(),
                    'unit_id' => $lease->getUnit()->getId(),
                    'tenant_name' => $tenant->getName(),
                    'tenant_guid' => $tenant->getGuid(),
                    'phone_number' => $tenant->getPhone(),
                    'email' => $tenant->getEmail(),
                    'adults' => $tenant->getAdults(),
                    'children' => $tenant->getChildren(),
                    'id_number' => $tenant->getIdNumber(),
                    'id_document_type' => $tenant->getIdDocumentType(),
                    'salary' => $tenant->getSalary(),
                    'occupation' => $tenant->getOccupation(),
                    'tenant_id' => $tenant->getId(),
                    'lease_start' => $lease->getStart()->format("Y-M-d"),
                    'lease_end' => $lease->getEnd()->format("Y-M-d"),
                    'lease_id' => $lease->getId(),
                    'due' => "R" . number_format(intval($due["result_message"]), 2, '.', ''),
                    'guid' => $lease->getGuid(),
                    'status' => $lease->getStatus(),
                    'inspection_exist' => $inspectionExist,
                    'payment_rules' => $lease->getPaymentRules(),
                    "signed_lease" => $leaseDocumentName,
                    "id_document" => $idDocumentName,
                    "proof_of_payment" => $popDocumentName,
                );
            }
            return $responseArray;
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    function generateGuid()
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    public function getLease($guid): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $this->logger->info("Guid: " . $guid);
        $responseArray = array();
        try {
            //validate property id
            if (strlen($guid) !== 36) {
                return array(
                    'result_message' => "Error. Lease GUID is invalid",
                    'result_code' => 1
                );
            }

            $lease = $this->em->getRepository(Leases::class)->findOneBy(array('guid' => $guid));
            if ($lease == null) {
                return array(
                    'result_message' => "Error. Lease not found",
                    'result_code' => 1
                );
            }

            $allDocsUploaded = $lease->getLeaseAggreement() !== null && $lease->getIdDocument() !== null && $lease->getDepositPop() !== null;

            $now = new DateTime();
            $tenant = $lease->getTenant();
            $due = $this->transactionApi->getBalanceDue($lease->getId());
            return array(
                'unit_name' => $lease->getUnit()->getName(),
                'unit_id' => $lease->getUnit()->getId(),
                'tenant_name' => $tenant->getName(),
                'phone_number' => $tenant->getPhone(),
                'email' => $tenant->getEmail(),
                'adults' => $tenant->getAdults(),
                'children' => $tenant->getChildren(),
                'id_number' => $tenant->getIdNumber(),
                'tenant_id' => $tenant->getId(),
                'lease_start' => $lease->getStart()->format("Y-M-d"),
                'lease_end' => $lease->getEnd()->format("Y-M-d"),
                'lease_id' => $lease->getId(),
                'due' => number_format(intval($due["result_message"]), 2, '.', ''),
                'statement_date' => $now->format("Y-M-d"),
                'property' => $lease->getUnit()->getProperty()->getName() . ", " . $lease->getUnit()->getProperty()->getAddress(),
                'payment_rules' => $lease->getPaymentRules(),
                'alldocs_uploaded' => $allDocsUploaded,
                'bedrooms' => $lease->getUnit()->getBedrooms(),
                'bathrooms' => $lease->getUnit()->getBathrooms()
            );
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }


    public function getLeaseByIdNumber($idNumber, $phoneNumber)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $tenant = $this->em->getRepository(Tenant::class)->findOneBy(array('idNumber' => $idNumber));
            if ($tenant == null) {
                return array(
                    'result_message' => "Error. Tenant not found for ID number",
                    'result_code' => 1
                );
            }

            if (strcmp($tenant->getPhone(), $phoneNumber) !== 0) {
                return array(
                    'result_message' => "Error. Tenant authentication failed",
                    'result_code' => 1
                );
            }

            $lease = $this->em->getRepository(Leases::class)->findOneBy(array('tenant' => $tenant->getId()));
            if ($lease == null) {
                return array(
                    'result_message' => "Error. Lease not found for ID number",
                    'result_code' => 1
                );
            }

            return $lease;
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    #[ArrayShape(['result_message' => "string", 'result_code' => "int"])]
    public function createLease($tenant, $unitId, $startDate, $endDate, $leaseId, $paymentRules, $status = "active"): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $successMessage = "Successfully created lease";


            if (strlen($leaseId) < 1 || !is_numeric($leaseId)) {
                return array(
                    'result_message' => "Error. Lease ID value is invalid",
                    'result_code' => 1
                );
            }

            if (strcmp($status, "active") !== 0 && strcmp($status, "pending_docs") !== 0) {
                return array(
                    'result_message' => "Error. Status value is invalid",
                    'result_code' => 1
                );
            }

            $startDateDateObject = new DateTime($startDate);
            $endDateDateObject = new DateTime($endDate);

            //validate lease number of months
            $totalNights = intval($startDateDateObject->diff($endDateDateObject)->format('%a'));
            if ($totalNights < 30) {
                return array(
                    'result_message' => "Error. The lease period is invalid",
                    'result_code' => 1
                );
            }


            if ($leaseId == 0) {
                $lease = new Leases();
                $unit = $this->em->getRepository(Units::class)->findOneBy(array('guid' => $unitId));
                if ($unit == null) {
                    return array(
                        'result_message' => "Error: Unit not found",
                        'result_code' => 1
                    );
                }
            } else {
                $lease = $this->em->getRepository(Leases::class)->findOneBy(array('id' => $leaseId));

                $auth = $this->authApi->isAuthorisedToChangeUnit($lease->getUnit()->getId());
                if($auth["result_code"] == 1){return $auth;}

                if ($lease == null) {
                    return array(
                        'result_message' => "Error. Lease not found",
                        'result_code' => 1
                    );
                }

                $unit = $this->em->getRepository(Units::class)->findOneBy(array('guid' => $unitId));
                $successMessage = "Successfully updated lease";
            }

            $this->em->persist($tenant);
            $this->em->flush($tenant);

            $lease->setProperty($unit->getProperty());
            $lease->setStart(new DateTime($startDate));
            $lease->setEnd(new DateTime($endDate));
            $lease->setTenant($tenant);
            $lease->setUnit($unit);
            $lease->setGuid($this->generateGuid());
            $lease->setStatus($status);
            if (strlen($paymentRules) > 0) {
                $lease->setPaymentRules($paymentRules);
            } else {
                $lease->setPaymentRules("");
            }
            $this->em->persist($lease);
            $this->em->flush($lease);

            return array(
                'result_message' => $successMessage,
                'result_code' => 0,
                'id' => $lease->getID()
            );

        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    function validateSouthAfricanID($idNumber): bool
    {
        // Remove spaces and dashes from the ID number
        $idNumber = str_replace([' ', '-'], '', $idNumber);

        // Check if the ID number is 13 digits long
        if (strlen($idNumber) !== 13) {
            return false;
        }

        // Check if the ID number contains only numeric characters
        if (!ctype_digit($idNumber)) {
            return false;
        }

        // Calculate the date of birth from the first 6 digits
        $year = substr($idNumber, 0, 2);
        $month = substr($idNumber, 2, 2);
        $day = substr($idNumber, 4, 2);

        $dateOfBirth = DateTime::createFromFormat('ymd', $year . $month . $day);

        // Validate the date of birth
        if (!$dateOfBirth || $dateOfBirth->format('ymd') !== $year . $month . $day) {
            return false;
        }

        // Check the citizenship status (7th digit)
        $citizenship = substr($idNumber, 10, 1);
        if ($citizenship !== '0' && $citizenship !== '1') {
            return false;
        }

        // Calculate the Luhn check digit
        $checkDigit = (int)substr($idNumber, -1);
        $partialSum = 0;

        for ($i = 0; $i < 12; $i++) {
            $digit = (int)$idNumber[$i];
            $partialSum += ($i % 2 === 0) ? $digit : array_sum(str_split($digit * 2));
        }

        $calculatedCheckDigit = (10 - ($partialSum % 10)) % 10;

        // Compare the calculated check digit with the provided check digit
        if ($checkDigit !== $calculatedCheckDigit) {
            return false;
        }

        // If all checks pass, the ID number is valid
        return true;
    }


    public function createInspection($leaseGuid, $inspectionGuid, $json, $status): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {

            if (strlen($json) < 1 || strlen($json) > 5000) {
                return array(
                    'result_message' => "Error. Json value length is invalid",
                    'result_code' => 1
                );
            }

            if (strlen($leaseGuid) !== 36) {
                return array(
                    'result_message' => "Error. Lease guid value is invalid",
                    'result_code' => 1
                );
            }
            if (strlen($inspectionGuid) < 1 || strlen($inspectionGuid) > 36) {
                return array(
                    'result_message' => "Error. Inspection guid value is invalid",
                    'result_code' => 1
                );
            }

            if (strcmp($status, "active") !== 0 && strcmp($status, "new") !== 0) {
                return array(
                    'result_message' => "Error. Status value is invalid",
                    'result_code' => 1
                );
            }

            $lease = $this->em->getRepository(Leases::class)->findOneBy(array('guid' => $leaseGuid));
            if ($lease == null) {
                return array(
                    'result_message' => "Error. Lease not found",
                    'result_code' => 1
                );
            }

            if (strcmp($inspectionGuid, "0") == 0) {
                $inspection = new Inspection();
            } else {
                $inspection = $this->em->getRepository(Inspection::class)->findOneBy(array('guid' => $inspectionGuid));
            }

            $inspection->setDate(new DateTime());
            $inspection->setLease($lease);
            $inspection->setJson($json);
            $inspection->setGuid($this->generateGuid());
            $inspection->setStatus($status);

            $this->em->persist($inspection);
            $this->em->flush($inspection);

            if (strcmp($inspectionGuid, "0") == 0) {
                //send sms to applicant
                $smsApi = new SMSApi($this->em, $this->logger);
                $tenantPortalURL = $_SERVER['SERVER_PROTOCOL'] . "://" . $_SERVER['HTTP_HOST'] . "/tenant";
                $message = "New inspection created. View on your tenant portal " . $tenantPortalURL;
                $isSMSSent = $smsApi->sendMessage("+27" . substr($lease->getTenant()->getPhone(), 0, 9), $message);

                if ($isSMSSent) {
                    return array(
                        'result_message' => "Successfully created inspection",
                        'result_code' => 0,
                        'guid'=> $inspection->getGuid()
                    );
                } else {
                    return array(
                        'result_message' => "Error. Created inspection. SMS to tenant failed",
                        'result_code' => 1
                    );
                }
            } else {
                return array(
                    'result_message' => "Successfully created inspection",
                    'result_code' => 0,
                    'guid'=> $inspection->getGuid()
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

    public function getLatestInspection($leaseGuid)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {

            $lease = $this->em->getRepository(Leases::class)->findOneBy(array('guid' => $leaseGuid));
            if ($lease == null) {
                return array(
                    'result_message' => "Error. Lease not found",
                    'result_code' => 1
                );
            }
            $inspection = $this->em->getRepository(Inspection::class)->findBy(array('lease' => $lease, 'status' => "active"), array('date' => 'DESC'));


            if (sizeof($inspection) < 1) {
                return array(
                    'result_message' => "Error. Inspection not found",
                    'result_code' => 1
                );
            }

            $inspectionImages = $this->em->getRepository(InspectionImage::class)->findBy(array('inspection' => $inspection[0]));

            return array(
                'json' => $inspection[0]->getJson(),
                'date' => $inspection[0]->getDate()->format("Y-M-d"),
                'id' => $inspection[0]->getId(),
                'images' => $inspectionImages
            );
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    public function updateLease($field, $value, $id): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $lease = $this->em->getRepository(Leases::class)->findOneBy(array('id' => $id));
            if ($lease == null) {
                return array(
                    'result_message' => "Error. Lease not found",
                    'result_code' => 1
                );
            }

            switch ($field) {
                case "status":
                    $lease->setStatus($value);
                    break;
                default:
            }

            $this->em->persist($lease);
            $this->em->flush($lease);

            return array(
                'result_message' => "Successfully updated lease",
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
    public function raiseLateFees(): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $leases = $this->em->getRepository(Leases::class)->findBy(array('status' => 'active'));
            if (sizeof($leases) < 1) {
                return array(
                    'result_message' => "No leases found",
                    'result_code' => 1
                );
            }

            $now = new DateTime();
            foreach ($leases as $lease) {
                $lateFee = $lease->getUnit()->getProperty()->getLateFee();
                $response = $this->transactionApi->getBalanceDue($lease->getId());
                $due = intval($response["result_message"]);
                $this->logger->debug("due is this " . $due);
                $todayDay = $now->format("d");
                $rentLateBy = $lease->getUnit()->getProperty()->getRentLateDays();

                $this->logger->debug("late fee: " . $lateFee);
                $this->logger->debug("due: " . $due);
                $this->logger->debug("rent late by : " . $rentLateBy);
                $this->logger->debug("is raise fee day: " . strcmp($todayDay, $rentLateBy) == 0);
                if (intval($lateFee) > 0
                    && $due > 0
                    && strcmp($todayDay, $rentLateBy) == 0) {
                    $this->transactionApi->addTransaction($lease->getId(), $lateFee, "Late Rent Payment Fee", $now->format("Y-m-d"));

                    //send sms to applicant
                    $smsApi = new SMSApi($this->em, $this->logger);
                    $tenantPortalURL = $_SERVER['SERVER_PROTOCOL'] . "://" . $_SERVER['HTTP_HOST'] . "/tenant";
                    $message = "Late payment fee added on your account R" . $lateFee . " , Balance: R" . $due . ". View Statement " . $tenantPortalURL;
                    $isSMSSent = $smsApi->sendMessage("+27" . substr($lease->getTenant()->getPhone(), 0, 9), $message);

                    if ($isSMSSent) {
                        $responseArray = array(
                            'result_message' => "Successfully added transaction",
                            'result_code' => 0
                        );
                    } else {
                        $responseArray = array(
                            'result_message' => "Error. Added transaction. SMS to Applicant failed",
                            'result_code' => 1
                        );
                    }

                }else{
                    $this->logger->error("Conditions to raise fees not met");
                }
            }

            return $responseArray;
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }


    #[ArrayShape(['result_message' => "string", 'result_code' => "int"])]
    public function raiseMonthlyRent(): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $leases = $this->em->getRepository(Leases::class)->findBy(array('status' => 'active'));
            if (sizeof($leases) < 1) {
                return array(
                    'result_message' => "No leases found",
                    'result_code' => 1
                );
            }

            $now = new DateTime();
            $now->modify( 'first day of next month' );
            foreach ($leases as $lease) {
                $rent = $lease->getUnit()->getRent();

                $this->transactionApi->addTransaction($lease->getId(), $rent, $now->format("F Y") . " Rent", $now->format("Y-m-d"));

                //send sms to applicant
                $smsApi = new SMSApi($this->em, $this->logger);
                $tenantPortalURL = $_SERVER['SERVER_PROTOCOL'] . "://" . $_SERVER['HTTP_HOST'] . "/tenant";
                $message = $now->format("F") ." Rent added to your on your statement R" . $rent .". View Statement " . $tenantPortalURL;
                $isSMSSent = $smsApi->sendMessage("+27" . substr($lease->getTenant()->getPhone(), 0, 9), $message);

                if ($isSMSSent) {
                    $responseArray[] = array(
                        'result_message' => "Successfully added monthly rent",
                        'result_code' => 0
                    );
                } else {
                    $responseArray[] = array(
                        'result_message' => "Error. Added monthly rent. SMS to Applicant failed",
                        'result_code' => 1
                    );
                }
            }
            return $responseArray;
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    public function addLeaseDoc($tenantGuid, $documentType, $fileName, $applicationGuid = null): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $documentApi = new DocumentApi($this->em, $this->logger);
            $tenant = $this->em->getRepository(Tenant::class)->findOneBy(array('guid' => $tenantGuid));
            if ($tenant == null) {
                return array(
                    'result_message' => "Failed to upload document. Tenant not found",
                    'result_code' => 1
                );
            }

            $response = $documentApi->addDocument($tenant->getId(), $documentType, $fileName);
            if($response["result_code"] == 1){
                return $response;
            }

            $leaseDocument = $documentApi->getDocumentName($tenant->getId(), "ID Document");
            $popDocument = $documentApi->getDocumentName($tenant->getId(), "Proof OF Payment");
            $signedLeaseDocument = $documentApi->getDocumentName($tenant->getId(), "Signed Lease");

            $allDocsUploaded = $signedLeaseDocument["result_code"] == 0 && $leaseDocument["result_code"] == 0 && $popDocument["result_code"] == 0;


            if($applicationGuid !== null){
                if ($allDocsUploaded) {
                    $application = $this->em->getRepository(Application::class)->findOneBy(array('uid' => $applicationGuid));
                    if($application !== null){
                        $application->setStatus("lease_uploaded");
                        $this->em->persist($application);
                        $this->em->flush($application);
                    }else{
                        return array(
                            'result_message' => "Error. Failed to update application status",
                            'result_code' => 1
                        );
                    }

                }
            }

            return array(
                'result_message' => "Successfully uploaded file",
                'result_code' => 0,
                'alldocs_uploaded' => $allDocsUploaded
            );
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }


    public function addInspectionImage($guid, $fileName): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $inspection = $this->em->getRepository(Inspection::class)->findOneBy(array('guid' => $guid));
            if ($inspection == null) {
                return array(
                    'result_message' => "Failed to upload image. Inspection not found",
                    'result_code' => 1
                );
            }

            $inspectionImage = new InspectionImage();
            $inspectionImage->setInspection($inspection);
            $inspectionImage->setName($fileName);

            $this->em->persist($inspectionImage);
            $this->em->flush($inspectionImage);
            return array(
                'result_message' => "Successfully uploaded image",
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