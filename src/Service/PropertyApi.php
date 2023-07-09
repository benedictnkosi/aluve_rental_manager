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
            $properties = $this->em->getRepository(Propertyusers::class)->findBy(array('user' => $this->getUser()));
            if (sizeof($properties) < 1) {

                return array(
                    'result_message' => "Error. No Properties found",
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


    public function getProperty($propertyGuid)
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $property = $this->em->getRepository(Properties::class)->findOneBy(array('guid' => $propertyGuid));
            if ($property == null) {
                return array(
                    'result_message' => "Error. Property not found",
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

    public function updateProperty($field, $value, $propertyGuid): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $property = $this->em->getRepository(Properties::class)->findOneBy(array('guid' => $propertyGuid));
            if ($property == null) {
                return array(
                    'result_message' => "Error. Property not found",
                    'result_code' => 1
                );
            }

            switch ($field) {
                case "name":
                    if (strlen($value) < 1 || strlen($value) > 100) {
                        return array(
                            'result_message' => "Error. New value length is invalid",
                            'result_code' => 1
                        );
                    }
                    $property->setName($value);
                    break;
                case "address":
                    if (strlen($value) < 1 || strlen($value) > 100) {
                        return array(
                            'result_message' => "Error. New value length is invalid",
                            'result_code' => 1
                        );
                    }
                    $property->setAddress($value);
                    break;
                case "lateFee":
                    if (strlen($value) < 1 || !is_numeric($value) || strlen($value) > 4) {
                        return array(
                            'result_message' => "Error. New value is invalid",
                            'result_code' => 1
                        );
                    }
                    $property->setLateFee($value);
                    break;
                case "rent-due-day":
                    if (strlen($value) < 1 || !is_numeric($value) || intval($value) > 30) {
                        return array(
                            'result_message' => "Error. New value is invalid",
                            'result_code' => 1
                        );
                    }
                    $property->setRentDue($value);
                    break;
                case "late-fee-day":
                    if (strlen($value) < 1 || !is_numeric($value) || intval($value) > 30) {
                        return array(
                            'result_message' => "Error. New length is invalid",
                            'result_code' => 1
                        );
                    }
                    $property->setRentLateDays($value);
                    break;
                case "status":
                    if (strcmp($value, "deleted") !== 0) {
                        return array(
                            'result_message' => "Error. New value is invalid",
                            'result_code' => 1
                        );
                    }
                    $property->setStatus($value);
                    break;
                case "accountNumber":
                    if (strlen($value) < 1 || !is_numeric($value) || strlen($value) > 20) {
                        return array(
                            'result_message' => "Error. New value is invalid",
                            'result_code' => 1
                        );
                    }
                    $property->setAccountNUmber($value);
                    break;
                case "applicationFee":
                    if (strlen($value) < 1 || !is_numeric($value) || strlen($value) > 4) {
                        return array(
                            'result_message' => "Error. New value is invalid",
                            'result_code' => 1
                        );
                    }
                    $property->setApplicationFee($value);
                    break;
                case "depositPercent":
                    if (strlen($value) < 1 || !is_numeric($value) || intval($value) > 300) {
                        return array(
                            'result_message' => "Error. New value is invalid",
                            'result_code' => 1
                        );
                    }
                    $property->setDepositPecent($value);
                    break;
                case "property_lease":
                    if (strlen($value) < 1 || strlen($value) > 100) {
                        return array(
                            'result_message' => "Error. New value length is invalid",
                            'result_code' => 1
                        );
                    }
                    $property->setLeaseFileName($value);
                    break;
                default:
                    return array(
                        'result_message' => "Error. Field not found " . $field,
                        'result_code' => 1
                    );
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

    public function createProperty($name, $address, $propertyGuid): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {

            if (strlen($name) < 1 || strlen($name) > 100) {
                return array(
                    'result_message' => "Error. Name is invalid",
                    'result_code' => 1
                );
            }

            if (strlen($address) < 1 || strlen($address) > 100) {
                return array(
                    'result_message' => "Error. Address is invalid",
                    'result_code' => 1
                );
            }

            if (strcmp($propertyGuid, "0") ==0) {
                $property = new Properties();
            } else {
                $property = $this->em->getRepository(Properties::class)->findOneBy(array('guid' => $propertyGuid));
                if ($property == null) {
                    return array(
                        'result_message' => "Error. Property not found",
                        'result_code' => 1
                    );
                }
            }

            $property->setName($name);
            $property->setAddress($address);
            $guid = $this->generateGuid();
            $property->setGuid($guid);
            $this->em->persist($property);
            $this->em->flush($property);

            if ($propertyGuid == 0) {
                $user = $this->em->getRepository(User::class)->findOneBy(array('email' => $this->getUser()->getUserIdentifier()));

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
            } else {
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

    function generateGuid(): string
    {
        if (function_exists('com_create_guid') === true) {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }


}