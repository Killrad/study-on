<?php

namespace App\Tests;

use App\DataFixtures\CoursesFixtures;
use App\Entity\Course;

class CourseTest extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [new CoursesFixtures()];
    }
    public function testCoursePagesResponse(): void
    {
        $client = self::getClient();

        $courseRepository = self::getEntityManager()->getRepository(Course::class);

        $course = $courseRepository->findAll()[1];

        $client->request('GET', '/courses');
        $this->assertResponseOk();

        $client->request('GET', '/courses/' . $course->getCharCode());
        $this->assertResponseOk();

        $client->request('GET', '/courses/' . $course->getCharCode(). '/edit');
        $this->assertResponseOk();

        $client->request('POST', '/courses/' . $course->getCharCode(). '/edit');
        $this->assertResponseOk();

    }

    /*public function testLessonCreation(): void
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

    public function testLessonCreationBlankField1(): void
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
    public function testLessonCreationBlankField3(): void
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

    public function testLessonCreationOverflowField1(): void
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
            'lesson[name]' => str_repeat('SomeText',100),
            'lesson[content]' => "Some text",
            'lesson[lesson_number]' => '',
        ]);
        $client->submit($form);
        $crawler = $client->submit($form);
        $error = $crawler->filter('.invalid-feedback')->first();
        self::assertEquals('Error This value is too long. It should have 255 characters or less.', $error->text());
    }

    public function testLessonDelete(): void
    {
        $client = self::getClient();

        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $link = $crawler->filter('.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $links = $crawler->filter('.list-group-item > a');
        $lessoncount = $links->count();
        $link = $links->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $client->submit($crawler->selectButton('Удалить')->form());

        self::assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertResponseOk();
        self::assertCount($lessoncount - 1, $crawler->filter('.list-group-item > a'));
    }

    public function testLessonEdit(): void
    {
        $client = self::getClient();

        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $link = $crawler->filter('.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();


        $coursename = $crawler->filter('.cn')->first()->text();

        $lessonLink = $crawler->filter('.list-group-item > a')->first()->link();
        $crawler = $client->click($lessonLink);
        $this->assertResponseOk();

        $link = $crawler->filter('.lesson-edit')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form();
        $course = self::getEntityManager()
            ->getRepository(Course::class)
            ->findOneBy(['name' => $coursename]);

        $form['lesson[name]'] = 'Измененный урок';
        $form['lesson[content]'] = 'Изменённое содержание';
        $form['lesson[lesson_number]'] = 9999;
        $client->submit($form);

        self::assertTrue($client->getResponse()->isRedirect('/courses/' . $course->getCharCode()));
        $crawler = $client->followRedirect();
        $this->assertResponseOk();

        $lessonLink = $crawler->filter('.list-group-item > a')->last()->link();
        $crawler = $client->click($lessonLink);
        $this->assertResponseOk();

        $lessonName = $crawler->filter('.card-title')->text();

        self::assertEquals('Измененный урок', $lessonName);

        $lessonDescription = $crawler->filter('.card-text')->text();
        self::assertEquals('Изменённое содержание', $lessonDescription);

        $lessonCount = $crawler->filter('.card-subtitle')->text();
        self::assertEquals('Урок номер: 9999' , $lessonCount);

    }*/
}
