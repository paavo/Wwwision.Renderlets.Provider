<?php
declare(strict_types=1);

namespace Wwwision\Renderlets\Provider\Fusion;

use Neos\Flow\Annotations as Flow;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Neos\Domain\Exception;
use Neos\Neos\Service\LinkingService;

final class ConvertUrisRecursivelyImplementation extends AbstractFusionObject
{

    /**
     * @Flow\Inject
     * @var LinkingService
     */
    protected $linkingService;

    /**
     * @return mixed
     */
    public function evaluate()
    {
        $value = $this->fusionValue('value');
        if (empty($value)) {
            return $value;
        }
        if (\is_array($value)) {
            array_walk_recursive($value, function(&$value) {
                $value = \is_string($value) ? $this->convertUris($value) : $value;
            });
            return $value;
        }
        if (!\is_string($value)) {
            throw new Exception(sprintf('Only strings and arrays can be processed by this Fusion object, given: "%s".', \gettype($value)), 1637670551);
        }
        return $this->convertUris($value);
    }

    private function convertUris(string $subject): string
    {
        return preg_replace_callback(LinkingService::PATTERN_SUPPORTED_URIS, function (array $matches) {
            switch ($matches[1]) {
                case 'node':
                    $this->runtime->addCacheTag('node', $matches[2]);
                    return $this->linkingService->resolveNodeUri($matches[0], $this->fusionValue('node'), $this->runtime->getControllerContext(), true);
                case 'asset':
                    $this->runtime->addCacheTag('asset', $matches[2]);
                    return $this->linkingService->resolveAssetUri($matches[0]);
                default:
                    return '';
            }
        }, $subject);
    }
}
