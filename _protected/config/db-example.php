<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=localhost;dbname=proper_spd', 
    'username' => 'postgres',
    'password' => '',
    'charset' => 'utf8',
    'schemaMap' => [
      'pgsql'=> [
        'class'=>'yii\db\pgsql\Schema',
        'defaultSchema' => 'public' //specify your schema here
      ]
    ], // PostgreSQL
];