<?php

declare(strict_types=1);

namespace App\Strategy;

use JsonSerializable;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use League\Route\Http;
use League\Route\Http\Exception\{MethodNotAllowedException, NotFoundException};
use League\Route\Route;
use League\Route\{ContainerAwareInterface,
    ContainerAwareTrait,
    Strategy\AbstractStrategy,
    Strategy\OptionsHandlerInterface
};
use Psr\Http\Message\{ResponseFactoryInterface, ResponseInterface, ServerRequestInterface};
use Psr\Http\Server\{MiddlewareInterface, RequestHandlerInterface};
use Throwable;


class NewStrategy extends AbstractStrategy implements ContainerAwareInterface, OptionsHandlerInterface
{
    use ContainerAwareTrait;

    protected $responseFactory;
    protected $type;

    public function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public function getMethodNotAllowedDecorator(MethodNotAllowedException $exception): MiddlewareInterface
    {
        return $this->buildJsonResponseMiddleware($exception);
    }

    public function getNotFoundDecorator(NotFoundException $exception): MiddlewareInterface
    {
        return $this->buildJsonResponseMiddleware($exception);
    }

    public function getOptionsCallable(array $methods): callable
    {
        return function () use ($methods): ResponseInterface {
            $options = implode(', ', $methods);
            $response = $this->responseFactory->createResponse();
            $response = $response->withHeader('allow', $options);
            return $response->withHeader('access-control-allow-methods', $options);
        };
    }

    public function getThrowableHandler(): MiddlewareInterface
    {
        return new class ($this->responseFactory->createResponse()) implements MiddlewareInterface {
            protected $response;

            public function __construct(ResponseInterface $response)
            {
                $this->response = $response;
            }

            public function process(
                ServerRequestInterface  $request,
                RequestHandlerInterface $handler
            ): ResponseInterface
            {
                try {
                    return $handler->handle($request);
                } catch (Throwable $exception) {
                    $response = $this->response;

                    if ($exception instanceof Http\Exception) {
                        return $exception->buildJsonResponse($response);
                    }

                    $response->getBody()->write(json_encode([
                        'status_code' => 500,
                        'reason_phrase' => $exception->getMessage(),
                    ]));

                    $response = $response->withAddedHeader('content-type', 'application/json');
                    return $response->withStatus(500, strtok($exception->getMessage(), "\n"));
                }
            }
        };
    }

    public function invokeRouteCallable(Route $route, ServerRequestInterface $request): ResponseInterface
    {
        $controller = $route->getCallable($this->getContainer());
        $response = $controller($request, $route->getVars());
        if (is_array($response) && $this->isJsonSerializable($response)) {
            $response = new JsonResponse($response);
        } else {
            $response = new HtmlResponse($response);
        }
        return $this->decorateResponse($response);
    }

    protected function buildJsonResponseMiddleware(Http\Exception $exception): MiddlewareInterface
    {
        return new class ($this->responseFactory->createResponse(), $exception) implements MiddlewareInterface {
            protected $response;
            protected $exception;

            public function __construct(ResponseInterface $response, Http\Exception $exception)
            {
                $this->response = $response;
                $this->exception = $exception;
            }

            public function process(
                ServerRequestInterface  $request,
                RequestHandlerInterface $handler
            ): ResponseInterface
            {
                return $this->exception->buildJsonResponse($this->response);
            }
        };
    }

    protected function throwThrowableMiddleware(Throwable $error): MiddlewareInterface
    {
        return new class ($error) implements MiddlewareInterface {
            protected $error;

            public function __construct(Throwable $error)
            {
                $this->error = $error;
            }

            public function process(
                ServerRequestInterface  $request,
                RequestHandlerInterface $handler
            ): ResponseInterface
            {
                throw $this->error;
            }
        };
    }

    protected function isJsonSerializable($response): bool
    {
        if ($response instanceof ResponseInterface) {
            return false;
        }

        return (is_array($response) || is_object($response) || $response instanceof JsonSerializable);
    }
}
