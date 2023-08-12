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
                        'result_message' => "Unit not found",
                        'result_code' => 1
                    );
                }
            }

            $property = $this->em->getRepository(Properties::class)->findOneBy(array('guid' => $propertyGuid));
            if ($property == null) {
                return array(
                    'result_message' => "Property not found",
                    'result_code' => 1
                );
            }

            if (strlen($unitGuid)!== 36 && strlen($unitGuid) !== 1) {
                return array(
                    'result_message' => "Unit is invalid",
                    'result_code' => 1
                );
            }

            if (strlen($propertyGuid)!== 36) {
                return array(
                    'result_message' => "Property Guid is invalid",
                    'result_code' => 1
                );
            }

            if (strlen($summary)<1 || strlen($summary)> 100) {
                return array(
                    'result_message' => "Summary length is invalid",
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

    #[ArrayShape(['result_message' => "string", 'result_code' => "int"])]
    public function logMaintenanceByTenant($summary): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $emailAddress = $this->getUser()->getUserIdentifier();
            $tenant = $this->em->getRepository(Tenant::class)->findOneBy(array('email' => $emailAddress));
            if ($tenant == null) {
                return array(
                );
            }

            $lease = $this->em->getRepository(Leases::class)->findOneBy(array('tenant' => $tenant->getId(), 'status' => 'active'));
            if ($lease == null) {
                return array(
                    'result_message' => "Lease not found",
                    'result_code' => 1
                );
            }



            if (strlen($summary)<1 || strlen($summary)> 100) {
                return array(
                    'result_message' => "Summary length is invalid",
                    'result_code' => 1
                );
            }

            $maintenance = new Maintenance();

            $maintenance->setProperty($lease->getUnit()->getProperty()->getId());
            $maintenance->setSummary($summary);
            $maintenance->setStatus("new");
            $maintenance->setUid($this->generateGuid());
            $maintenance->setDateLogged(new DateTime());
            $maintenance->setLastUpdated(new DateTime());
            $maintenance->setUnit($lease->getUnit()->getId());
            $this->em->persist($maintenance);
            $this->em->flush($maintenance);

            //send email
            $message = "New maintenance call created for ".$lease->getUnit()->getProperty()->getName(). " - " . $lease->getUnit()->getName() ;
            $subject = "Aluve App - New Maintenance Call";

            $link =  "https://" . $_SERVER['HTTP_HOST'] . "/landlord";
            $linkText = "View Maintenance";
            $template = "generic";
            $communicationApi = new CommunicationApi($this->em, $this->logger);
            $communicationApi->sendEmail($lease->getUnit()->getProperty()->getEmail(), $lease->getUnit()->getProperty()->getName(),$subject , $message, $link, $linkText, $template);

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

    public function getMaintenanceCallsByTenant(): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $emailAddress = $this->getUser()->getUserIdentifier();
            $tenant = $this->em->getRepository(Tenant::class)->findOneBy(array('email' => $emailAddress));
            if ($tenant == null) {
                return array(
                );
            }

            $lease = $this->em->getRepository(Leases::class)->findOneBy(array('tenant' => $tenant->getId(), 'status' => 'active'));
            if ($lease == null) {
                return array(
                    'result_message' => "Lease not found",
                    'result_code' => 1
                );
            }

            $maintenanceCalls = $this->em->getRepository(Maintenance::class)->findBy(array('unit' => $lease->getUnit()->getID()), array('dateLogged' => 'DESC'));

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

            $maintenanceCalls = $this->em->getRepository(Maintenance::class)->findBy(array('property' => $property->getID()), array('dateLogged' => 'DESC'));

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
                    "guid" => $maintenanceCall->getUid(),
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


    #[ArrayShape(['result_message' => "string", 'result_code' => "int"])]
    public function closeMaintenanceCall($guid): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $maintenanceCall = $this->em->getRepository(Maintenance::class)->findOneBy(array('uid' => $guid));

            if ($maintenanceCall == null) {
                return array(
                    'result_message' => "Maintenance call not found",
                    'result_code' => 1
                );
            }

            $maintenanceCall->setStatus("closed");
            $this->em->persist($maintenanceCall);
            $this->em->flush($maintenanceCall);

            //send email
            $lease = $this->em->getRepository(Leases::class)->findOneBy(array('unit' => $maintenanceCall->getUnit()->getId()));
            if($lease == null){
                return array(
                    'result_message' => "Successfully closed maintenance call",
                    'result_code' => 0
                );
            }
            $message = "One of your maintenance calls has been closed. Please login to your tenant portal to view more details";
            $subject = "Aluve App - Maintenance Call Closed";

            $link =  "https://" . $_SERVER['HTTP_HOST'] . "/tenant";
            $linkText = "View Maintenance";
            $template = "generic";
            $communicationApi = new CommunicationApi($this->em, $this->logger);
            $communicationApi->sendEmail($lease->getTenant()->getEmail(), $lease->getTenant()->getName(),$subject , $message, $link, $linkText, $template);

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