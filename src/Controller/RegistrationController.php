<?php

namespace App\Controller;

use App\Entity\Tenant;
use App\Entity\User;
use App\Service\CommunicationApi;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, LoggerInterface $logger, CommunicationApi $communicationApi): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('post')) {
            return $this->render('signup.html', [
                'error' => "",
            ]);
        }

        $logger->info("This is a post register user" );
        try {
            if (strlen($request->get("_password")) < 1 || strlen($request->get("_username")) < 1) {

                $response = array(
                    "result_message" =>" Username and password is mandatory",
                    "result_code" => 1
                );
                return new JsonResponse($response , 200, array());
            }

            $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

            if (!preg_match($pattern, $request->get("_username"))) {
                $response = array(
                    "result_message" =>" Username must be a valid email address",
                    "result_code" => 1
                );
                return new JsonResponse($response , 200, array());
            }


            if (strcmp($request->get("_password"), $request->get("_confirm_password")) !== 0) {
                $response = array(
                    "result_message" =>" Passwords are not the same",
                    "result_code" => 1
                );
                return new JsonResponse($response , 200, array());
            }


            //check if user does exist
            $user = $entityManager->getRepository(User::class)->findOneBy(array('email' => $request->get("_username")));
            if($user !== null){
                $response = array(
                    "result_message" =>" Email is already registered",
                    "result_code" => 1
                );
                return new JsonResponse($response , 200, array());
            }

            $logger->info("all validations passed" );
            $user = new User();

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $request->get("_password")
                )
            );

            $user->setEmail($request->get("_username"));
            $user->setName($request->get("_name"));
            $user->setGuid($this->generateGuid());
            $user->setState("active");
            if(strcmp($request->get("_user_type"), "Tenant")==0){
                $user->setRoles(["ROLE_TENANT"]);
            }else if(strcmp($request->get("_user_type"), "Landlord")==0){
                $user->setRoles(["ROLE_LANDLORD"]);
            }else{
                $response = array(
                    "result_message" =>" User type is not recognised",
                    "result_code" => 1
                );
                return new JsonResponse($response , 200, array());
            }

            $logger->info("persist user" );
            try {
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (Exception $exception) {
                $logger->error($exception->getTraceAsString());
                $response = array(
                    "result_message" =>" Registration failed",
                    "result_code" => 1
                );
                return new JsonResponse($response , 200, array());
            }

            $response = array(
                "result_message" =>"Successfully registered, Please sign in",
                "result_code" => 0
            );

            $message = "Welcome to Aluve App. We hope you enjoy your experience with us.";
            $subject = "Aluve App - New Registration";
            $link =  "https://" . $_SERVER['HTTP_HOST'] . "/login";
            $linkText = "LOGIN";
            $template = "generic";
            $communicationApi->sendEmail($request->get("_username"), "",$subject , $message, $link, $linkText, $template);
            return new JsonResponse($response , 200, array());
        } catch (\Exception $exception) {
            $logger->info($exception->getMessage());
            $response = array(
                "result_message" =>" Registration failed",
                "result_code" => 1
            );
            return new JsonResponse($response , 200, array());
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
