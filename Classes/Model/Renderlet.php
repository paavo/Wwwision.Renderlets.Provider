<?php
declare(strict_types=1);

namespace Wwwision\Renderlets\Provider\Model;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
final class Renderlet
{
    public string $content;
    public CacheId $cacheId;
    public array $httpHeaders;

    private function __construct(string $content, CacheId $cacheId, array $httpHeaders)
    {
        $this->content = $content;
        $this->cacheId = $cacheId;
        $this->httpHeaders = $httpHeaders;
    }

    public static function fromContentCacheIdAndHttpHeaders(string $content, CacheId $cacheId, array $httpHeaders): self
    {
        return new self($content, $cacheId, $httpHeaders);
    }

    /**
     * @throws \JsonException
     */
    public static function fromJson(string $json): self
    {
        $array = \json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!isset($array['content'], $array['cacheId'], $array['httpHeaders'])) {
            throw new \InvalidArgumentException(sprintf('expected array keys "content", "cacheId", "httpHeaders", got; "%s"', implode('", "', array_keys($array))), 1637843730);
        }
        return new self($array['content'], CacheId::fromString($array['cacheId']), $array['httpHeaders']);
    }

    /**
     * @throws \JsonException
     */
    public function toJson(): string
    {
        return \json_encode(get_object_vars($this), JSON_THROW_ON_ERROR);
    }

}
