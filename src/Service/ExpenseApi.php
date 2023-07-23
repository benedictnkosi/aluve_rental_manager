<?php

namespace App\Service;

use App\Entity\Expense;
use App\Entity\ExpenseAccount;
use App\Entity\Properties;
use App\Helpers\DatabaseHelper;
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

class ExpenseApi extends AbstractController
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
    public function addExpense($expenseId, $propertyGuid, $amount, $description, $date): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {

            //validate expense id
            if (!is_numeric($expenseId)) {
                return array(
                    'result_message' => "Error. Expense account is invalid",
                    'result_code' => 1
                );
            }

            //validate property id
            if (strlen($propertyGuid) !== 36) {
                return array(
                    'result_message' => "Error. Property GUID is invalid",
                    'result_code' => 1
                );
            }


            //validate amount
            if (strlen($amount) > 6 || !is_numeric($amount) || intval($amount) < 1) {
                return array(
                    'result_message' => "Error. Amount is invalid",
                    'result_code' => 1
                );
            }

            //validate description
            if (strlen($description) > 100 || strlen($description) < 3) {
                return array(
                    'result_message' => "Error. Occupation name is invalid",
                    'result_code' => 1
                );
            }

            //validate date
            if (!DateTime::createFromFormat('Y-m-d', $date)) {
                return array(
                    'result_code' => 1,
                    'result_message' => "Error. Date is not valid",
                );
            }

            $expenseAccount = $this->em->getRepository(ExpenseAccount::class)->findOneBy(array('id' => $expenseId));
            if ($expenseAccount == null) {
                return array(
                    'result_message' => "Error. Expense account not found",
                    'result_code' => 1
                );
            }

            $property = $this->em->getRepository(Properties::class)->findOneBy(array('guid' =>  $propertyGuid));
            if ($property == null) {
                return array(
                    'result_message' => "Error. Property not found",
                    'result_code' => 1
                );
            }

            $expense = new Expense();
            $expense->setExpense($expenseAccount);
            $expense->setProperty($property);
            $expense->setAmount($amount);
            $expense->setDescription($description);
            $expense->setDate(new DateTime($date));
            $expense->setGuid($this->generateGuid());

            $this->em->persist($expense);
            $this->em->flush($expense);

            return array(
                'result_message' => "Successfully recorded expense",
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

    public function getExpenses($propertyGuid): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {

            //validate property id
            if (strlen($propertyGuid) !== 36 ) {
                return array(
                    'result_message' => "Error. Property GUID is invalid",
                    'result_code' => 1
                );
            }

            $property = $this->em->getRepository(Properties::class)->findOneBy(array('guid' =>  $propertyGuid));
            if ($property == null) {
                return array(
                    'result_message' => "Error. Property not found",
                    'result_code' => 1
                );
            }

            return $this->em->getRepository(Expense::class)->findBy(array('property' => $property), array('date' => 'DESC'), 100);
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    public function getExpensesTotal($propertyGuid, $numberOfDays): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {

            //validate property id
            if (strlen($propertyGuid) !== 36 ) {
                return array(
                    'result_message' => "Error. Property GUID is invalid",
                    'result_code' => 1
                );
            }

            //validate number of days
            if (strlen($numberOfDays) < 1 || !is_numeric($numberOfDays) || intval($numberOfDays) < 1) {
                return array(
                    'result_message' => "Error. Number Of days is not valid",
                    'result_code' => 1
                );
            }
            $property = $this->em->getRepository(Properties::class)->findOneBy(array('guid' =>  $propertyGuid));
            if ($property == null) {
                return array(
                    'result_message' => "Error. Property not found",
                    'result_code' => 1
                );
            }

            $sql = "SELECT sum(amount) as total FROM `expense` WHERE property = ".$property->getId()." and `amount` > 0 and `date` > (DATE(NOW()) - INTERVAL ".$numberOfDays." DAY)";
            $databaseHelper = new DatabaseApi($this->logger);
            $result = $databaseHelper->queryDatabase($sql);

            if (!$result) {
                return array(
                    'total' => 0,
                    'result_code' => 0,
                    'result_message' => "success"
                );
            } else {
                $sum = 0;
                while ($results = $result->fetch_assoc()) {
                    $sum += intval($results["total"]);
                }

                return array(
                    'total' => $sum,
                    'result_code' => 0,
                    'result_message' => "success"
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

    public function getExpensesAndIncomeTotal($propertyGuid, $numberOfDays): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            //validate number of days
            if (strlen($numberOfDays) < 1 || !is_numeric($numberOfDays) || intval($numberOfDays) < 1) {
                return array(
                    'result_message' => "Number Of days is not valid",
                    'result_code' => 1
                );
            }

            //validate property id
            if (strlen($propertyGuid) !== 36) {
                return array(
                    'result_message' => "Property GUID is invalid",
                    'result_code' => 1
                );
            }

            $expenseTotal = $this->getExpensesTotal($propertyGuid, $numberOfDays);
            $incomeTotal = $this->getIncomeTotal($propertyGuid, $numberOfDays);
            return array(
                'income' => $incomeTotal,
                'expense' => $expenseTotal,
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


    public function getIncomeTotal($propertyGuid, $numberOfDays): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {

            //validate property id
            if (strlen($propertyGuid) !== 36) {
                return array(
                    'result_message' => "Property GUID is invalid",
                    'result_code' => 1
                );
            }

            //validate property id
            if (strlen($numberOfDays) < 1 || !is_numeric($numberOfDays) || intval($numberOfDays) < 1) {
                return array(
                    'result_message' => "Number Of days is not valid",
                    'result_code' => 1
                );
            }

            $property = $this->em->getRepository(Properties::class)->findOneBy(array('guid' =>  $propertyGuid));
            if ($property == null) {
                return array(
                    'result_message' => "Property not found",
                    'result_code' => 1
                );
            }

            $sql = "SELECT sum(amount) as total FROM `transaction`, leases WHERE lease = leases.id and
leases.property = ".$property->getId()." and `amount` > 0 and `date` > (DATE(NOW()) - INTERVAL ".$numberOfDays." DAY)";
            $databaseHelper = new DatabaseApi($this->logger);
            $result = $databaseHelper->queryDatabase($sql);

            if (!$result) {
                return array(
                    'total' => 0,
                    'result_code' => 0,
                    'result_message' => "success"
                );
            } else {
                $sum = 0;
                while ($results = $result->fetch_assoc()) {
                    $sum += intval($results["total"]);
                }

                return array(
                    'total' => $sum,
                    'result_code' => 0,
                    'result_message' => "success"
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


    public function getExpensesByMonth($propertyGuid, $numberOfDays): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            //validate property id
            //validate property id
            if (strlen($propertyGuid) !== 36 ) {
                return array(
                    'result_message' => "Property GUID is invalid",
                    'result_code' => 1
                );
            }

            //validate number of days
            if (strlen($numberOfDays) < 1 || !is_numeric($numberOfDays) || intval($numberOfDays) < 1) {
                return array(
                    'result_message' => "Number Of days is not valid",
                    'result_code' => 1
                );
            }

            $property = $this->em->getRepository(Properties::class)->findOneBy(array('guid' =>  $propertyGuid));
            if ($property == null) {
                return array(
                    'result_message' => "Property not found",
                    'result_code' => 1
                );
            }

            $sql = "SELECT
    MONTHNAME(`date`) AS `month`,
    MONTH(`date`) AS `month_num`,
    YEAR(`date`) AS `year`,
    SUM(`amount`) AS `total`
FROM
    `expense`
WHERE property = ".$property->getId()." and 
    `date` >= CURDATE() - INTERVAL ".$numberOfDays." DAY
GROUP BY
    `month_num`
ORDER BY
    `month_num`;";

            $databaseHelper = new DatabaseApi($this->logger);
            $result = $databaseHelper->queryDatabase($sql);

            if (!$result) {
                return array(
                );
            } else {
                while ($results = $result->fetch_assoc()) {
                    $this->logger->info("expense found " . $results["month"]);
                    $responseArray[] = array(
                        'month' => $results["month"],
                        'total' => $results["total"],
                        'month_num' => $results["month_num"],
                        'year' => $results["year"]
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

    public function getIncomeByMonth($propertyGuid, $numberOfDays): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            //validate property id
            //validate property id
            if (strlen($propertyGuid) < 1 ) {
                return array(
                    'result_message' => "Property GUID is invalid",
                    'result_code' => 1
                );
            }

            //validate number of days
            if (strlen($numberOfDays) < 1 || !is_numeric($numberOfDays) || intval($numberOfDays) < 1) {
                return array(
                    'result_message' => "Number Of days is not valid",
                    'result_code' => 1
                );
            }

            $property = $this->em->getRepository(Properties::class)->findOneBy(array('guid' =>  $propertyGuid));
            if ($property == null) {
                return array(
                    'result_message' => "Property not found",
                    'result_code' => 1
                );
            }

            $sql = "SELECT
    MONTHNAME(`date`) AS `month`,
    MONTH(`date`) AS `month_num`,
    YEAR(`date`) AS `year`,
    SUM(`amount`) AS `total`
FROM
    `transaction`, leases
WHERE lease = leases.id and
leases.property = ".$property->getId()." and 
   amount < 0 and 
    `date` >= CURDATE() - INTERVAL ".$numberOfDays." DAY
GROUP BY
    `month_num`
ORDER BY
    `month_num`;";
            $this->logger->info("income sql " . $sql);

            $databaseHelper = new DatabaseApi($this->logger);
            $result = $databaseHelper->queryDatabase($sql);

            if (!$result) {
                return array();
            } else {
                while ($results = $result->fetch_assoc()) {
                    $this->logger->info("income found " . $results["month"]);
                    $responseArray[] = array(
                        'month' => $results["month"],
                        'month_num' => $results["month_num"],
                        'total' => intval($results["total"]) * -1,
                        'year' => $results["year"]
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

    public function getExpensesIncomeByMonth($propertyGuid, $numberOfDays): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();

        //validate property id
        if (strlen($propertyGuid) !== 36 ) {
            return array(
                'result_message' => "Property GUID is invalid",
                'result_code' => 1
            );
        }

        //validate number of days
        if (strlen($numberOfDays) < 1 || !is_numeric($numberOfDays) || intval($numberOfDays) < 1) {
            return array(
                'result_message' => "Number Of days is not valid",
                'result_code' => 1
            );
        }

        $incomeArray = $this->getIncomeByMonth($propertyGuid, $numberOfDays);
        $this->logger->debug("Size for income array " . sizeof($incomeArray));
        $expensesArray = $this->getExpensesByMonth($propertyGuid, $numberOfDays);

        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec'
        ];

        for($i = 1; $i < 13; $i++){
            $incomeValue = 0;
            $expenseValue = 0;
            $year = 0;
            foreach ($incomeArray as $income) {
                $this->logger->info("income month num " . print_r($income, true));
                if (strcmp($income['month_num'], $i) == 0) {
                    $incomeValue = $income['total'];
                    $year = $income['year'];
                }
            }

            foreach ($expensesArray as $expense) {
                $this->logger->info("month num " . $expense['month_num']);
                if (strcmp($expense['month_num'], $i) == 0) {
                    $expenseValue = $expense['total'];
                    $year = $expense['year'];
                }
            }

            $date = DateTime::createFromFormat('d/m/Y', '01/'.$i.'/'.$year);
            if($year !== 0 ){
                $responseArray[] = array(
                    'month' => $months[$i] . " " . substr($year, 2,2),
                    'income' => $incomeValue,
                    'expense' => $expenseValue,
                    'date' => $date->format("Y-M-d")
                );
            }



        }
        // Create an instance of YourClass
       // $yourObject = new YourClass();

// Sort the array by date using $this->sortByDate
        $this->sortResponseArrayByDate($responseArray);
        return $responseArray;
    }

    public function sortByDate($a, $b) {
        $dateA = strtotime($a['date']);
        $dateB = strtotime($b['date']);
        return $dateA - $dateB;
    }

    public function sortResponseArrayByDate(&$responseArray) {
        usort($responseArray, array($this, 'sortByDate'));
    }

    public function getExpensesByAccount($propertyGuid, $numberOfDays): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            //validate property id
            //validate property id
            if (strlen($propertyGuid) < 1 ) {
                return array(
                    'result_message' => "Property GUID is invalid",
                    'result_code' => 1
                );
            }

            //validate number of days
            if (strlen($numberOfDays) < 1 || !is_numeric($numberOfDays) || intval($numberOfDays) < 1) {
                return array(
                    'result_message' => "Number Of days is not valid",
                    'result_code' => 1
                );
            }
            $property = $this->em->getRepository(Properties::class)->findOneBy(array('guid' =>  $propertyGuid));
            if ($property == null) {
                return array(
                    'result_message' => "Property not found",
                    'result_code' => 1
                );
            }

            $sql = "SELECT name, sum(amount) as total
FROM `expense`, expense_account
where `expense`.`expense` = expense_account.id and  property = ".$property->getId()." and `amount` > 0 and `date` > (DATE(NOW()) - INTERVAL ".$numberOfDays." DAY) group by `expense`";
            $databaseHelper = new DatabaseApi($this->logger);
            $result = $databaseHelper->queryDatabase($sql);

            if (!$result) {
                return array(
                    'result_code' => 1,
                    'result_message' => "no data found"
                );
            } else {
                $sum = 0;
                while ($results = $result->fetch_assoc()) {
                    $responseArray[] = array(
                        'expense' => $results["name"],
                        'amount' => $results["total"],
                    );
                }

                return $responseArray;
            }
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    public function getExpenseAccounts(): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            return $this->em->getRepository(ExpenseAccount::class)->findAll();
        } catch (Exception $ex) {
            $this->logger->error("Error " . print_r($responseArray, true));
            return array(
                'result_message' => $ex->getMessage(),
                'result_code' => 1
            );
        }
    }

    #[ArrayShape(['result_message' => "string", 'result_code' => "int"])]
    public function deleteExpense($guid): array
    {
        $this->logger->debug("Starting Method: " . __METHOD__);
        $responseArray = array();
        try {
            //validate number of days
            if (strlen($guid) != 36) {
                return array(
                    'result_message' => "Expense Guid invalid",
                    'result_code' => 1
                );
            }
            $expense = $this->em->getRepository(Expense::class)->findOneBy(array('guid' => $guid));

            if ($expense == null) {
                return array(
                    'result_message' => "Expense not found",
                    'result_code' => 1
                );
            }

            $this->em->remove($expense);
            $this->em->flush($expense);

            return array(
                'result_message' => "Successfully removed expense",
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
