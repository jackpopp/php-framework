<?php namespace Framework;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
* 
*/
class DatabaseConnection
{
    public function __construct()
    {
        /**
         * Configure the database and boot Eloquent
         */
        $capsule = new Capsule;

        $capsule->addConnection(array(
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'test',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_general_ci',
            'prefix'    => ''
        ));

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }
}