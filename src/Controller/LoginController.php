<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, LoggerInterface $logger): Response
    {
        $logger->info("Starting Methods: " . __METHOD__);
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('signin.html', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/properties/", name="app_properties")
     */
    public function app_properties(LoggerInterface $logger): Response
    {
        if ($this->getUser() !== null) {
            $logger->info("Session: " . print_r($_SESSION, true));
            $logger->info("user roles: " . print_r($this->getUser()->getRoles(), true));
            return $this->render('properties.html');
        } else {
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/public/is_logged_in/", name="app_is_logged_in")
     */
    public function app_is_logged_in(LoggerInterface $logger): Response
    {
        if ($this->getUser() == null) {
            return new JsonResponse("Not logged in", 200, array());
        }else{
            return new JsonResponse("logged in", 200, array());
        }
    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logout(): void
    {

    }
}