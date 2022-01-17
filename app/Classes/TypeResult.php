<?php

namespace App\Classes;

use App\Classes\TestClass;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;

class TypeResult
{
    private $result;

    public function __construct(ServerRequestInterface $request)
    {
        $this->result = $request;
    }

    public function getRes()
    {
        if (count($this->result->getQueryParams()) > 1) {
            $response = $this->result->getQueryParams();
        } else {
            $response = '<h1>Hello,' . print_r($this->result->getQueryParams()['name'], true) .
                '!</h1><p><pre>' . TestClass::clientCode(new ConcreteFactory1()) . '</pre></p>';
        }
        return $response;
    }
}

//interface Result
//{
//    public function getType($data);
//}
//
//class getArray implements ResponseInterface
//{
//    public function getType($data)
//    {
//        $response = new JsonResponse(
//            $data->getQueryParams(),
//            200,
//            ['Content-Type' => ['application/hal+json']]
//        );
//        return $response;
//    }
//}
//
//class getString implements ResponseInterface
//{
//    public function getType($data)
//    {
//        $res = '<h1>Hello,' . print_r($data->getQueryParams()['name'], true) .
//            '!</h1><p><pre>' . TestClass::clientCode(new ConcreteFactory1()) . '</pre></p>';
//        $response = new HtmlResponse(
//            $res,
//            200,
//            ['Content-Type' => ['application/xhtml+xml']]
//        );
//        return $response;
//    }
//}
//
//class TypeResult
//{
//
//    private $result;
//
//    public function __construct(ServerRequestInterface $request)
//    {
//        $this->result = $request;
//    }
//
//    public function getRes()
//    {
//        if (count($this->result->getQueryParams()) > 1) {
//            $data = new getArray();
//        } else {
//            $data = new getString();
//        }
//        $data->getType($this->result);
//        return $data;
//    }
//}
