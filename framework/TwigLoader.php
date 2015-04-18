<?php namespace Framework;

class TwigLoader
{

    private function __construct()
    {

    }

    public static function make()
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__.'/views');
        return new \Twig_Environment($loader, ['cache' => __DIR__.'/storage']);
    }

}