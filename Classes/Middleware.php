<?php
declare(strict_types=1);

namespace Cornelsen\Renderlets\Provider;

use Cornelsen\Renderlets\Provider\Exception\FailedToFindRenderletDefinition;
use Cornelsen\Renderlets\Provider\Exception\InvalidRenderletId;
use Cornelsen\Renderlets\Provider\Exception\MissingRenderletParameter;
use Cornelsen\Renderlets\Provider\Exception\UnknownRenderletParameters;
use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Psr7\Response;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Security\Context;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use function GuzzleHttp\Psr7\parse_query;

final class Middleware implements MiddlewareInterface
{

    private Renderer $renderer;
    private Context $securityContext;

    private const URL_PATH = '/__renderlet';

    public function __construct(Renderer $renderer, Context $securityContext)
    {
        $this->renderer = $renderer;
        $this->securityContext = $securityContext;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        if ($request->getUri()->getPath() !== self::URL_PATH) {
            return $next->handle($request);
        }
        parse_str($request->getUri()->getQuery(), $queryParameters);
        if (!isset($queryParameters['id'])) {
            return new Response(406, [], 'Missing "id" query parameter');
        }
        $fakeActionRequest = ActionRequest::fromHttpRequest($request);
        $this->securityContext->setRequest($fakeActionRequest);
        $renderletId = RenderletId::fromString($queryParameters['id']);
        try {
            $renderlet = $this->renderer->render($request, $renderletId, $queryParameters['parameters'] ?? []);
        } catch (InvalidRenderletId $exception) {
            return new Response(404, [], 'Unknown renderlet id');
        } catch (MissingRenderletParameter | UnknownRenderletParameters $exception) {
            return new Response(400, [], $exception->getMessage());
        }
        if ($request->hasHeader('If-None-Match') && $request->getHeaderLine('If-None-Match') === $renderlet->cacheId->toString()) {
            return new Response(304);
        }
        return new Response(200, ['Content-Type' => $renderlet->contentType, 'ETag' => $renderlet->cacheId->toString()], $renderlet->content);
    }
}
