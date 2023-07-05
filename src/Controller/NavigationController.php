<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;

class NavigationController extends AbstractController
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

    /**
     * @Route("/inspection/", name="app_inspection")
     */
    public function app_inspection(LoggerInterface $logger): Response
    {
        return $this->render('inspection.html');
    }

    /**
     * @Route("/applications/", name="app_applications")
     */
    public function app_applications(LoggerInterface $logger): Response
    {
        return $this->render('applications.html');
    }

    /**
     * @Route("/onboarding/", name="app_onboarding")
     */
    public function app_onboarding(LoggerInterface $logger): Response
    {
        return $this->render('onboarding.html');
    }

    /**
     * @Route("/view/inspection/", name="app_inspection_view")
     */
    public function app_inspection_view(LoggerInterface $logger): Response
    {
        return $this->render('view_inspection.html');
    }
}
