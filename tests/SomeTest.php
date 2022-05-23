<?php

namespace App\Tests;

use App\DataFixtures\CoursesFixtures;
use App\Tests\AbstractTest;

class SomeTest extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [new CoursesFixtures()];
    }
    public function testSomething(): void
    {
        $client = AbstractTest::getClient();
        $url = '/courses/';

        $crawler = $client->request('GET', $url);
        $crawler->text();
        $link = $crawler->selectLink('Подробнее')->link();
        $crawler = $client->click($link);

        $this->assertResponseOk();
    }
}
