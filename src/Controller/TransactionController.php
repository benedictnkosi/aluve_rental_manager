<?php

namespace App\Controller;

use App\Service\LeaseApi;
use App\Service\PropertyApi;
use App\Service\TransactionApi;
use App\Service\UnitApi;
use JMS\Serializer\SerializerBuilder;
use PhpImap\Exceptions\ConnectionException;
use Psr\Log\LoggerInterface;
use SecIT\ImapBundle\Service\Imap;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TransactionController extends AbstractController
{
    /**
     * @Route("api/transaction/payment")
     */
    public function addPayment(Request $request, LoggerInterface $logger, TransactionApi $transactionApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('POST')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $transactionApi->addTransaction($request->get("lease_id"), intval($request->get("amount")) * -1, "Thank you for payment", $request->get("payment_date"));
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/lease/balance/{leaseId}")
     */
    public function getLeaseBalance($leaseId, Request $request, LoggerInterface $logger, TransactionApi $transactionApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $transactionApi->getBalanceDue($leaseId);
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("public/lease/transactions/{guid}")
     */
    public function getLeaseTransactions($guid, Request $request, LoggerInterface $logger, TransactionApi $transactionApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $transactionApi->getTransactions($guid);
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/transactions/import")
     * @throws ConnectionException
     */
    public function importTransactions(TransactionApi $transactionApi, LoggerInterface $logger, Imap $imap): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $response  = $transactionApi->importTransactions($imap);
        return new JsonResponse($response, 200, array());
    }
}