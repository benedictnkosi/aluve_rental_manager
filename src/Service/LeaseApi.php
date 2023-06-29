<?php

namespace App\Service;

use App\Entity\Leases;
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
            $leases = $this->em->getRepository(Leases::class)->findBy(array('property' => $propertyId, 'status' => 'active'));
            if (sizeof($leases) < 1) {
                return array(
                    'result_message' => "No leases found",
                    'result_code' => 1
                );
            }

            foreach ($leases as $lease) {
                $tenant = $lease->getTenant();
                $due = $this->transactionApi->getBalanceDue($lease->getIdleases());
                $responseArray[] = array(
                    'unit_name' => $lease->getUnit()->getName(),
                    'unit_id' => $lease->getUnit()->getIdunits(),
                    'tenant_name' => $tenant->getName(),
                    'phone_number' => $tenant->getPhone(),
                    'email' => $tenant->getEmail(),
                    'tenant_id' => $tenant->getIdtenant(),
                    'lease_start' => $lease->getStart()->format("Y-M-d"),
                    'lease_end' => $lease->getEnd()->format("Y-M-d"),
                    'lease_id' => $lease->getIdleases(),
                    'deposit' => "R". number_format($lease->getDeposit(), 2, '.', ''),
                    'due' => "R". number_format(intval($due["result_message"]), 2, '.', ''),
                    'guid' => $lease->getGuid()
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
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    public function getLease($guid): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $lease = $this->em->getRepository(Leases::class)->findOneBy(array('guid' => $guid));
            if ($lease == null) {
                return array(
                    'result_message' => "Lease not found",
                    'result_code' => 1
                );
            }

            $now = New DateTime();
            $tenant = $lease->getTenant();
            $due = $this->transactionApi->getBalanceDue($lease->getIdleases());
            $responseArray = array(
                'unit_name' => $lease->getUnit()->getName(),
                'unit_id' => $lease->getUnit()->getIdunits(),
                'tenant_name' => $tenant->getName(),
                'phone_number' => $tenant->getPhone(),
                'email' => $tenant->getEmail(),
                'tenant_id' => $tenant->getIdtenant(),
                'lease_start' => $lease->getStart()->format("Y-M-d"),
                'lease_end' => $lease->getEnd()->format("Y-M-d"),
                'lease_id' => $lease->getIdleases(),
                'deposit' => number_format($lease->getDeposit(), 2, '.', ''),
                'due' => number_format( intval($due["result_message"]), 2, '.', '' ),
                'statement_date' => $now->format("Y-M-d"),
                'property' => $lease->getUnit()->getProperty()->getName() . ", ". $lease->getUnit()->getProperty()->getAddress()
            );

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
    public function createLease($tenantName, $phone, $email, $unitId, $startDate, $endDate, $deposit, $leaseId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $successMessage = "Successfully created lease";

            if ($leaseId == 0) {
                $lease = new Leases();
                $tenant = new Tenant();
                $unit = $this->em->getRepository(Units::class)->findOneBy(array('idunits' => $unitId));
                if ($unit == null) {
                    return array(
                        'result_message' => "Unit not found",
                        'result_code' => 1
                    );
                }
            } else {
                $lease = $this->em->getRepository(Leases::class)->findOneBy(array('idleases' => $leaseId));
                if ($lease == null) {
                    return array(
                        'result_message' => "Lease not found",
                        'result_code' => 1
                    );
                }

                $tenant = $lease->getTenant();
                $unit = $lease->getUnit();
                $successMessage = "Successfully updated lease";
            }

            $tenant->setName($tenantName);
            $tenant->setEmail($email);
            $tenant->setPhone($phone);
            $this->em->persist($tenant);
            $this->em->flush($tenant);

            $lease->setProperty($unit->getProperty());
            $lease->setDeposit(intval($deposit));
            $lease->setStart(new DateTime($startDate));
            $lease->setEnd(new DateTime($endDate));
            $lease->setTenant($tenant);
            $lease->setUnit($unit);
            $lease->setGuid($this->generateGuid());
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

    public function updateLease($field, $value, $id): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $lease = $this->em->getRepository(Leases::class)->findOneBy(array('idleases' => $id));
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
                $due = intval($this->transactionApi->getBalanceDue($lease->getIdleases())["result_message"]);
                $todayDay = $now->format("d");
                $rentLateBy = $lease->getUnit()->getProperty()->getRentLateDays();

                if(intval($lateFee) > 0
                    && $due > 0
                    && strcmp($todayDay, $rentLateBy) == 0 ){
                    $this->transactionApi->addTransaction($lease->getIdleases(),$lateFee,"Late Rent Payment Fee",$now->format("Y-m-d"));
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
}