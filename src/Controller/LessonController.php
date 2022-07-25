<?php

namespace App\Controller;

use App\Entity\Lesson;
use App\Form\LessonType;
use App\Repository\LessonRepository;
use App\Service\BillingClient;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/lessons")
 */
class LessonController extends AbstractController
{
    /**
     * @Route("/", name="app_lesson_index", methods={"GET"})
     */
    public function index(LessonRepository $lessonRepository): Response
    {
        return $this->render('lesson/index.html.twig', [
            'lessons' => $lessonRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="app_lesson_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_SUPER_ADMIN", statusCode=403 , message="Нет доступа!")
     */
    public function new(Request $request, LessonRepository $lessonRepository): Response
    {
        $lesson = new Lesson();
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lessonRepository->add($lesson);
            return $this->redirectToRoute('app_lesson_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('lesson/new.html.twig', [
            'lesson' => $lesson,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_lesson_show", methods={"GET"})
     */
    public function show(Lesson $lesson, BillingClient $billingClient): Response
    {
        $course = $lesson->getCourse();

        $billingCourse = $billingClient->getCurrentCourse($course);

        if ($billingCourse['type'] === 'free') {
            return $this->render('lesson/show.html.twig', [
                'lesson' => $lesson,
            ]);
        }

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        $apiToken = $this->getUser()->getApiToken();
        $transaction = $billingClient->getTransactions(
            ['type' => 'payment', 'course_code' => $course->getCharCode(), 'skip_expired' => true],
            $apiToken
        );
        if ($transaction || $this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->render('lesson/show.html.twig', [
                'lesson' => $lesson,
            ]);
        }
        throw new HttpException(Response::HTTP_NOT_ACCEPTABLE,'Данный курс вам недоступен!');
    }

    /**
     * @Route("/{id}/edit", name="app_lesson_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_SUPER_ADMIN", statusCode=403 , message="Нет доступа!")
     */
    public function edit(Request $request, Lesson $lesson, LessonRepository $lessonRepository): Response
    {
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lessonRepository->add($lesson);
            return $this->redirectToRoute(
                'app_course_show',
                ['char_code' => $lesson->getCourse()->getCharCode()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderForm('lesson/edit.html.twig', [
            'lesson' => $lesson,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_lesson_delete", methods={"POST"})
     * @IsGranted("ROLE_SUPER_ADMIN", statusCode=403 , message="Нет доступа!")
     */
    public function delete(Request $request, Lesson $lesson, LessonRepository $lessonRepository): Response
    {
        $course = $lesson->getCourse();
        if ($this->isCsrfTokenValid('delete'.$lesson->getId(), $request->request->get('_token'))) {
            $lessonRepository->remove($lesson);
        }

        return $this->redirectToRoute('app_course_show', ['char_code' => $course->getCharCode()], Response::HTTP_SEE_OTHER);
    }
}
