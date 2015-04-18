<?php
require __DIR__.'/../vendor/autoload.php';

$app = new Framework\Application();

$app->addRoute('GET', '/', 'HomeController@index');

$app->addRoute('GET', '/user/{name}', function($name) use ($app)
{
   // $user = Framework\Models\User::find($id);
    $string = $app->container->make('Twig_Environment')->render('index.html', ['name' => $name]);
    return $string;
});

$app->addRoute('GET', '/{name}', 'HomeController@welcome');

$app->run();