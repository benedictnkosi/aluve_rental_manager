<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\CommunicationApi;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
        $logger->info("Starting Methods: " . __METHOD__);
        if ($this->getUser() !== null) {
            $logger->info("Session: " . print_r($_SESSION, true));
            $logger->info("user roles: " . print_r($this->getUser()->getRoles(), true));
            $logger->info("user email: " . print_r($this->getUser()->getUserIdentifier(), true));
            $_SESSION["username"] = $this->getUser()->getUserIdentifier();
            return $this->render('properties.html');
        } else {
            return $this->redirectToRoute("app_login");
        }
    }

    /**
     * @Route("/no_auth/is_logged_in/", name="app_is_logged_in")
     */
    public function app_is_logged_in(LoggerInterface $logger): Response
    {
        $logger->info("Starting Methods: " . __METHOD__);
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

    /**
     * @Route("no_auth/me")
     */
    public function meAction(Request $request, LoggerInterface $logger): JsonResponse
    {
        $logger->info("Starting Methods: " . __METHOD__);
        if (!$request->isMethod('get')) {
            return new JsonResponse("Method Not Allowed" , 405, array());
        }
        $responseArray = array(
            'authenticated' => $this->getUser() !== null,
            'result_code' => 0,
            'roles' => $this->getUser()->getRoles()
        );

        return new JsonResponse($responseArray);
    }

    /**
     * @Route("/reset", name="app_render_reset_password")
     */
    public function renderResetPassword(LoggerInterface $logger): Response
    {
        $logger->info("Starting Methods: " . __METHOD__);
        return $this->render('reset.html');
    }

    /**
     * @Route("/reset/email", name="app_reset_email")
     */
    public function sendResetPassword(Request $request, LoggerInterface $logger, EntityManagerInterface $entityManager, CommunicationApi $communicationApi): Response
    {
        $logger->info("Starting Methods: " . __METHOD__);
        //check if user does exist
        $user = $entityManager->getRepository(User::class)->findOneBy(array('email' => $request->get("_username")));
        if($user == null){
            $response = array(
                "result_message" =>"Email address not found",
                "result_code" => 1
            );
            return new JsonResponse($response , 200, array());
        }

        $message = "Forgot your password? That's okay, it happens! Click on the button below to reset your password";
        $subject = "Aluve App - Reset Password";
        $link =  "https://" . $_SERVER['HTTP_HOST'] . "/reset?guid=" . $user->getGuid();
        $linkText = "Reset Password";
        $template = "generic";
        //$communicationApi->sendEmail($user->getEmail(), $user->getName(), $subject, $message, $link, $linkText, $template);

        $response = array(
            "result_message" => "Please check your email for the reset password instructions",
            "result_code" => 0
        );

        return new JsonResponse($response , 200, array());
    }


    /**
     * @Route("/reset/password", name="app_reset_password")
     */
    public function resetPassword(Request $request, LoggerInterface $logger, EntityManagerInterface $entityManager,UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $logger->info("Starting Methods: " . __METHOD__);

        if (strcmp($request->get("_password"), $request->get("_confirm_password")) !== 0) {
            $response = array(
                "result_message" =>" Passwords are not the same",
                "result_code" => 1
            );
            return new JsonResponse($response , 200, array());
        }

        //check if user does exist
        $user = $entityManager->getRepository(User::class)->findOneBy(array('guid' => $request->get("_guid")));
        if($user == null){
            $response = array(
                "result_message" =>"User not found. Email link is invalid",
                "result_code" => 1
            );
            return new JsonResponse($response , 200, array());
        }

        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $request->get("_password")
            )
        );
        try {
            $entityManager->persist($user);
            $entityManager->flush();
        } catch (Exception $exception) {
            $logger->error($exception->getTraceAsString());
            $response = array(
                "result_message" =>"Failed to reset password. Please try again.",
                "result_code" => 1
            );
            return new JsonResponse($response , 200, array());
        }

        $response = array(
            "result_message" =>"Successfully changed password, Please sign in",
            "result_code" => 0
        );

        return new JsonResponse($response , 200, array());
    }

}