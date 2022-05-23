<?php

namespace App\Tests;

use App\DataFixtures\CoursesFixtures;
use App\Entity\Course;

class LessonTest extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [new CoursesFixtures()];
    }
    public function testLessonPagesResponse(): void
    {
        $client = self::getClient();

        $courseRepository = self::getEntityManager()->getRepository(Course::class);

        $courses = $courseRepository->findAll();
        foreach ($courses as $course) {
            foreach ($course->getLessons() as $lesson) {
                $client->request('GET', '/lessons/' . $lesson->getId());
                $this->assertResponseOk();

                $client->request('GET', '/lessons/' . $lesson->getId() . '/edit');
                $this->assertResponseOk();

                $client->request('POST', '/lessons/' . $lesson->getId() . '/edit');
                $this->assertResponseOk();
            }
        }
    }

    public function testLessonCreation(): void
    {
        $client = self::getClient();

        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $link = $crawler->filter('.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $coursename = $crawler->filter('.cn')->first()->text();
        $link = $crawler->filter('.lesson-add')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'lesson[name]' => 'Новый урок',
            'lesson[content]' => 'Текст урока',
            'lesson[lesson_number]' => 33,
        ]);
        $course = self::getEntityManager()
            ->getRepository(Course::class)
            ->findOneBy(['name' => $coursename]);

        $client->submit($form);
        self::assertTrue($client->getResponse()->isRedirect('/courses/' . $course->getCharCode()));
        $crawler = $client->followRedirect();

        $lessonLink = $crawler->filter('.list-group-item > a')->last()->link();
        $client->click($lessonLink);
        $this->assertResponseOk();
    }

    public function testLessonCreation1FiledErr(): void
    {
        $client = self::getClient();

        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $link = $crawler->filter('.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('.lesson-add')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'lesson[name]' => '',
            'lesson[content]' => 'урок',
            'lesson[lesson_number]' => 1000,
        ]);
        $client->submit($form);
        $crawler = $client->submit($form);
        $error = $crawler->filter('.invalid-feedback')->first();

        self::assertEquals('Error This value should not be blank.', $error->text());
    }
    public function testLessonCreation3FiledErr(): void
    {
        $client = self::getClient();

        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $link = $crawler->filter('.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $link = $crawler->filter('.lesson-add')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $submitButton = $crawler->selectButton('Сохранить');

        $form = $submitButton->form([
            'lesson[name]' => 'Lesson',
            'lesson[content]' => "Some text",
            'lesson[lesson_number]' => '',
        ]);
        $client->submit($form);
        $crawler = $client->submit($form);
        $error = $crawler->filter('.invalid-feedback')->first();

        self::assertEquals('Error This value should not be blank.', $error->text());

    }
}
