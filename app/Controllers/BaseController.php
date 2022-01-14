<?php declare(strict_types=1);

namespace App\Controllers;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response;
use App\Classes\ConcreteFactory1;
use App\Classes\TestClass;


class BaseController
{

    public function index(ServerRequestInterface $request)
    {
        $response = new Response;
        $response->getBody()->write(
            '<h1>Hello,' . print_r($request->getQueryParams()['name'], true) .
            '!</h1><p><pre>' . TestClass::clientCode(new ConcreteFactory1()) . '</pre></p>');
        return $response;
    }
}