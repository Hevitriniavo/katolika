<?php

namespace App\Core;

use PDO;
use App\Connection\Connection;

abstract class AbstractRepository extends PpaRepository
{
    protected string $table;

    protected function __construct(string $entityNamespace, array $primaryKeyInfo)
    {
        $pdo = $this->getConnection();
        parent::__construct($entityNamespace, $primaryKeyInfo, $pdo);
        $this->table = $this->getTableName();
    }

    protected function getConnection(): PDO
    {
        return Connection::getInstance()->getPdo();
    }

    public function getTableName(): string
    {
        $baseName = basename(str_replace('\\', '/', $this->entityNamespace));
        return $this->convertCamelCaseToTableName($baseName);
    }

    protected function convertCamelCaseToTableName(string $entityNamespace): string
    {
        $snakeCaseName = strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', $entityNamespace));
        if (str_ends_with($snakeCaseName, 'y')) {
            return substr($snakeCaseName, 0, -1) . 'ies';
        }
        return $snakeCaseName . 's';
    }


    protected function executeQuery(QueryBuilder $queryBuilder): array
    {
        $query = $queryBuilder->buildQuery();
        $parameters = $queryBuilder->getParameters();
        $stmt = $this->getConnection()->prepare($query);
        foreach ($parameters as $index => $value) {
            $stmt->bindValue($index + 1, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
