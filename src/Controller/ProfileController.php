<?php

namespace App\Controller;

use App\Exception\BillingUnavailableException;
use App\Repository\CourseRepository;
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


    /**
     * @Route("/profile/history", name="app_profile_history")
     * @IsGranted("ROLE_USER", statusCode=403 , message="Не авторизированный пользователь!")
     */
    public function history(Request $request, courseRepository $courseRepository):Response{

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_course_index');
        }
        try {
            $userDTO = $this->billingClient->getCurrentUser($this->getUser());
            $transactions = $this->billingClient->getUserTransactions($this->getUser());

        } catch (BillingUnavailableException $e) {
            throw new \Exception($e->getMessage());
        }
        $viewTransactions= [];
        foreach ($transactions as $transaction){
            if ($transaction['type'] == 'payment'){
            $courseInfo = $courseRepository->findOneBy(['char_code'=>$transaction['course_code']]);
            if ($courseInfo) {
                $courseData = $this->billingClient->getCurrentCourse($courseInfo);
                $viewTransaction = [
                    'TRtype' => $transaction['type'],
                    'course' => $courseInfo->getName(),
                    'courseCode' => $courseInfo->getCharCode(),
                    'type' => $courseData['type'],
                    'created_at' => $transaction['created_at'],
                    'value' => $transaction['ammount']
                ];
                }
            }else{
                $viewTransaction = [
                    'TRtype' => $transaction['type'],
                    'created_at' => $transaction['created_at'],
                    'value' => $transaction['ammount']
                ];
            }

            $viewTransactions[] = $viewTransaction;

        }
        return $this->render('profile/transaction_history.html.twig', [
            'transactions' => $viewTransactions,
            'user' => $userDTO
        ]);
    }
}
