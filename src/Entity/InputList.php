<?php

namespace App\Entity;

use App\Repository\InputListRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InputListRepository::class)]
class InputList
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $input;

    #[ORM\Column(type: 'json')]
    private $scores = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInput(): ?string
    {
        return $this->input;
    }

    public function setInput(string $input): self
    {
        $this->input = $input;

        return $this;
    }

    public function getScores(): ?array
    {
        return $this->scores;
    }

    public function setScores(array $scores): self
    {
        $this->scores = $scores;

        return $this;
    }
}
