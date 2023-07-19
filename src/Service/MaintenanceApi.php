<?php

namespace App\Service;

use App\Entity\Leases;
use App\Entity\Maintenance;
use App\Entity\Properties;
use App\Entity\Propertyusers;
use App\Entity\Tenant;
use App\Entity\Transaction;
use App\Entity\Units;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use PhpImap\Exceptions\ConnectionException;
use Psr\Log\LoggerInterface;
use SecIT\ImapBundle\Service\Imap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceApi extends AbstractController
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

    #[ArrayShape(['result_message' => "string", 'result_code' => "int"])]
    public function logMaintenance($summary, $unitGuid, $propertyGuid): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        $this->logger->debug("unit guid is: " . $unitGuid);
        try {
            $unit = null;
            if(strcmp($unitGuid, "0") !== 0){
                $unit = $this->em->getRepository(Units::class)->findOneBy(array('guid' => $unitGuid));
                if ($unit == null) {
                    return array(
                        'result_message' => "Error. Unit not found",
                        'result_code' => 1
                    );
                }
            }

            $property = $this->em->getRepository(Properties::class)->findOneBy(array('guid' => $propertyGuid));
            if ($property == null) {
                return array(
                    'result_message' => "Error. Property not found",
                    'result_code' => 1
                );
            }

            if (strlen($unitGuid)!== 36 && strlen($unitGuid) !== 1) {
                return array(
                    'result_message' => "Error. Unit is invalid",
                    'result_code' => 1
                );
            }

            if (strlen($propertyGuid)!== 36) {
                return array(
                    'result_message' => "Error. Property Guid is invalid",
                    'result_code' => 1
                );
            }

            if (strlen($summary)<1 || strlen($summary)> 100) {
                return array(
                    'result_message' => "Error. Summary length is invalid",
                    'result_code' => 1
                );
            }

            $maintenance = new Maintenance();
            if($unit !== null){
                $maintenance->setUnit($unit->getId());
            }

            $maintenance->setProperty($property->getId());
            $maintenance->setSummary($summary);
            $maintenance->setStatus("new");
            $maintenance->setUid($this->generateGuid());
            $maintenance->setDateLogged(new DateTime());
            $maintenance->setLastUpdated(new DateTime());

            $this->em->persist($maintenance);
            $this->em->flush($maintenance);

            return array(
                'result_message' => "Successfully logged maintenance call",
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

    function generateGuid(): string
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    public function getMaintenanceCallsByIDNumber($idNumber, $phoneNumber): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $tenant = $this->em->getRepository(Tenant::class)->findOneBy(array('idNumber' => $idNumber));
            if($tenant == null){
                return array(
                    'result_message' => "Tenant not found for ID number",
                    'result_code' => 1
                );
            }

            if(strcmp($tenant->getPhone(), $phoneNumber) !== 0){
                return array(
                    'result_message' => "Error. Tenant authentication failed",
                    'result_code' => 1
                );
            }

            $lease = $this->em->getRepository(Leases::class)->findOneBy(array('tenant' => $tenant->getId()));
            if($lease == null){
                return array(
                    'result_message' => "Lease not found for ID number",
                    'result_code' => 1
                );
            }

            $maintenanceCalls = $this->em->getRepository(Maintenance::class)->findBy(array('unit' => $lease->getUnit()->getID()));

            if (sizeof($maintenanceCalls) < 1) {
                return array(
                    'result_message' => "No Maintenance calls found",
                    'result_code' => 1
                );
            }

            return $maintenanceCalls;
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    public function getMaintenanceCalls($propertyGuid): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $property = $this->em->getRepository(Properties::class)->findOneBy(array('guid' => $propertyGuid));
            if($property == null){
                return array(
                    'result_message' => "Property not found",
                    'result_code' => 1
                );
            }

            $maintenanceCalls = $this->em->getRepository(Maintenance::class)->findBy(array('property' => $property->getID()), array('status' => 'DESC'));

            if (sizeof($maintenanceCalls) < 1) {
                return array(
                    'result_message' => "No Maintenance calls found",
                    'result_code' => 1
                );
            }

            foreach ($maintenanceCalls as $maintenanceCall){

                $lease = $this->em->getRepository(Leases::class)->findOneBy(array('unit' => $maintenanceCall->getUnit(), 'status' => 'active'));
                $unit  = $this->em->getRepository(Units::class)->findOneBy(array('id' => $maintenanceCall->getUnit()));
                $tenantName = "";
                $tenantPhone = "";
                $unitName = "";
                if($lease !== null){
                    $tenantName = $lease->getTenant()->getName();
                    $tenantPhone = $lease->getTenant()->getPhone();
                }

                if($unit !== null){
                    $unitName = $unit->getName();
                }

                $responseArray[] = array(
                    "id" => $maintenanceCall->getId(),
                    "summary" => $maintenanceCall->getSummary(),
                    "status" => $maintenanceCall->getStatus(),
                    "date" => $maintenanceCall->getDateLogged()->format("Y-m-d"),
                    "unit" => $unitName,
                    "tenant" => $tenantName,
                    "phone_number" => $tenantPhone
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

    #[ArrayShape(['result_message' => "string", 'result_code' => "int"])] public function
    logMaintenanceCallByIDNumber($idNumber, $phoneNumber, $summary): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {

            if(strlen($summary) < 1 || strlen($summary) > 100){
                return array(
                    'result_message' => "Error. Summary is not valid",
                    'result_code' => 1
                );
            }
            $tenant = $this->em->getRepository(Tenant::class)->findOneBy(array('idNumber' => $idNumber));
            if($tenant == null){
                return array(
                    'result_message' => "Error. Tenant not found for ID number",
                    'result_code' => 1
                );
            }

            if(strcmp($tenant->getPhone(), $phoneNumber) !== 0){
                return array(
                    'result_message' => "Error. Tenant authentication failed",
                    'result_code' => 1
                );
            }

            $lease = $this->em->getRepository(Leases::class)->findOneBy(array('tenant' => $tenant->getId()));
            if($lease == null){
                return array(
                    'result_message' => "Error. Lease not found for ID number",
                    'result_code' => 1
                );
            }

            return $this->logMaintenance($summary,$lease->getUnit()->getGuid(), $lease->getUnit()->getProperty()->getGuid());
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    public function getMaintenanceCallByGuid($guid): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $maintenanceCall = $this->em->getRepository(Maintenance::class)->findOneBy(array('guid' => $guid));

            if ($maintenanceCall == null) {
                return array(
                    'result_message' => "Maintenance call not found",
                    'result_code' => 1
                );
            }

            return $maintenanceCall;
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    #[ArrayShape(['result_message' => "string", 'result_code' => "int"])]
    public function closeMaintenanceCall($id): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $maintenanceCall = $this->em->getRepository(Maintenance::class)->findOneBy(array('id' => $id));

            if ($maintenanceCall == null) {
                return array(
                    'result_message' => "Maintenance call not found",
                    'result_code' => 1
                );
            }

            $maintenanceCall->setStatus("closed");
            $this->em->persist($maintenanceCall);
            $this->em->flush($maintenanceCall);

            return array(
                'result_message' => "Successfully closed maintenance call",
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
    public function updateMaintenanceCallStatus($guid, $status): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {

            if(strcmp($status, "resolved") !== 0){
                return array(
                    'result_message' => "Maintenance status is invalid",
                    'result_code' => 1
                );
            }

            $maintenanceCall = $this->em->getRepository(Maintenance::class)->findOneBy(array('guid' => $guid));

            if ($maintenanceCall == null) {
                return array(
                    'result_message' => "Maintenance call not found",
                    'result_code' => 1
                );
            }

            $maintenanceCall->setStatus($status);
            $this->em->persist($maintenanceCall);
            $this->em->flush($maintenanceCall);

            return array(
                'result_message' => "Successfully updated maintenance status",
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