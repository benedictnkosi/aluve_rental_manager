<?php

namespace App\Service;

use App\Entity\Leases;
use App\Entity\Properties;
use App\Entity\Propertyusers;
use App\Entity\Units;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\ArrayShape;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AuthApi  extends AbstractController
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

    #[ArrayShape(['result_message' => "string", 'result_code' => "int"])]
    public function isAuthorisedToChangeUnit($unitId): array
    {
        $unit = $this->em->getRepository(Units::class)->findOneBy(array('id' => $unitId));
        if($unit == null){
            return array(
                'result_message' => "Error. Unit not found",
                'result_code' => 1
            );
        }

        $user = $this->em->getRepository(User::class)->findOneBy(array('email' => $_SESSION["username"]));

        //get user properties matching unit property
        $propertyUser = $this->em->getRepository(Propertyusers::class)->findOneBy(array('user' => $user->getIdusers(), 'property' => $unit->getProperty()->getId()));
        if($propertyUser == null){
            return array(
                'result_message' => "Error. You are not authorised to update this entity",
                'result_code' => 1
            );
        }else{
            return array(
                'result_message' => "Allowed",
                'result_code' => 0
            );
        }
    }

    #[ArrayShape(['result_message' => "string", 'result_code' => "int"])]
    public function isAuthorisedToChangeLease($leaseGuid): array
    {
        //get lease
        $lease = $this->em->getRepository(Leases::class)->findOneBy(array('guid' => $leaseGuid));
        if($lease == null){
            return array(
                'result_message' => "Error. Lease not found",
                'result_code' => 1
            );
        }

        return $this->isAuthorisedToChangeUnit($lease->getUnit()->getId());
    }

}