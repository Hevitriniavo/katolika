<?php


namespace App\Repository;

use App\Core\AbstractRepository;
use App\Entity\Category;

class CategoryRepository extends AbstractRepository
{
    protected function __construct()
    {
        parent::__construct(Category::class, ["id" => "int"]);
    }

    public static function createInstance(): self
    {
        return new self();
    }

    public function findAllWithProducts(): array
    {
        $query = $this
            ->select(['categories.*', 'c.*', 'p.*'])
            ->fullOuterJoin('products',
                fn () => 'c.category_id = categories.id',
                'c', 'p'
            );

        echo $query->buildQuery();
        $results = $this->executeQuery($query);
        $entities = [];
        dump($results);
        foreach ($results as $result) {
            $categories = $this->hydrate($result);
        }
        return $entities;
    }

}
