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

    public function getUnits($propertyId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $units = $this->em->getRepository(Units::class)->findBy(array('property' => $propertyId, 'status' => 'active'));
            if (sizeof($units) < 1) {
                return array(
                    'result_message' => "No units found",
                    'result_code' => 1
                );
            }

            foreach ($units as $unit) {
                $lease = $this->em->getRepository(Leases::class)->findOneBy(array('unit' => $unit->getIdunits(), 'status' => "active"));
                if ($lease == null) {
                    $responseArray[] = array(
                        'unit_name' => $unit->getName(),
                        'unit_id' => $unit->getIdunits(),
                        'listed' => $unit->getListed(),
                        'min_gross_salary' => "R". number_format($unit->getMinGrossSalary(), 2, '.', ''),
                        'max_occupants' => $unit->getMaxOccupants(),
                        'parking' => $unit->getParking(),
                        'children' => $unit->getChildrenAllowed(),
                        'rent' => "R". number_format($unit->getRent(), 2, '.', ''),
                        'bedrooms' => $unit->getBedrooms(),
                        'bathrooms' => $unit->getbathrooms(),

                    );
                } else {
                    $tenant = $lease->getTenant();
                    $responseArray[] = array(
                        'unit_name' => $unit->getName(),
                        'unit_id' => $unit->getIdunits(),
                        'tenant_name' => $tenant->getName(),
                        'phone_number' => $tenant->getPhone(),
                        'email' => $tenant->getEmail(),
                        'tenant_id' => $tenant->getIdtenant(),
                        'lease_start' => $lease->getStart()->format("Y-m-d"),
                        'lease_end' => $lease->getEnd()->format("Y-m-d"),
                        'lease_id' => $lease->getIdleases(),
                        'deposit' => number_format($lease->getDeposit(), 2, '.', ''),
                        'listed' => $unit->getListed(),
                        'min_gross_salary' => "R". number_format($unit->getMinGrossSalary(), 2, '.', ''),
                        'max_occupants' => $unit->getMaxOccupants(),
                        'parking' => $unit->getParking(),
                        'children' => $unit->getChildrenAllowed(),
                        'rent' => "R". number_format($unit->getRent(), 2, '.', ''),
                        'bedrooms' => $unit->getBedrooms(),
                        'bathrooms' => $unit->getBathrooms(),
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

    public function getUnit($unitId)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $unit = $this->em->getRepository(Units::class)->findOneBy(array('idunits' => $unitId));
            if ($unit == null) {
                return array(
                    'result_message' => "Error: Unit not found",
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
    public function createUnit($name, $unitId, $listed, $parking, $childrenAllowed, $maxOccupants, $minGrossSalary, $rent, $bedrooms, $bathrooms): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $successMessage = "Successfully created rental unit";
            if ($unitId == 0) {
                $unit = new Units();
            } else {
                $unit = $this->em->getRepository(Units::class)->findOneBy(array('idunits' => $unitId));
                if($unit == null){
                    return array(
                        'result_message' => "Error: Unit not found",
                        'result_code' => 0
                    );
                }
                $successMessage = "Successfully updated rental unit";
            }

            $user = $this->em->getRepository(User::class)->findOneBy(array('email' => $this->getUser()->getUserIdentifier()));
            $propertyUser = $this->em->getRepository(Propertyusers::class)->findOneBy(array('user' => $user));

            $property = $propertyUser->getProperty();

            $this->logger->info("min salary " .$minGrossSalary);
            $unit->setName($name);
            $unit->setProperty($property);
            $unit->setParking(intval($parking));
            $unit->setMinGrossSalary($minGrossSalary);
            $unit->setMaxOccupants($maxOccupants);
            $unit->setChildrenAllowed($childrenAllowed);
            $unit->setRent($rent);
            $unit->setBedrooms($bedrooms);
            $unit->setBathrooms($bathrooms);

            if(strcmp($listed, "true") == 0){
                $unit->setListed(true);
            }else{
                $unit->setListed(false);
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

    public function updateUnit($field, $value, $id): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $unit = $this->em->getRepository(Units::class)->findOneBy(array('idunits' =>  $id));
            if($unit == null){
                return array(
                    'result_message' => "Error: Unit not found",
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