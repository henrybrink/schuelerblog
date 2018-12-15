<?php

namespace App\Controller;

use App\Entity\IServUser;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/api/auth/authenticate", name="oauth_login")
     */
    public function authenticateOAuth() {
        return $this->redirect(getenv("AUTH_REQUEST_URL") . "?response_type=code&client_id=" . getenv("CLIENT_ID"));
    }

    /**
     * @Route("/api/auth/callback", name="oauth_callback")
     */
    public function OAuthCallback(\Symfony\Component\HttpFoundation\Request $request) {
        $code = $request->get("code");

        if(!$code) {
            return $this->redirect("/404#eOAUTH_CODE_EMPTY");
        }

        $client = new Client();

            $response = \json_decode($client->request("POST", getenv("AUTH_TOKEN_URL"), [
                "form_params" => [
                    "grant_type" => "authorization_code",
                    "code" => $code,
                    "client_id" => getenv("CLIENT_ID"),
                    "client_secret" => getenv("CLIENT_SECRET")
                ]
            ])->getBody(), true);

            $username = $response["user_id"];

            $request->getSession()->set("can_comment", true);
            $request->getSession()->set("username", $username);

            $iServRepository = $this->getDoctrine()->getRepository(IServUser::class);
            $user = $iServRepository->findBy(["username" => $username]);

            if(!$user) {
                $user = new IServUser();
                $user->setUsername($username);
                $user->setDisplayName($username);
                $user->setDisabled(false);

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
            }

            return $this->redirect("/");
    }
}
