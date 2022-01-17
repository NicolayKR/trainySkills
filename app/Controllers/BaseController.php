<?php declare(strict_types=1);

namespace App\Controllers;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;
use App\Classes\ConcreteFactory1;
use App\Classes\TestClass;
use App\Classes\TypeResult;

class BaseController
{

    public function index(ServerRequestInterface $request)
    {
//        $builder = new \DI\ContainerBuilder();
//        $container = $builder->build();
        $response = new TypeResult($request);
        $response = $response->getRes();
//        $response = $container->get('TypeResult');
        return $response;
    }
}

