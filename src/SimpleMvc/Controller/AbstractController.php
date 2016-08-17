<?php

namespace SimpleMvc\Controller;

abstract class AbstractController
{
    const HEADER_TITLE_KEY = 'title';
    const HEADER_KEYWORDS_KEY = 'keywords';
    const HEADER_DESCRIPTION_KEY = 'description';
    const FLASH_MESSAGES_KEY = 'flashMessages';

    const LANGUAGE_SK = 'sk_SK';
    const LANGUAGE_EN = 'en_US';

    protected static $languageMapping = [
        'sk' => self::LANGUAGE_SK,
        'en' => self::LANGUAGE_EN,
    ];

    protected $routeParams = [];

    protected $data = [];

    protected $jsonOutput;

    protected $view;

    protected $header = [
        self::HEADER_TITLE_KEY => '',
        self::HEADER_KEYWORDS_KEY => '',
        self::HEADER_DESCRIPTION_KEY => ''
    ];

    protected $layout = 'default';

    protected $notFoundRoute = 'not-found';

    protected $flashMessages = [];

    public function __construct(array $params){
        $this->setRouteParams($params);
        $this->view = $this->getDefaultViewName();          // default view is derived from actionName
        $this->dispatchToAction();
    }

    public function renderView()
    {
        $this->processFlashMessages();

        if ($this->view) {
            extract($this->sanitize($this->data));
            extract($this->data, EXTR_PREFIX_ALL, "");
            $reflect = new \ReflectionClass($this);
            require("view/templates/" . $reflect->getShortName() . '/' . $this->view . ".phtml");
        }
    }

    public function getJsonOutput(){
        return $this->jsonOutput;
    }

    protected function addFlashMessage($message)
    {
        if (isset($_SESSION[self::FLASH_MESSAGES_KEY]))
            $_SESSION[self::FLASH_MESSAGES_KEY][] = $message;
        else
            $_SESSION[self::FLASH_MESSAGES_KEY] = array($message);
    }

    protected function processFlashMessages()
    {
        if (isset($_SESSION[self::FLASH_MESSAGES_KEY])) {
            $messages = $_SESSION[self::FLASH_MESSAGES_KEY];
            unset($_SESSION[self::FLASH_MESSAGES_KEY]);
            $this->flashMessages =  $messages;
        }
    }

    protected function getFlashMessages(){
        return $this->flashMessages;
    }

    protected function redirect($url = '')
    {
        // if url not specified, redirect to same url
        if (empty($url)){
            $redirectUrl = $_SERVER['REQUEST_URI'];
        }
        // redirect to home
        else if ($url === '/'){
            $redirectUrl = '/';
        }
        else {
            $redirectUrl = '/' . $url;
        }

        header("Location: $redirectUrl");
        header("Connection: close");
        exit;
    }

    private function sanitize($x = null)
    {
        if (!isset($x))
            return null;
        elseif (is_string($x))
            return htmlspecialchars($x, ENT_QUOTES);
        elseif (is_array($x)) {
            foreach ($x as $k => $v) {
                $x[$k] = $this->sanitize($v);
            }
            return $x;
        } else
            return $x;
    }

    private function dispatchToAction(){
        $actionName = $this->getActionName();

        if (!method_exists($this, $actionName)){
            $this->redirect($this->notFoundRoute);
        }

        $this->$actionName();
    }

    private function getActionName(){
        $actionParam = dashesToCamelCase($this->getRouteParam('action'));
        $actionName = !empty($actionParam) ? $actionParam . 'Action' : 'indexAction';

        if (!method_exists($this, $actionName)){
            $this->redirect($this->notFoundRoute);
        }

        return $actionName;
    }

    private function setRouteParams(array $parametre){
        $namedRouteParams = $this->getRouterConfigForController()['namedRouteParams'];
        $paramNames = explode('/', $namedRouteParams);
        foreach ($paramNames as $paramName){
            $this->routeParams[$paramName] = array_shift($parametre);
        }

        if (!empty($parametre)){
            foreach($parametre as $parameter){
                $this->routeParams['additionalParams'][] = $parameter;
            }
        }
    }

    protected function getRouteParam($key, $undefinedValue = null){
        if (isset($this->routeParams[$key])){
            return $this->routeParams[$key];
        }

        return $undefinedValue;
    }

    protected function getRouteParams(){
        return $this->routeParams;
    }

    private function getRouterConfigForController(){
        $config = require 'config/router.config.php';
        $controllerClass = get_class($this);
        if (!array_key_exists($controllerClass, $config)){
            throw new \Exception("Missing router config for {$controllerClass}");
        }
        return $config[get_class($this)];
    }

    protected function setViewData(array $viewData){
        foreach ($viewData as $key => $value) {
            $this->data[$key] = $value;
        }
        return $this;
    }

    private function getDefaultViewName(){
        $appendedPartPosition = strripos($this->getActionName(),'Action');
        return camelCaseToDashes(substr($this->getActionName(), 0, $appendedPartPosition));
    }

    protected function setTitle($str){
        $this->header[self::HEADER_TITLE_KEY] = $str;
        return $this;
    }

    protected function setKeywords($str){
        $this->header[self::HEADER_KEYWORDS_KEY] = $str;
        return $this;
    }

    protected function setDescription($str){
        $this->header[self::HEADER_DESCRIPTION_KEY] = $str;
        return $this;
    }

    protected function getAdditionalRouteParam($index, $undefinedValue = null){
        if (is_array($this->getRouteParam('additionalParams')) && array_key_exists($index, $this->getRouteParam('additionalParams'))){
            return $this->getRouteParam('additionalParams')[$index];
        }

        return $undefinedValue;
    }

    protected function setView($templateName){
        $this->view = $templateName;
    }

    protected function setLayout($layoutName){
        $this->layout = $layoutName;
    }

    public function __get($name){
        if (array_key_exists($name, $this->data)){
            return $this->data[$name];
        }
        return null;
    }

    public function url($controllerClassName, array $routeParams = [], array $getParams = [])
    {
        $url = $this->staticPath();
        if (!empty($controllerClassName)){
            $shortenedName = str_replace('Controller','',$controllerClassName);
            $url .= camelCaseToDashes($shortenedName) . '/';
        }
        foreach ($routeParams as $routeParam){
            $url .= $routeParam . '/';
        }
        $i = 0;
        foreach ($getParams as $key => $value){
            if ($i == 0){
                $url .= '?';
            } else {
                $url .= '&';
            }
            $url .= $key . '=' . $value;
            $i++;
        }
        return $url;
    }

    public function staticPath($pathToFile = '')
    {
        global $staticPath;
        return $staticPath . $pathToFile;
    }
}