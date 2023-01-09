<?php
declare(strict_types=1);

namespace Wwwision\Renderlets\Provider\Model;

use Neos\Flow\Annotations as Flow;
use Wwwision\Renderlets\Provider\Model\CacheId;

/**
 * @Flow\Proxy(false)
 */
final class Renderlet
{
    public string $content;
    public CacheId $cacheId;
    public string $contentType;

    private function __construct(string $content, CacheId $cacheId, string $contentType)
    {
        $this->content = $content;
        $this->cacheId = $cacheId;
        $this->contentType = $contentType;
    }

    public static function fromContentCacheIdAndContentType(string $content, CacheId $cacheId, string $contentType): self
    {
        return new self($content, $cacheId, $contentType);
    }

    /**
     * @throws \JsonException
     */
    public static function fromJson(string $json): self
    {
        $array = \json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        if (!isset($array['content'], $array['cacheId'], $array['contentType'])) {
            throw new \InvalidArgumentException(sprintf('expected array keys "content", "cacheId", "contentType", got; "%s"', implode('", "', array_keys($array))), 1637843730);
        }
        return new self($array['content'], CacheId::fromString($array['cacheId']), $array['contentType']);
    }

    /**
     * @throws \JsonException
     */
    public function toJson(): string
    {
        return \json_encode(get_object_vars($this), JSON_THROW_ON_ERROR);
    }

}
