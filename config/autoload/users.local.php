<?php
return array(
    // //Place it into main config folder ////
    'settings' => array(
        'BASE_URL' => 'http://projzf2', //http://example.com
        "EMAIL" => array(
            "SMTP_SENDER_TYPE" => "user",
            "SMTP_NAME" => "72.29.66.163",
            "SMTP_HOST" => "72.29.66.163",
            "SMTP_PORT" => "26",
            "SMTP_CONNECTION_CLASS" => "plain",
            "SMTP_USERNAME" => "contato@mscriacoes.com.br",
            "SMTP_PASSWORD" => "m2a6r1c9",
            "SMTP_SSL" => "26",
            "BODY" => "Hello there!",
            "FROM" => "from@url.com",
            "TO" => "to@url.com",
            "MAIL_FROM_NICK_NAME" => "Nome",
            "SUBJECT" => "Assunto",
            "FROM_NICK_NAME" => "Nom de novo",
            'UPDATE_EMAIL_TEMPLATE' => true
        ),
        'FORGOT_PASSWORD_SUBJECT' => 'Email de redefinição de senha'
    ),
    'afterLoginURL' => 'maff', //Change it where your application redirects after login
    'controllers' => array(
        'invokables' => array(
            'Users\Controller\User' => 'Users\Controller\UserController',
            'Grupos\Controller\Index' => 'Grupos\Controller\IndexController',
        )
    ),
    'whitelist' => array(
        '/users',
        '/users/index',
        '/users/logout',
        '/users/forgot-password',
        '/users/reset-password',
        '/users/password-reset-confirmation',
    ),
    
    'db' => array(
        'driver' => 'Pdo',
        'dsn'            => 'mysql:dbname=maff_zf;hostname=localhost',
        'username'       => 'root',
        'password'       => '',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter' => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    ),
);
