<?php

namespace App\Service;

use App\Entity\Leases;
use App\Entity\Properties;
use App\Entity\Propertyusers;
use App\Entity\Units;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UnitApi extends AbstractController
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

    public function getUnits($propertyGuid): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {

            $Property = $this->em->getRepository(Properties::class)->findOneBy(array('guid' => $propertyGuid, 'status' => 'active'));
            if ($Property == null) {
                return array(
                    'result_message' => "No units found",
                    'result_code' => 1
                );
            }

            $units = $this->em->getRepository(Units::class)->findBy(array('property' => $Property->getId(), 'status' => 'active'));
            if (sizeof($units) < 1) {
                return array(
                    'result_message' => "No units found",
                    'result_code' => 1
                );
            }

            foreach ($units as $unit) {
                $lease = $this->em->getRepository(Leases::class)->findOneBy(array('unit' => $unit->getId(), 'status' => "active"));
                if ($lease == null) {
                    $responseArray[] = array(
                        'unit_name' => $unit->getName(),
                        'unit_id' => $unit->getId(),
                        'listed' => $unit->getListed(),
                        'min_gross_salary' => number_format($unit->getMinGrossSalary(), 2, '.', ''),
                        'max_occupants' => $unit->getMaxOccupants(),
                        'parking' => $unit->getParking(),
                        'children' => $unit->getChildrenAllowed(),
                        'rent' => number_format($unit->getRent(), 2, '.', ''),
                        'bedrooms' => $unit->getBedrooms(),
                        'bathrooms' => $unit->getbathrooms(),
                        'guid' => $unit->getGuid(),
                        'meter' => $unit->getMeter(),
                        'water' => $unit->getWater(),
                        'electricity' => $unit->getElectricity()

                    );
                } else {
                    $tenant = $lease->getTenant();
                    $responseArray[] = array(
                        'unit_name' => $unit->getName(),
                        'unit_id' => $unit->getId(),
                        'tenant_name' => $tenant->getName(),
                        'phone_number' => $tenant->getPhone(),
                        'email' => $tenant->getEmail(),
                        'tenant_id' => $tenant->getId(),
                        'lease_start' => $lease->getStart()->format("Y-m-d"),
                        'lease_end' => $lease->getEnd()->format("Y-m-d"),
                        'lease_id' => $lease->getId(),
                        'listed' => $unit->getListed(),
                        'min_gross_salary' => number_format($unit->getMinGrossSalary(), 2, '.', ''),
                        'max_occupants' => $unit->getMaxOccupants(),
                        'parking' => $unit->getParking(),
                        'children' => $unit->getChildrenAllowed(),
                        'rent' => number_format($unit->getRent(), 2, '.', ''),
                        'bedrooms' => $unit->getBedrooms(),
                        'bathrooms' => $unit->getBathrooms(),
                        'guid' => $unit->getGuid(),
                        'meter' => $unit->getMeter(),
                        'water' => $unit->getWater(),
                        'electricity' => $unit->getElectricity(),
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


    public function getUnitsNames($propertyGuid): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {

            $Property = $this->em->getRepository(Properties::class)->findOneBy(array('guid' => $propertyGuid, 'status' => 'active'));
            if ($Property == null) {
                return array(
                    'result_message' => "No units found",
                    'result_code' => 1
                );
            }

            $units = $this->em->getRepository(Units::class)->findBy(array('property' => $Property->getId(), 'status' => 'active'));
            if (sizeof($units) < 1) {
                return array(
                    'result_message' => "No units found",
                    'result_code' => 1
                );
            }

            foreach ($units as $unit) {
                {
                    $responseArray[] = array(
                        'unit_name' => $unit->getName(),
                        'unit_id' => $unit->getId(),
                        'guid' => $unit->getGuid(),
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

    public function getUnit($guid)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $unit = $this->em->getRepository(Units::class)->findOneBy(array('guid' => $guid));
            if ($unit == null) {
                return array(
                    'result_message' => " Unit not found",
                    'result_code' => 1
                );
            }

            return $unit;
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    #[ArrayShape(['result_message' => "string", 'result_code' => "int"])]
    public function createUnit($name, $guid, $listed, $parking, $childrenAllowed, $maxOccupants, $minGrossSalary, $rent, $bedrooms, $bathrooms, $propertyGuid, $meter, $water, $electricity): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $successMessage = "Successfully created rental unit";

            $property = $this->em->getRepository(Properties::class)->findOneBy(array('guid' =>  $propertyGuid));
            if ($property == null) {
                return array(
                    'result_message' => "Property not found",
                    'result_code' => 1
                );
            }


            if(strcmp($guid, "0") == 0){
                $unit = new Units();
                $unit->setGuid($this->generateGuid());

                //check if not duplicate
                $existingUnit = $this->em->getRepository(Units::class)->findOneBy(array('name' => $name, 'property' => $property->getId()));
                if($existingUnit !== null){
                    return array(
                        'result_message' => " Unit with the same name already exists",
                        'result_code' => 1
                    );
                }

            } else {
                $unit = $this->em->getRepository(Units::class)->findOneBy(array('guid' => $guid));
                if($unit == null){
                    return array(
                        'result_message' => " Unit not found",
                        'result_code' => 1
                    );
                }
                $successMessage = "Successfully updated rental unit";
            }

            $this->logger->info("min salary " .$minGrossSalary);
            $unit->setName($name);
            $unit->setProperty($property);
            $unit->setMinGrossSalary($minGrossSalary);
            $unit->setMaxOccupants($maxOccupants);
            $unit->setRent($rent);
            $unit->setBedrooms($bedrooms);
            $unit->setBathrooms($bathrooms);
            $unit->setMeter($meter);
            $unit->setWater($water);
            $unit->setElectricity($electricity);

            if(strcmp($listed, "true") == 0){
                $unit->setListed(true);
            }else{
                $unit->setListed(false);
            }

            if(strcmp($parking, "true") == 0){
                $unit->setParking(true);
            }else{
                $unit->setParking(false);
            }

            if(strcmp($childrenAllowed, "true") == 0){
                $unit->setChildrenAllowed(true);
            }else{
                $unit->setChildrenAllowed(false);
            }

            $this->em->persist($unit);
            $this->em->flush($unit);


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

    function generateGuid(): string
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    public function updateUnit($field, $value, $guid): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $unit = $this->em->getRepository(Units::class)->findOneBy(array('guid' =>  $guid));
            if($unit == null){
                return array(
                    'result_message' => " Unit not found",
                    'result_code' => 1
                );
            }

            $msg = "Successfully updated unit";

            switch ($field) {
                case "name":
                    $unit->setName($value);
                    break;
                case "status":
                    $unit->setStatus($value);
                    if(strcmp($value, "deleted") == 0){
                        $msg = "Successfully deleted unit";
                    }
                    break;
                case "listed":
                    $unit->setListed($value);
                    break;
                default:
            }

            $this->logger->info("unit status is " . $unit->getStatus());
            $this->em->persist($unit);
            $this->em->flush($unit);

            return array(
                'result_message' => $msg,
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