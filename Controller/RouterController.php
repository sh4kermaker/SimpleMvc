<?php

namespace SimpleMvc\Controller;

use Application\Controller\DefaultController;

class RouterController extends AbstractController
{
    private $controller;

    public function indexAction()
    {
        $path = $this->getRouteParam('path');
        $pathParts = $this->parseURL($path);
        $controllerClass = dashesToCamelCase(array_shift($pathParts)) . 'Controller';

        if (file_exists('src/Application/Controller/' . $controllerClass . '.php')) {
            $controllerClass = "\\Application\\Controller\\$controllerClass";
            $this->controller = new $controllerClass($pathParts);
        }
        else {
            $this->controller = new DefaultController($this->parseURL($path));;
        }
    }

    protected function getTitle(){
        return $this->getController()->header[self::HEADER_TITLE_KEY];
    }

    protected function getKeywords(){
        return $this->getController()->header[self::HEADER_KEYWORDS_KEY];
    }

    protected function getDescription(){
        return $this->getController()->header[self::HEADER_DESCRIPTION_KEY];
    }

    private function parseURL($url)
    {
        $parsedUrl = parse_url($url);
        $parsedUrl["path"] = ltrim($parsedUrl["path"], "/");
        $parsedUrl["path"] = trim($parsedUrl["path"]);
        $pathParts = explode("/", $parsedUrl["path"]);
        return $pathParts;
    }

    protected function getController(){
        return $this->controller;
    }

}