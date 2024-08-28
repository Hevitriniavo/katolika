<?php

namespace App\Entity;

class Product
{
    private ?int $id = null;
    private ?string $name;
    private ?float $price;

    /** @var Category|null */
    private ?Category $category = null;

    public function __construct(?string $name = null, ?float $price = null)
    {
        $this->name = $name;
        $this->price = $price;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): void
    {
        $this->category = $category;
    }
}
