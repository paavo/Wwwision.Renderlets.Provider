<?php
declare(strict_types=1);

namespace Cornelsen\Renderlets\Provider;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
final class CacheId implements \JsonSerializable
{

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return sprintf('Cache ID "%s"', $this->value);
    }


}
