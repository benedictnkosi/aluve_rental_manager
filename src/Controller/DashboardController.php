<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends AbstractController
{

    /**
     * @Route("/dashboard/", name="app_dashboard")
     */
    public function app_dashboard(LoggerInterface $logger): Response
    {
        return $this->render('dashboard.html');
    }


    /**
     * @Route("/statement/", name="app_statement")
     */
    public function app_statement(LoggerInterface $logger): Response
    {
        return $this->render('statement.html');
    }

}
