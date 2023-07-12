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

class TenantApi extends AbstractController
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

    public function getTenantLease($guid)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $tenant = $this->em->getRepository(Tenant::class)->findOneBy(array('guid' => $guid));
            if ($tenant == null) {
                return array(
                    'result_message' => "Error: Tenant not found",
                    'result_code' => 1
                );
            }

            $lease = $this->em->getRepository(Leases::class)->findOneBy(array('tenant' => $tenant->getId(), 'status' => 'active'));
            if ($lease == null) {
                return array(
                    'result_message' => "Error: Lease not found",
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

    public function getTenant($guid)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $tenant = $this->em->getRepository(Tenant::class)->findOneBy(array('guid' => $guid));
            if ($tenant == null) {
                return array(
                    'result_message' => "Error: Tenant not found",
                    'result_code' => 1
                );
            }

            return $tenant;
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    public function getTenantById($id, $phone)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $tenant = $this->em->getRepository(Tenant::class)->findOneBy(array('idNumber' => $id, 'phone'=>$phone));
            if ($tenant == null) {
                return array(
                    'result_message' => "Error: Tenant not found",
                    'result_code' => 1
                );
            }

            return $tenant;
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    public function getLeaseToSign($id, $phone): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $tenant = $this->em->getRepository(Tenant::class)->findOneBy(array('idNumber' => $id, 'phone'=>$phone));
            if ($tenant == null) {
                return array(
                    'result_message' => "Error: Tenant not found",
                    'result_code' => 1
                );
            }

            $lease = $this->em->getRepository(Leases::class)->findOneBy(array('tenant' => $tenant->getId(), 'status' => 'active'));
            if ($lease == null) {
                return array(
                    'result_message' => "Error: Lease not found",
                    'result_code' => 1
                );
            }

            $leaseFileName = $lease->getUnit()->getProperty()->getLeaseFileName();
            return array(
                'name' => $leaseFileName,
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

    public function createTenant($name, $phone, $email, $idDocType, $idNumber, $salary, $occupation, $adults, $children): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        try {
            //validate name
            if (strlen($name) > 100 || strlen($name) < 3) {
                return array(
                    'result_message' => "Error. Applicant name is invalid",
                    'result_code' => 1
                );
            }

            //validate phone
            if (strlen($phone) !== 10) {
                return array(
                    'result_message' => "Error. Phone number is invalid ",
                    'result_code' => 1
                );
            }

            //validate email
            $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";
            if (!preg_match($pattern, $email)) {
                return array(
                    'result_message' => "Error. Email is invalid2",
                    'result_code' => 1
                );
            }

            //validate id document type
            if (strcmp($idDocType, "South African ID") !== 0 &&  strcmp($idDocType, "Passport") !== 0) {
                return array(
                    'result_message' => "Error. ID document type is not valid",
                    'result_code' => 1
                );
            }

            //validate id doc type
            if(strcmp($idDocType, "South African ID") == 0){
                if (!$this->validateSouthAfricanID($idNumber)) {
                    return array(
                        'result_message' => "Error. ID number is not valid",
                        'result_code' => 1
                    );
                }
            }else{
                if (strlen($idNumber) > 50 || strlen($idNumber) < 3) {
                    return array(
                        'result_message' => "Error. Passport number is invalid",
                        'result_code' => 1
                    );
                }
            }

            //validate salary
            if (strlen($salary) > 10 || !is_numeric($salary) || intval($salary) < 1) {
                return array(
                    'result_message' => "Error. Salary is invalid",
                    'result_code' => 1
                );
            }

            //validate occupation
            if (strlen($occupation) > 100 || strlen($occupation) < 3) {
                return array(
                    'result_message' => "Error. Occupation name is invalid",
                    'result_code' => 1
                );
            }

            //validate child count
            if (strlen($children) > 1 || !is_numeric($children)) {
                return array(
                    'result_message' => "Error. Children field is invalid",
                    'result_code' => 1
                );
            }

            //validate adult count
            if (strlen($adults) > 1 || !is_numeric($adults) || intval($adults) < 1) {
                return array(
                    'result_message' => "Error. Adult field is invalid",
                    'result_code' => 1
                );
            }

            $tenant = new Tenant();
            $tenant->setName($name);
            $tenant->setEmail($email);
            $tenant->setPhone($phone);
            $tenant->setAdults($adults);
            $tenant->setChildren( $children);
            $tenant->setIdNumber($idNumber);
            $tenant->setIdDocumentType($idDocType);
            $tenant->setSalary($salary);
            $tenant->setOccupation($occupation);
            $tenant->setStatus("documents_pending");

            $this->em->persist($tenant);
            $this->em->flush($tenant);

            return array(
                'result_message' => "Successfully created tenant",
                'result_code' => 0,
                'id' => $tenant->getId(),
                'tenant' => $tenant
            );

        } catch (Exception $ex) {
            $this->logger->error("Error " .$ex->getMessage() . $ex->getTraceAsString());
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

}