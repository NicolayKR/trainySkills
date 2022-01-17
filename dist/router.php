<?php declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Controllers\BaseController;
use App\Middleware\BaseMiddleware;
use App\Classes\TypeResult;
use League\Route\Strategy;
use App\Strategy\NewStrategy;
use Laminas\Diactoros\ResponseFactory;

$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);

$router = new League\Route\Router;
$responseFactory = new ResponseFactory();

// map a route
$router->get('/', function (ServerRequestInterface $request): ResponseInterface {
    $response = new Laminas\Diactoros\Response;
    $response->getBody()->write('<h1>Главная страница, World!</h1>');
    return $response;
});

$router->get('/test', [BaseController::class, 'index'])
    ->middleware(new BaseMiddleware());

$router->get('/test2', function (ServerRequestInterface $request) {
    $response = new TypeResult($request);
    $response = $response->getRes();
    return $response;
})->setStrategy(new NewStrategy($responseFactory, $request));

$response = $router->dispatch($request);

// send the response to the browser
(new Laminas\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);