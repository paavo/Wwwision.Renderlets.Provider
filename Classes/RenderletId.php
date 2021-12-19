<?php
declare(strict_types=1);

namespace Cornelsen\Renderlets\Provider;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
final class RenderletId implements \JsonSerializable
{

    private const PATTERN = '/^[a-z][a-z_0-9]*$/';

    private string $value;

    private function __construct(string $value)
    {
        if (preg_match(self::PATTERN, $value) !== 1) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid renderlet id (RegEx pattern: %s)', $value, self::PATTERN), 1637842424);
        }
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
        return sprintf('Renderlet ID "%s"', $this->value);
    }
}
