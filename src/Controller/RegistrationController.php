<?php

namespace App\Controller;

use App\Entity\Tenant;
use App\Entity\User;
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
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('post')) {
            return $this->render('signup.html', [
                'error' => "",
            ]);
        }
        $logger->info("Starting Method: " . __METHOD__);

        try {
            if (strlen($request->get("_password")) < 1 || strlen($request->get("_username")) < 1) {

                $response = array(
                    "result_message" =>"Error: Username and password is mandatory",
                    "result_code" => 1
                );
                return new JsonResponse($response , 200, array());
            }

            $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

            if (!preg_match($pattern, $request->get("_username"))) {
                $response = array(
                    "result_message" =>"Error: Username must be a valid email address",
                    "result_code" => 1
                );
                return new JsonResponse($response , 200, array());
            }


            if (strcmp($request->get("_password"), $request->get("_confirm_password")) !== 0) {
                $response = array(
                    "result_message" =>"Error: Passwords are not the same",
                    "result_code" => 1
                );
                return new JsonResponse($response , 200, array());
            }


            //check if user does exist
            $user = $entityManager->getRepository(User::class)->findOneBy(array('email' => $request->get("_username")));
            if($user !== null){
                $response = array(
                    "result_message" =>"Error: Email is already registered",
                    "result_code" => 1
                );
                return new JsonResponse($response , 200, array());
            }
            $user = new User();

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $request->get("_password")
                )
            );

            $user->setEmail($request->get("_username"));
            $user->setRoles(["ROLE_ADMIN"]);
            try {
                $entityManager->persist($user);
                $entityManager->flush();
            } catch (Exception $exception) {
                $logger->error($exception->getTraceAsString());
                $response = array(
                    "result_message" =>"Error: Registration failed",
                    "result_code" => 1
                );
                return new JsonResponse($response , 200, array());
            }

            $response = array(
                "result_message" =>"Successfully registered, Please sign in",
                "result_code" => 0
            );
            return new JsonResponse($response , 200, array());
        } catch (\Exception $exception) {
            $logger->info($exception->getMessage());
            $response = array(
                "result_message" =>"Error: Registration failed",
                "result_code" => 1
            );
            return new JsonResponse($response , 200, array());
        }
    }
}
