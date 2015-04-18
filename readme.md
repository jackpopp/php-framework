### MVC PHP Framework
#### Demo Project show how to create a framework from different PHP components

Routing with FastRoute

Templating with Twig

Active Record ORM with Eloquent

Service Container with Illuminate\Container

```php
<?php
require __DIR__.'/../vendor/autoload.php';

$app = new Framework\Application();

$app->addRoute('GET', '/', 'HomeController@index');

$app->addRoute('GET', '/welcome/{name}', function($name) use ($app)
{
    $string = $app->container->make('Twig_Environment')->render('index.html', ['name' => $name]);
    return $string;
});

$app->addRoute('GET', '/{name}', 'HomeController@welcome');

$app->run();
```