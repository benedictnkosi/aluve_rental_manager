<?php

namespace App\Service;

use App\Entity\Leases;
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
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TransactionApi extends AbstractController
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
    public function addTransaction($leaseId, $amount, $description, $transactionDate): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $lease = $this->em->getRepository(Leases::class)->findOneBy(array('idleases' => $leaseId));
            if ($lease == null) {
                return array(
                    'result_message' => "Lease not found",
                    'result_code' => 1
                );
            }

            $transaction = new Transaction();
            $transaction->setDate(new DateTime($transactionDate));
            $transaction->setAmount(intval($amount));
            $transaction->setDescription($description);
            $transaction->setLease($lease);

            $this->em->persist($transaction);
            $this->em->flush($transaction);

            return array(
                'result_message' => "Successfully added transaction",
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


    public function getTransactions($guid): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $lease = $this->em->getRepository(Leases::class)->findOneBy(array('guid' => $guid));

            $transactions = $this->em->getRepository(Transaction::class)->findBy(array('lease' => $lease), array('date' => 'ASC'));
            if (sizeof($transactions) < 1) {
                return array(
                    'result_message' => "0",
                    'result_code' => 1
                );
            }
            $balance = 0;
            foreach ($transactions as $transaction) {
                $balance = $balance + intval($transaction->getAmount());
                $responseArray[] =  array(
                    'description' => $transaction->getDescription(),
                    'date' => $transaction->getDate()->format("Y-m-d"),
                    'amount' => "R" . number_format( $transaction->getAmount(), 2, '. ', '' ),
                    'balance' => "R" .  number_format( $balance, 2, '. ', '' )
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
    public function getBalanceDue($leaseId): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            $transactions = $this->em->getRepository(Transaction::class)->findBy(array('lease' => $leaseId));
            if (sizeof($transactions) < 1) {
                return array(
                    'result_message' => "0",
                    'result_code' => 1
                );
            }

            $total = 0;

            foreach ($transactions as $transaction) {
                $total = $total + intval($transaction->getAmount());
            }

            return array(
                'result_message' => $total,
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