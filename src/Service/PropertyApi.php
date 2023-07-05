<?php

namespace App\Service;

use App\Entity\Properties;
use App\Entity\Propertyusers;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PropertyApi extends AbstractController
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

    public function getProperties(): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $properties = $this->em->getRepository(Propertyusers::class)->findBy(array('user' =>  $this->getUser()));
            if (sizeof($properties) < 1) {

                return array(
                    'result_message' => "No Properties found",
                    'result_code' => 1
                );
            }
            return $properties;
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }


    public function getProperty($id)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $property = $this->em->getRepository(Properties::class)->findOneBy(array('idproperties' =>  $id));
            if ($property == null) {
                return array(
                    'result_message' => "Property not found",
                    'result_code' => 1
                );
            }
            return $property;
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    public function updateProperty($field, $value, $id): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $property = $this->em->getRepository(Properties::class)->findOneBy(array('idproperties' =>  $id));
            if($property == null){
                return array(
                    'result_message' => "Property not found",
                    'result_code' => 1
                );
            }

            switch ($field) {
                case "name":
                    $property->setName($value);
                    break;
                case "address":
                    $property->setAddress($value);
                    break;
                case "lateFee":
                    $property->setLateFee($value);
                    break;
                case "quickbooksToken":
                    $property->setQuickbooksToken($value);
                    break;
                case "rentDue":
                    $property->setRentDue($value);
                    break;
                case "rentLateDays":
                    $property->setRentLateDays($value);
                    break;
                case "status":
                    $property->setStatus($value);
                    break;
                case "accountNumber":
                    $property->setAccountNUmber($value);
                    break;
                case "applicaitonFee":
                    $property->setApplicationFee($value);
                    break;
                case "depositPercent":
                    $property->setDepositPecent($value);
                    break;
                default:
            }

            $this->em->persist($property);
            $this->em->flush($property);

            return array(
                'result_message' => "Successfully updated property",
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

    public function createProperty($name, $address, $propertyId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            if($propertyId == 0){
                $property =  new Properties();
            }else{
                $property = $this->em->getRepository(Properties::class)->findOneBy(array('idproperties' =>  $propertyId));
            }

            $property->setName($name);
            $property->setAddress($address);
            $this->em->persist($property);
            $this->em->flush($property);

            if($propertyId == 0){
                $user = $this->em->getRepository(User::class)->findOneBy(array('email' =>  $this->getUser()->getUserIdentifier()));

                //link property with current user
                $propertyusers = new Propertyusers();
                $propertyusers->setProperty($property);
                $propertyusers->setUser($user);
                $this->em->persist($propertyusers);
                $this->em->flush($propertyusers);

                return array(
                    'result_message' => "Successfully created property",
                    'result_code' => 0
                );
            }else{
                return array(
                    'result_message' => "Successfully updated property",
                    'result_code' => 0
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