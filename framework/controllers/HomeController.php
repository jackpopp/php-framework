<?php namespace Framework\Controllers;

use Twig_Environment;

class HomeController 
{
    protected $twig;

    public function __construct(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }  

    function index()
    {
        return 'index';
    } 

    function welcome($name)
    {
        return $this->twig->render('index.html', ['name' => $name]);
    }
}