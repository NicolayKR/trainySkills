<?php declare(strict_types=1);

namespace App\Controllers;


use DI\Container;
use Psr\Http\Message\ServerRequestInterface;
use App\Classes\TypeResult;

class BaseController
{

    public function index(ServerRequestInterface $request)
    {
        $container = new Container();
        $container->set(ServerRequestInterface::class, $request);
        $response = $container->get(TypeResult::class);
        $response = $response->getRes();
        return $response;
    }
}

