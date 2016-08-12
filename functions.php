<?php

function getConfig()
{
    return parse_ini_file("config/config.ini", true)[APPLICATION_ENV];
}

function getConfigValue($value)
{
    global $config;
    if (!isset($config)) {
        $config = getConfig();
    }
    return array_key_exists($value, $config) ? $config[$value] : '';
}

function staticPath($pathToFile = '')
{
    $staticPath = getConfigValue('staticPath');
    return $staticPath . $pathToFile;
}

function dashesToCamelCase($text)
{
    $veta = str_replace('-', ' ', $text);
    $veta = ucwords($veta);
    $veta = str_replace(' ', '', $veta);
    return $veta;
}

function camelCaseToDashes($str){
    return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $str));
}

function url($controllerClassName, array $routeParams = [], array $getParams = [])
{
    $url = staticPath();
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