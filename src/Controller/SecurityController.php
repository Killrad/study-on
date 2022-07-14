<?php

namespace App\Controller;

use App\DTO\ShortUSerDTO;
use App\Exception\BillingUnavailableException;
use App\Form\RegistrationType;
use App\Security\UserAuthenticator;
use App\Service\BillingClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(
        Request $request,
        UserAuthenticatorInterface $authenticator,
        UserAuthenticator $userAuthenticator,
        BillingClient $billingClient
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_profile');
        }
        $credentials = new ShortUSerDTO();
        $form = $this->createForm(RegistrationType::class, $credentials);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $user = $billingClient->userRegister($credentials);
            } catch (BillingUnavailableException $e) {
                return $this->render('security/register.html.twig', [
                    'registrationForm' => $form->createView(),
                    'errors' => $e->getMessage(),
                ]);
            }
            return $authenticator->authenticateUser(
                $user,
                $userAuthenticator,
                $request
            );
        }
        return $this->render('security/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
