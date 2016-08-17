<?php

function getConfig()
{
    if (!defined("APPLICATION_PATH") || !defined("APPLICATION_ENV")){
        throw new Exception("APPLICATION_PATH and APPLICATION_ENV have to be defined to process configuration");
    }

    return parse_ini_file(APPLICATION_PATH . "/config/config.ini", true)[APPLICATION_ENV];
}

function getConfigValue($value, $undefinedValue = null)
{
    global $config;
    if (!isset($config)) {
        $config = getConfig();
    }
    return array_key_exists($value, $config) ? $config[$value] : $undefinedValue;
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