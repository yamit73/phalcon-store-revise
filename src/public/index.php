<?php
use Phalcon\Di\FactoryDefault;
//Required class for loader
use Phalcon\Loader;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Url;
/**
 * Required classes for DB
 */
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Config;
use Phalcon\Config\ConfigFactory;
/**
 * Required classes for session
 */
use Phalcon\Session\Manager;
use Phalcon\Session\Adapter\Stream;
/**
 * Required classes for Event
 */
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
/**
 * Required classes for logger
 */
use Phalcon\Logger;
/**
 * Required classes for cache
 */
use Phalcon\Cache;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Storage\SerializerFactory;

$config = new Config([]);

// Define some absolute path constants to aid in locating resources
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
require_once(BASE_PATH.'/vendor/autoload.php');

// Register an autoloader
$loader = new Loader();
/**
 * Registering controllers and models dir
 */
$loader->registerDirs(
    [
        APP_PATH . "/controllers/",
        APP_PATH . "/models/",
    ]
);

/**
 * Registering namespaces of files
 */
$loader->registerNamespaces(
    [
        'App\Components' => APP_PATH.'/components',
        'App\Notification' => APP_PATH.'/notification'
    ]
);
$loader->register();

$container = new FactoryDefault();

/**
 * Di container for view
 */
$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(APP_PATH . '/views/');
        return $view;
    }
);
/**
 * Url container
 */
$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);

/**
 * Container for config 
 * Contains neccessory variables
 */
$container->set(
    'config',
    function () {
        $file='../app/config/config.php';
        $factory=new ConfigFactory();
        return $factory->newInstance('php', $file);
    }
);

/**
 * Di container
 * Session
 * Shared
 */
$container->setShared(
    'session',
    function () {
        $session = new Manager();
        $files = new Stream(
            [
                'savePath' => '/tmp',
            ]
        );
        $session->setAdapter($files);
        $session->start();
        return $session;
    }
);
/**
 * Cache container
 */
$container->setShared(
    'cache',
    function () {
        $serializerFactory = new SerializerFactory();
        $adapterFactory    = new AdapterFactory($serializerFactory);
        $options = [
            'defaultSerializer' => 'Json',
            'lifetime'          => 7200
        ];
        $adapter = $adapterFactory->newInstance('apcu', $options);
        $cache=new Cache($adapter);
        return $cache;
    }
);

/**
 * Locale container
 * Used to language translation
 */
$container->set(
    'locale',
    (new \App\Components\Locale())->getTranslator()
);
/**
 * DB container
 */

$container->set(
    'db',
    function () {
        $config=$this->get('config')->db;
        return new Mysql(
            [
                'host'     => $config->host,
                'username' => $config->username,
                'password' => $config->password,
                'dbname'   => $config->dbname,
            ]
        );
    }
);
//Creating object of application class
$application = new Application($container);

//Event manager-----start-----
$eventsManager=new EventsManager();
$eventsManager->attach(
    'notification',
    new App\Notification\NotificationListener()
);
$application->setEventsManager($eventsManager);
$eventsManager->attach(
    'application:beforeHandleRequest',
    new App\Notification\NotificationListener()
);
$container->set(
    'EventsManager',
    $eventsManager
);
//Event manager-------end------

/**
 * Helper services
 */
$container->set(
    'helper',
    function () {
        return new \App\Components\Helper();
    }
);
//container for signup logger
$container->set(
    'signupLogger',
    function () {
        $adapter = new \Phalcon\Logger\Adapter\Stream(APP_PATH.'/logs/SignUp.log');
        $logger  = new Logger(
            'messages',
            [
                'main' => $adapter,
            ]
        );

        return $logger;
    }
);
//container for login logger
$container->set(
    'loginLogger',
    function () {
        $adapter = new \Phalcon\Logger\Adapter\Stream(APP_PATH.'/logs/Login.log');
        $logger  = new Logger(
            'messages',
            [
                'main' => $adapter,
            ]
        );

        return $logger;
    }
);
try {
    // Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );

    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}
