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

class LeaseApi extends AbstractController
{

    private $em;
    private $logger;
    private $transactionApi;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
        $this->transactionApi = new TransactionApi($entityManager, $logger);
        if (session_id() === '') {
            $logger->info("Session id is empty" . __METHOD__);
            session_start();
        }
    }

    public function getLeases($propertyId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {

            //validate property id
            if (strlen($propertyId) < 1 || !is_numeric($propertyId) || intval($propertyId) < 1) {
                return array(
                    'result_message' => "Property ID is not valid",
                    'result_code' => 1
                );
            }

            $leases = $this->em->getRepository("App\Entity\Leases")->createQueryBuilder('l')
                ->where('l.property = :property')
                ->andWhere("l.status = 'active' or l.status = 'pending_docs'")
                ->setParameter('property', $propertyId)
                ->getQuery()
                ->getResult();
            if (sizeof($leases) < 1) {
                return array(
                    'result_message' => "No leases found",
                    'result_code' => 1
                );
            }

            foreach ($leases as $lease) {
                $tenant = $lease->getTenant();
                $due = $this->transactionApi->getBalanceDue($lease->getId());

                $inspection = $this->em->getRepository(Inspection::class)->findBy(array('lease' => $lease, 'status' => "active"));
                $inspectionExist = false;
                if (sizeof($inspection) > 0) {
                    $inspectionExist = true;
                }

                $responseArray[] = array(
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
                    'deposit' => "R" . number_format($lease->getDeposit(), 2, '.', ''),
                    'due' => "R" . number_format(intval($due["result_message"]), 2, '.', ''),
                    'guid' => $lease->getGuid(),
                    'status' => $lease->getStatus(),
                    'inspection_exist' => $inspectionExist,
                    'payment_rules' => $lease->getPaymentRules()
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
        $responseArray = array();
        try {
            //validate property id
            if (strlen($guid) < 1 || strlen($guid) > 36 ) {
                return array(
                    'result_message' => "Property ID is not valid",
                    'result_code' => 1
                );
            }

            $lease = $this->em->getRepository(Leases::class)->findOneBy(array('guid' => $guid));
            if ($lease == null) {
                return array(
                    'result_message' => "Lease not found",
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
                'deposit' => number_format($lease->getDeposit(), 2, '.', ''),
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

    #[ArrayShape(['result_message' => "string", 'result_code' => "int"])]
    public function createLease($tenant, $unitId, $startDate, $endDate, $deposit, $leaseId, $paymentRules, $status = "active"): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $successMessage = "Successfully created lease";

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
                $unit = $this->em->getRepository(Units::class)->findOneBy(array('id' => $unitId));
                if ($unit == null) {
                    return array(
                        'result_message' => "Error: Unit not found",
                        'result_code' => 1
                    );
                }
            } else {
                $lease = $this->em->getRepository(Leases::class)->findOneBy(array('id' => $leaseId));
                if ($lease == null) {
                    return array(
                        'result_message' => "Lease not found",
                        'result_code' => 1
                    );
                }

                $unit = $lease->getUnit();
                $successMessage = "Successfully updated lease";
            }

            $this->em->persist($tenant);
            $this->em->flush($tenant);

            $lease->setProperty($unit->getProperty());
            $lease->setDeposit(intval($deposit));
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


    #[ArrayShape(['result_message' => "string", 'result_code' => "int"])]
    public function createInspection($leaseId, $json, $status): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $lease = $this->em->getRepository(Leases::class)->findOneBy(array('id' => $leaseId));
            if ($lease == null) {
                return array(
                    'result_message' => "Lease not found",
                    'result_code' => 1
                );
            }

            $inspection = new Inspection();
            $inspection->setDate(new DateTime());
            $inspection->setLease($lease);
            $inspection->setJson($json);
            $inspection->setStatus($status);

            $this->em->persist($inspection);
            $this->em->flush($inspection);

            return array(
                'result_message' => "Successfully created inspection",
                'result_code' => 0,
                'id' => $inspection->getId()
            );

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
                    'result_message' => "Lease not found",
                    'result_code' => 1
                );
            }
            $inspection = $this->em->getRepository(Inspection::class)->findBy(array('lease' => $lease, 'status' => "active"), array('date' => 'DESC'));


            if (sizeof($inspection) < 1) {
                return array(
                    'result_message' => "Inspection not found",
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
                    'result_message' => "Lease not found",
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
                $due = intval($this->transactionApi->getBalanceDue($lease->getId())["result_message"]);
                $todayDay = $now->format("d");
                $rentLateBy = $lease->getUnit()->getProperty()->getRentLateDays();

                if (intval($lateFee) > 0
                    && $due > 0
                    && strcmp($todayDay, $rentLateBy) == 0) {
                    $this->transactionApi->addTransaction($lease->getId(), $lateFee, "Late Rent Payment Fee", $now->format("Y-m-d"));
                }
            }

            return array(
                'result_message' => "Successfully added all late fees",
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


    public function addLeaseDoc($leaseGuid, $documentType, $fileName): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $documentApi = new DocumentApi($this->em,$this->logger);
            $lease = $this->em->getRepository(Leases::class)->findOneBy(array('guid' => $leaseGuid));
            if ($lease == null) {
                return array(
                    'result_message' => "Failed to upload application_documents. Lease not found",
                    'result_code' => 1
                );
            }
            if (strcmp($documentType, "lease") == 0) {
                $lease->setLeaseAggreement($fileName);
            } else if (strcmp($documentType, "id") == 0) {
                $documentApi->addDocument($lease->getTenant()->getId(), "ID Document", $fileName);
            } else if (strcmp($documentType, "pop") == 0) {
                $documentApi->addDocument($lease->getTenant()->getId(), "Proof OF Payment", $fileName);
            } else {
                return array(
                    'result_message' => "Document type not suppoerted",
                    'result_code' => 1
                );
            }

            $leaseDocument = $documentApi->getDocumentName($lease->getTenant()->getId(), "ID Document");
            $popDocument = $documentApi->getDocumentName($lease->getTenant()->getId(), "Proof OF Payment");
            $allDocsUploaded = $lease->getLeaseAggreement() !== null && $leaseDocument["result_code"] == 0 && $popDocument["result_code"] == 0;

            if ($allDocsUploaded) {
                $lease->setStatus("docs_uploaded");
            }

            $this->em->persist($lease);
            $this->em->flush($lease);
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


    public function addInspectionImage($inspectionId, $fileName): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $inspection = $this->em->getRepository(Inspection::class)->findOneBy(array('id' => $inspectionId));
            if ($inspection == null) {
                return array(
                    'result_message' => "Failed to upload application_documents. Inspection not found",
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