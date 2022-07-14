<?php

namespace App\Controller;

use App\Exception\BillingUnavailableException;
use App\Service\BillingClient;
use JMS\Serializer\SerializerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    private $billingClient;

    public function __construct(
        BillingClient $billingClient
    ) {
        $this->billingClient = $billingClient;
    }
    /**
     * @Route("/profile", name="app_profile")
     * @IsGranted("ROLE_USER", statusCode=403 , message="Не авторизированный пользователь!")
     */
    public function index(Request $request): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_course_index');
        }
        try {

            $userDTO = $this->billingClient->getCurrentUser($this->getUser());
        } catch (BillingUnavailableException $e) {
            throw new \Exception($e->getMessage());
        }
        return $this->render('profile/index.html.twig', [
            'user' => $userDTO
        ]);
    }
}
