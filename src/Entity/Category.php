<?php

namespace App\Entity;

class Category
{
    private ?int $id = null;
    private ?string $name = null;

    /** @var Product[] */
    private array $products = [];

    public function __construct(?string $name = null)
    {
        $this->name = $name;


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

    public function getProducts(): array
    {
        return $this->products;
    }

    public function addProduct(Product $product): void
    {
        $this->products[] = $product;
        $product->setCategory($this);
    }

    public function removeProduct(Product $product): void
    {
        $index = array_search($product, $this->products, true);
        if ($index !== false) {
            unset($this->products[$index]);
            $product->setCategory(null);
        }
    }
}
