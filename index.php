<?php

session_start();

use App\Controllers\ArticleControllers;
use App\Controllers\MainPage;
use App\Controllers\UserControllers;
use App\Redirect;
use App\Views\View;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

require_once 'vendor/autoload.php';


$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', [MainPage::class, 'main']);

    $r->addRoute('GET', '/articles', [ArticleControllers::class, 'index']);
    $r->addRoute('GET', '/articles/{id:\d+}', [ArticleControllers::class, 'show']);

    $r->addRoute('POST', '/articles', [ArticleControllers::class, 'store']);
    $r->addRoute('GET', '/articles/create', [ArticleControllers::class, 'create']);

    $r->addRoute('POST', '/articles/{id:\d+}/delete', [ArticleControllers::class, 'delete']);

    $r->addRoute('GET', '/articles/{id:\d+}/edit', [ArticleControllers::class, 'edit']);
    $r->addRoute('POST', '/articles/{id:\d+}', [ArticleControllers::class, 'update']);

    $r->addRoute('GET', '/users/signUp', [UserControllers::class, 'signUp']);
    $r->addRoute('POST', '/users/register', [UserControllers::class, 'register']);


    $r->addRoute('GET', '/users', [UserControllers::class, 'login']);
    $r->addRoute('POST', '/users/signIn', [UserControllers::class, 'signIn']);

    $r->addRoute('GET', '/logout', [UserControllers::class, 'logout']);

    $r->addRoute('GET', '/users/message', [UserControllers::class, 'error']);
});

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        break;
    case FastRoute\Dispatcher::FOUND:
        $controller = $routeInfo[1][0];
        $method = $routeInfo[1][1];

        /** @var View $response */
        $response = (new $controller)->$method($routeInfo[2]);

        $twig = new Environment(new FilesystemLoader('app/Views'));

        if ($response instanceof View) {
            try {
                echo $twig->render($response->getPath() . '.html', $response->getVariables());
            } catch (LoaderError|RuntimeError|SyntaxError $e) {
            }
        }

        if ($response instanceof Redirect) {
            header('Location: ' . $response->getLocation());
            exit;
        }
        break;
}

