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
    private $input_list;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInputList(): ?string
    {
        return $this->input_list;
    }

    public function setInputList(string $input_list): self
    {
        $this->input_list = $input_list;

        return $this;
    }
}
