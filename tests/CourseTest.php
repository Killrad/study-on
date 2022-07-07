<?php

namespace App\Tests;

use App\DataFixtures\CoursesFixtures;
use App\Entity\Course;
use PhpParser\Node\Expr\BinaryOp\Equal;

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

        $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $client->request('GET', '/courses/' . $course->getCharCode());
        $this->assertResponseOk();

        $client->request('GET', '/courses/' . $course->getCharCode(). '/edit');
        $this->assertResponseOk();

        $client->request('POST', '/courses/' . $course->getCharCode(). '/edit');
        $this->assertResponseOk();

    }

    public function testCourseCreation(): void
    {
        $client = self::getClient();

        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();
        $courseCount = $crawler->filter('.card-link')->count();

        $link = $crawler->filter('.new-course')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();


        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'course[char_code]' => 'new-course',
            'course[name]' => 'Новый курс',
            'course[description]' => 'Some text',
        ]);
        $client->submit($form);
        $crawler = $client->followRedirect();
        $newCourseCount = $crawler->filter('.card-link')->count();
        self::assertTrue($courseCount + 1 == $newCourseCount);
    }

    public function testCourseCreationBlankField1(): void
    {
        $client = self::getClient();

        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $link = $crawler->filter('.new-course')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'course[char_code]' => '',
            'course[name]' => 'Новый курс',
            'course[description]' => 'Some text',
        ]);
        $client->submit($form);
        $crawler = $client->submit($form);
        $error = $crawler->filter('.invalid-feedback')->first();

        self::assertEquals('Error This value should not be blank.', $error->text());
    }

    public function testCourseCreationBlankField2(): void
    {
        $client = self::getClient();

        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $link = $crawler->filter('.new-course')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'course[char_code]' => 'new-course',
            'course[name]' => '',
            'course[description]' => 'Some text',
        ]);
        $client->submit($form);
        $crawler = $client->submit($form);
        $error = $crawler->filter('.invalid-feedback')->first();

        self::assertEquals('Error This value should not be blank.', $error->text());
    }
    public function testCourseCreationBlankField3(): void
    {
        $client = self::getClient();

        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $link = $crawler->filter('.new-course')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'course[char_code]' => 'new-course',
            'course[name]' => 'Новый курс',
            'course[description]' => '',
        ]);
        $client->submit($form);
        $crawler = $client->submit($form);
        $error = $crawler->filter('.invalid-feedback')->first();

        self::assertEquals('Error This value should not be blank.', $error->text());
    }

    public function testCourseCreationOverflowField1(): void
    {
        $client = self::getClient();

        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $link = $crawler->filter('.new-course')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'course[char_code]' => str_repeat('SomeText',100),
            'course[name]' => 'Новый курс',
            'course[description]' => 'sOME TEXT',
        ]);
        $client->submit($form);
        $crawler = $client->submit($form);
        $error = $crawler->filter('.invalid-feedback')->first();
        self::assertEquals('Error This value is too long. It should have 255 characters or less.', $error->text());
    }

    public function testCourseCreationOverflowField2(): void
    {
        $client = self::getClient();

        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $link = $crawler->filter('.new-course')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'course[char_code]' => 'course-new',
            'course[name]' => str_repeat('SomeText',100),
            'course[description]' => 'SOME TEXT',
        ]);
        $client->submit($form);
        $crawler = $client->submit($form);
        $error = $crawler->filter('.invalid-feedback')->first();
        self::assertEquals('Error This value is too long. It should have 255 characters or less.', $error->text());
    }

    public function testCourseCreationOverflowField3(): void
    {
        $client = self::getClient();

        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();

        $link = $crawler->filter('.new-course')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $submitButton = $crawler->selectButton('Сохранить');
        $form = $submitButton->form([
            'course[char_code]' => 'course-new',
            'course[name]' => 'New Course',
            'course[description]' => str_repeat('SomeText',200)
        ]);
        $client->submit($form);
        $crawler = $client->submit($form);
        $error = $crawler->filter('.invalid-feedback')->first();
        self::assertEquals('Error This value is too long. It should have 1000 characters or less.', $error->text());
    }

    public function testCourseDelete(): void
    {
        $client = self::getClient();

        $crawler = $client->request('GET', '/courses/');
        $this->assertResponseOk();
        $countCourse= $crawler->filter('.card-link')->count();
        $link = $crawler->filter('.card-link')->first()->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();

        $client->submit($crawler->selectButton('Удалить')->form());

        self::assertTrue($client->getResponse()->isRedirect());
        $crawler = $client->followRedirect();
        $this->assertResponseOk();
        self::assertCount($countCourse - 1, $crawler->filter('.card-link'));
    }

}
