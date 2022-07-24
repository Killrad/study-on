<?php

namespace App\Controller;

use App\Entity\Course;
use App\Entity\Lesson;
use App\Form\CourseType;
use App\Form\LessonType;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Service\BillingClient;
use mysql_xdevapi\CollectionFind;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/courses")
 */
class CourseController extends AbstractController
{
    public function urlRedirectAction(){
        return $this->redirectToRoute("app_course_index");
    }
    /**
     * @Route("/", name="app_course_index", methods={"GET"})
     */
    public function index(CourseRepository $courseRepository, BillingClient $billingClient): Response
    {
        $courses = $courseRepository->findAll();
        $coursesView = [];

        foreach ($courses as $course){
            $courseCost = $billingClient->getCurrentCourse($course);
            $viewCourse = [
                'id' => $course->getId(),
                'charcode' => $course->getCharCode(),
                'name' => $course->getName(),
                'description' => $course->getDescription(),
                'type' => $courseCost['type']
            ];
            if ($courseCost['type'] != 'free') $viewCourse['price'] = $courseCost['price'];
            $coursesView[] = $viewCourse;
        }
        return $this->render('course/index.html.twig', [
            'courses' => $coursesView,
        ]);

    }

    /**
     * @Route("/new", name="app_course_new", methods={"GET", "POST"})
     * @IsGranted("ROLE_SUPER_ADMIN", statusCode=403 , message="Нет доступа!")
     */
    public function new(Request $request, CourseRepository $courseRepository): Response
    {
        $course = new Course();
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $courseRepository->add($course);
            return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('course/new.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{char_code}", name="app_course_show", methods={"GET"})
     */
    public function show(Course $course): Response
    {
        return $this->render('course/show.html.twig', [
            'course' => $course,
        ]);
    }

    /**
     * @Route("/{char_code}/edit", name="app_course_edit", methods={"GET", "POST"})
     * @IsGranted("ROLE_SUPER_ADMIN", statusCode=403 , message="Нет доступа!")
     */
    public function edit(Request $request, Course $course, CourseRepository $courseRepository): Response
    {
        $form = $this->createForm(CourseType::class, $course);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $courseRepository->add($course);
            return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('course/edit.html.twig', [
            'course' => $course,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_course_delete", methods={"POST"})
     * @IsGranted("ROLE_SUPER_ADMIN", statusCode=403 , message="Нет доступа!")
     */
    public function delete(Request $request, Course $course, CourseRepository $courseRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$course->getId(), $request->request->get('_token'))) {
            $courseRepository->remove($course);
        }

        return $this->redirectToRoute('app_course_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/{char_code}/lesson/add", name="app_course_add_lesson", methods={"POST", "GET"})
     * @IsGranted("ROLE_SUPER_ADMIN", statusCode=403 , message="Нет доступа!")
     */
    public function addLesson(Request $request, LessonRepository $lessonRepository, Course $course): Response
    {
        $lesson = new Lesson();
        $lesson->setCourse($course);
        $form = $this->createForm(LessonType::class, $lesson);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $lessonRepository->add($lesson);
            return $this->redirectToRoute('app_course_show',
                ['char_code' => $course->getCharCode()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('lesson/new.html.twig', [
            'lesson' => $lesson,
            'form' => $form,
            'course' => $course,
        ]);
    }
}
