#!/usr/bin/env php
<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;

$command = $argv[1] ?? '';
$name = $argv[2] ?? '';

$name = formatName($name, $command);

switch ($command) {
    case 'do:controller':
        if ($name) {
            makeController($name);
        }
        break;
    case 'do:view':
        if ($name) {
            makeView($name);
        }
        break;
    case 'do:repository':
        if ($name) {
            makeRepository($name);
        }
        break;
    case 'do:entity':
        if ($name) {
            makeEntity($name);
        }
        break;
    case 'do:service':
        if ($name) {
            makeService($name);
        }
        break;

    case 'do:mvc':
        if ($name) {
            $filesystem = new Filesystem();

            $controllerPath = __DIR__ . '/../src/Controller/' . $name . 'Controller.php';
            $viewPath = __DIR__ . '/../src/View/' . kebabCase($name) . '.view.php';
            $repositoryPath = __DIR__ . '/../src/Repository/' . $name . 'Repository.php';
            $entityPath = __DIR__ . '/../src/Entity/' . $name . '.php';

            if ($filesystem->exists($controllerPath) || $filesystem->exists($viewPath) || $filesystem->exists($repositoryPath) || $filesystem->exists($entityPath)) {
                echo "One or more files for $name already exist. MVC creation aborted.\n";
            } else {
                makeController($name);
                makeView($name);
                makeRepository($name);
                makeEntity($name);
                echo "MVC structure for $name created successfully.\n";
            }
        }
        break;
    default:
        echo "Usage:\n";
        echo "  php bin/console do:controller ControllerName\n";
        echo "  php bin/console do:view ViewName\n";
        echo "  php bin/console do:repository RepositoryName\n";
        echo "  php bin/console do:entity EntityName\n";
        echo "  php bin/console do:service ServiceName\n";
        exit(1);
}


function formatName(string $name, string $command = ''): string
{
    $suffixes = ['Repository', 'View', 'Controller', 'Entity', 'Service'];

    foreach ($suffixes as $suffix) {
        if (str_ends_with($name, $suffix)) {
            $name = substr($name, 0, -strlen($suffix));
        }
    }

    if ($command === 'do:view') {
        $name = strtolower(str_replace([' ', '_'], '-', $name));
    } else {
        // Sinon, convertir en PascalCase
        $name = str_replace(['-', '_'], ' ', strtolower($name));
        $name = str_replace(' ', '', ucwords($name));
    }

    return $name;
}


function makeController(string $name): void
{
    $filesystem = new Filesystem();
    $controllerPath = __DIR__ . '/../src/Controller/' . $name . 'Controller.php';

    if ($filesystem->exists($controllerPath)) {
        echo "Controller $name already exists.\n";
        return;
    }

    $template = file_get_contents(__DIR__ . '/templates/controller.txt');

    $viewNameKebabCase = strtolower(str_replace(' ', '-', preg_replace('/(?<!^)[A-Z]/', '-$0', $name)));

    $content = str_replace(['{{name}}', '{{view}}'], [$name, $viewNameKebabCase], $template);
    try {
        $filesystem->dumpFile($controllerPath, $content);
        echo "Controller $name created successfully.\n";
    } catch (IOExceptionInterface $exception) {
        echo "An error occurred while creating the controller: " . $exception->getMessage() . "\n";
    }
}


function makeView(string $name): void
{
    $filesystem = new Filesystem();
    $viewPath = __DIR__ . '/../src/resources/views/' . kebabCase($name) . '.view.php';

    if ($filesystem->exists($viewPath)) {
        echo "View $name already exists.\n";
        return;
    }

    $template = file_get_contents(__DIR__ . '/templates/view.txt');
    $content = str_replace('{{name}}', kebabCase($name), $template);

    try {
        $filesystem->dumpFile($viewPath, $content);
        echo "View $name created successfully.\n";
    } catch (IOExceptionInterface $exception) {
        echo "An error occurred while creating the view.txt: " . $exception->getMessage() . "\n";
    }
}

function makeRepository(string $name): void
{
    $filesystem = new Filesystem();
    $repositoryPath = __DIR__ . '/../src/Repository/' . $name . 'Repository.php';

    if ($filesystem->exists($repositoryPath)) {
        echo "Repository $name already exists.\n";
        return;
    }

    $template = file_get_contents(__DIR__ . '/templates/repository.txt');
    $content = str_replace('{{name}}', $name, $template);

    try {
        $filesystem->dumpFile($repositoryPath, $content);
        echo "Repository $name created successfully.\n";
    } catch (IOExceptionInterface $exception) {
        echo "An error occurred while creating the repository.txt: " . $exception->getMessage() . "\n";
    }
}

function makeEntity(string $name): void
{
    $filesystem = new Filesystem();
    $entityPath = __DIR__ . '/../src/Entity/' . $name . '.php';

    if ($filesystem->exists($entityPath)) {
        echo "Entity $name already exists.\n";
        return;
    }

    $template = file_get_contents(__DIR__ . '/templates/entity.txt');
    $content = str_replace('{{name}}', $name, $template);

    try {
        $filesystem->dumpFile($entityPath, $content);
        echo "Entity $name created successfully.\n";
    } catch (IOExceptionInterface $exception) {
        echo "An error occurred while creating the entity.txt: " . $exception->getMessage() . "\n";
    }
}

function makeService(string $name): void
{
    $filesystem = new Filesystem();
    $servicePath = __DIR__ . '/../src/Service/' . $name . 'Service.php';

    if ($filesystem->exists($servicePath)) {
        echo "Service $name already exists.\n";
        return;
    }

    $template = file_get_contents(__DIR__ . '/templates/service.txt');
    $content = str_replace('{{name}}', $name, $template);

    try {
        $filesystem->dumpFile($servicePath, $content);
        echo "Service $name created successfully.\n";
    } catch (IOExceptionInterface $exception) {
        echo "An error occurred while creating the service.txt: " . $exception->getMessage() . "\n";
    }
}


function kebabCase(string $string): string
{
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string)));
}
