<?php

namespace App\Controller;

use App\Service\AuthApi;
use App\Service\ExpenseApi;
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

class ExpenseController extends AbstractController
{


    /**
     * @Route("api/expenses/new")
     */
    public function recordExpense(Request $request, LoggerInterface $logger, ExpenseApi $expenseApi, AuthApi $authApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('POST')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $expenseApi->addExpense($request->get("expense_id"), $request->get("property_guid"),  $request->get("amount"), $request->get("description"), $request->get("date"));
        return new JsonResponse($response, 200, array());
    }


    /**
     * @Route("api/expenses/get/{propertyGuid}")
     */
    public function getExpenses($propertyGuid, Request $request, LoggerInterface $logger, ExpenseApi $expenseApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $expenseApi->getExpenses($propertyGuid);
        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($response, 'json');
        return new JsonResponse($jsonContent , 200, array(), true);
    }

    /**
     * @Route("api/expenses/accounts/get")
     */
    public function getExpenseAccounts(Request $request, LoggerInterface $logger, ExpenseApi $expenseApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $expenseApi->getExpenseAccounts();
        $serializer = SerializerBuilder::create()->build();
        $jsonContent = $serializer->serialize($response, 'json');
        return new JsonResponse($jsonContent , 200, array(), true);
    }

    /**
     * @Route("api/expenses/delete")
     */
    public function deleteExpense(Request $request, LoggerInterface $logger,  ExpenseApi $expenseApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('delete')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $expenseApi->deleteExpense($request->get("guid"));
        return new JsonResponse($response , 200, array());
    }

    /**
     * @Route("api/expense/total")
     */
    public function getExpensesTotalForPastDays(Request $request, LoggerInterface $logger,ExpenseApi $expenseApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $expenseApi->getExpensesTotal($request->get("property_id"), $request->get("number_od_days"));
        return new JsonResponse($response, 200, array());
    }


    /**
     * @Route("api/expenses_income/monthly")
     */
    public function getMonthlyExpensesAndIncomeForPastDays(Request $request, LoggerInterface $logger,ExpenseApi $expenseApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $expenseApi->getExpensesIncomeByMonth($request->get("property_id"), $request->get("number_od_days"));
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/expense_income/total")
     */
    public function getIncomeExpensesTotalForPastDays(Request $request, LoggerInterface $logger,ExpenseApi $expenseApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }

        $response = $expenseApi->getExpensesAndIncomeTotal($request->get("property_id"), $request->get("number_od_days"));
        return new JsonResponse($response, 200, array());
    }

    /**
     * @Route("api/expense_by_account/total")
     */
    public function getIncomeExpensesByAccount(Request $request, LoggerInterface $logger,ExpenseApi $expenseApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed", 405, array());
        }
        $response = $expenseApi->getExpensesByAccount($request->get("property_id"), $request->get("number_od_days"));
        return new JsonResponse($response, 200, array());
    }

}