<?php

namespace App\Core;

use PDO;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use RuntimeException;
use Throwable;

abstract class PpaRepository extends QueryBuilder
{
    private static ?QueryBuilder $queryBuilder = null;
    protected string $entityNamespace;
    private array $primaryKeyInfo;
    private PDO $connection;

    public function __construct(
        string $entityNamespace,
        array  $primaryKeyInfo,
        PDO    $connection
    )
    {
        $this->entityNamespace = $entityNamespace;
        $this->primaryKeyInfo = $primaryKeyInfo;
        $this->connection = $connection;

        $tableName = $this->convertCamelCaseToTableName($this->entityNamespace);
        parent::__construct($tableName);
    }



    public function createQueryBuilder(): QueryBuilder
    {
        if (self::$queryBuilder === null) {
            self::$queryBuilder = new QueryBuilder($this->table);
        }
        return self::$queryBuilder;
    }

    public function findAll(): array
    {
        $this->select(['*']);
        $query = $this->buildQuery();

        $stmt = $this->connection->query($query);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $entities = [];
        foreach ($results as $result) {
            $entities[] = $this->hydrate($result);
        }

        return $entities;
    }

    public function add(array $payload): ?object
    {
        $tableName = $this->convertCamelCaseToTableName($this->entityNamespace);
        $columns = implode(',', array_keys($payload));
        $placeholders = implode(',', array_fill(0, count($payload), '?'));

        $query = "INSERT INTO {$tableName} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->connection->prepare($query);
        $stmt->execute(array_values($payload));
        $insertedId = $this->connection->lastInsertId();
        return $this->findOne($insertedId);
    }

    public function findOne(mixed $id): ?object
    {
        $primaryKey = key($this->primaryKeyInfo);
        $this->select(['*'])->where($primaryKey, '=', $id);
        $query = $this->buildQuery();

        $stmt = $this->connection->prepare($query);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $this->hydrate($result) : null;
    }


    public function update(array $payload): ?object
    {
        $tableName = $this->convertCamelCaseToTableName($this->entityNamespace);
        $primaryKey = key($this->primaryKeyInfo);

        $setClause = implode(', ', array_map(fn($col) => "{$col} = ?", array_keys($payload)));
        $query = "UPDATE {$tableName} SET {$setClause} WHERE {$primaryKey} = ?";

        $stmt = $this->connection->prepare($query);
        $stmt->execute(array_merge(array_values($payload), [$payload[$primaryKey]]));

        return $this->findOne($payload[$primaryKey]);
    }

    public function delete(mixed $id): void
    {
        $tableName = $this->convertCamelCaseToTableName($this->entityNamespace);
        $primaryKey = key($this->primaryKeyInfo);

        $query = "DELETE FROM {$tableName} WHERE {$primaryKey} = ?";
        $stmt = $this->connection->prepare($query);
        $stmt->execute([$id]);
    }

    protected function hydrate(array $data): object
    {
        try {
            $reflectionClass = new ReflectionClass($this->entityNamespace);

            $instance = $this->createInstance($reflectionClass, $data);

            foreach ($data as $key => $value) {
                $camelCaseKey = $this->convertSnakeCaseToCamelCase($key);

                if ($reflectionClass->hasProperty($camelCaseKey)) {
                    $property = $reflectionClass->getProperty($camelCaseKey);
                    $this->setProperty($instance, $property, $value);
                }
            }

            return $instance;
        } catch (ReflectionException $e) {
            throw new RuntimeException("Hydration failed: " . $e->getMessage());
        }
    }

    private function createInstance(ReflectionClass $reflectionClass, array $data): object
    {
        try {
            $constructor = $reflectionClass->getConstructor();
            if ($constructor && $constructor->getNumberOfParameters() > 0) {
                $parameters = [];
                foreach ($constructor->getParameters() as $parameter) {
                    $name = $parameter->getName();
                    if (array_key_exists($name, $data)) {
                        $parameters[] = $data[$name];
                    } else {
                        throw new RuntimeException(
                            "Missing parameter '{$name}' in data array for class '{$reflectionClass->getName()}'"
                        );
                    }
                }
                return $reflectionClass->newInstanceArgs($parameters);
            }

            return $reflectionClass->newInstance();
        } catch (ReflectionException $e) {
            throw new RuntimeException(
                "ReflectionException: Unable to create instance of class '{$reflectionClass->getName()}'. " .
                "Error: " . $e->getMessage()
            );
        } catch (Throwable $e) {
            throw new RuntimeException(
                "Unexpected error occurred while creating instance of class '{$reflectionClass->getName()}'. " .
                "Error: " . $e->getMessage()
            );
        }
    }


    private function setProperty(object $instance, ReflectionProperty $property, mixed $value): void
    {

        $type = $this->getPropertyType($property);
        if ($type) {
            if ($this->isEntity($type)) {
                $property->setValue($instance, $this->findOneBy($type, $value));
            } elseif ($this->isCollection($type)) {
                $property->setValue($instance, $this->findManyBy($type, $value));
            }
        } else {
            $property->setValue($instance, $value);
        }
    }

    private function getPropertyType(ReflectionProperty $property): ?string
    {
        $docComment = $property->getDocComment();
        if ($docComment) {
            preg_match('/@var\s+(\S+)/', $docComment, $matches);
            return $matches[1] ?? null;
        }
        return null;
    }

    private function isEntity(string $type): bool
    {
        return class_exists($type);
    }

    private function isCollection(string $type): bool
    {
        return str_contains($type, '[]');
    }

    private function findOneBy(string $entity, mixed $id): ?object
    {
        $repository = $this->getRepository($entity);
        return $repository->findById($id);
    }

    private function findManyBy(string $entity, mixed $ids): array
    {
        $repository = $this->getRepository($entity);
        return $repository->findByIds($ids);
    }

    private function getRepository(string $entity): object
    {
        return new $entity();
    }

    private function convertSnakeCaseToCamelCase(string $snakeCase): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $snakeCase))));
    }

    private function convertCamelCaseToTableName(string $entityNamespace): string
    {
        $baseName = basename(str_replace('\\', '/', $entityNamespace));
        $snakeCaseName = strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $baseName));
        if (str_ends_with($snakeCaseName, 'y')) {
            return substr($snakeCaseName, 0, -1) . 'ies';
        }
        return $snakeCaseName . 's';
    }
}
