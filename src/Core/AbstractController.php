<?php

namespace App\Core;


abstract class AbstractController
{

    protected function views(string $templateName, array $variables = []): Response
    {
        $viewPath = $this->getViewPath($templateName);
        $jsPath = $this->getJsPath($templateName);
        $cssPath = $this->getCssPath($templateName);
        $baseLayoutPath = $this->getBaseLayoutPath();

        if (!file_exists($viewPath)) {
            return new Response("The view.txt '$templateName' does not exist.");
        }

        extract($variables);

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        $style = '';
        if (file_exists($cssPath)) {
            $style = file_get_contents($cssPath);
        }

        $script = '';
        if (file_exists($jsPath)) {
            $script = file_get_contents($jsPath);
        }

        if (!file_exists($baseLayoutPath)) {
            return new Response("The base layout file '$baseLayoutPath' does not exist.");
        }

        ob_start();
        require $baseLayoutPath;
        $finalContent = ob_get_clean();

        return new Response($finalContent);
    }

    private function getViewPath(string $templateName): string
    {
        return RESOURCE_PATH . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . $templateName . '.php';
    }

    private function getJsPath(string $templateName): string
    {
        return RESOURCE_PATH . DIRECTORY_SEPARATOR . "js" . DIRECTORY_SEPARATOR . $templateName . '.js';
    }

    private function getCssPath(string $templateName): string
    {
        return RESOURCE_PATH . DIRECTORY_SEPARATOR . "css" . DIRECTORY_SEPARATOR . $templateName . '.css';
    }

    private function getBaseLayoutPath(): string
    {
        return RESOURCE_PATH . DIRECTORY_SEPARATOR . 'base.php';
    }

    protected function redirect(string $path): Response
    {
        header("Location: $path");
        exit();
    }

    protected function setSession(string $name, mixed $value): Response
    {
        $_SESSION[$name] = $value;
        return new Response($this->getSession($name));
    }

    protected function getSession(string $name): Response
    {
        $value = $_SESSION[$name] ?? null;
        return new Response($value);
    }


}
