<?php

namespace App\Entity;

use App\Repository\LessonRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LessonRepository::class)
 */
class Lesson
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Course::class, cascade={"persist"}, inversedBy="lessons")
     * @ORM\JoinColumn(nullable=false)
     */
    private $Course;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="integer")
     */
    private $lesson_number;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCourse(): ?Course
    {
        return $this->Course;
    }

    public function setCourse(?Course $Course): self
    {
        $this->Course = $Course;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getLessonNumber(): ?int
    {
        return $this->lesson_number;
    }

    public function setLessonNumber(int $lesson_number): self
    {
        $this->lesson_number = $lesson_number;

        return $this;
    }
}
