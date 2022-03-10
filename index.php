<?php


use App\Controllers\ArticleControllers;
use App\Controllers\CommentController;
use App\Controllers\FriendsController;
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

session_start();

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/', [ArticleControllers::class, 'index']);
    $r->addRoute('GET', '/articles/{id:\d+}', [ArticleControllers::class, 'show']);

    $r->addRoute('POST', '/articles', [ArticleControllers::class, 'store']);
    $r->addRoute('GET', '/articles/create', [ArticleControllers::class, 'create']);

    $r->addRoute('POST', '/articles/{id:\d+}/delete', [ArticleControllers::class, 'delete']);

    $r->addRoute('GET', '/articles/{id:\d+}/edit', [ArticleControllers::class, 'edit']);
    $r->addRoute('POST', '/articles/{id:\d+}', [ArticleControllers::class, 'update']);

    $r->addRoute('POST', '/articles/{id:\d+}/likes', [ArticleControllers::class, 'likes']);

    $r->addRoute('POST', '/articles/{id:\d+}/addComment', [CommentController::class, 'addComment']);
    $r->addRoute('POST', '/comment/{id:\d+}/delete', [CommentController::class, 'deleteComment']);

    $r->addRoute('GET', '/users/signUp', [UserControllers::class, 'signUp']);
    $r->addRoute('POST', '/users/register', [UserControllers::class, 'register']);

    $r->addRoute('GET', '/users', [UserControllers::class, 'login']);
    $r->addRoute('POST', '/users/signIn', [UserControllers::class, 'signIn']);

    $r->addRoute('GET', '/logout', [UserControllers::class, 'logout']);
    $r->addRoute('GET', '/users/message', [UserControllers::class, 'error']);

    $r->addRoute('GET', '/findFriends', [FriendsController::class, 'findFriends']);
    $r->addRoute('GET', '/showFriends', [FriendsController::class, 'showFriends']);

    $r->addRoute('POST', '/invite/{id:\d+}', [FriendsController::class, 'inviteFriend']);
    $r->addRoute('POST', '/reject/{id:\d+}', [FriendsController::class, 'rejectFriend']);
    $r->addRoute('POST', '/accept/{id:\d+}', [FriendsController::class, 'acceptFriend']);

    $r->addRoute('POST', '/end/{id:\d+}', [FriendsController::class, 'endFriendship']);

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
                echo $twig->render($response->getPath() . '.twig', $response->getVariables());
            } catch (LoaderError|RuntimeError|SyntaxError $e) {
            }
        }

        if ($response instanceof Redirect) {
            header('Location: ' . $response->getLocation());
            exit;
        }
        break;
}

if(isset($_SESSION["errors"])){
    unset($_SESSION["errors"]);
}

if(isset($_SESSION["inputs"])){
    unset($_SESSION["inputs"]);
}

