<?php
declare(strict_types=1);

namespace Cornelsen\Renderlets\Provider;

use Cornelsen\Renderlets\Provider\Exception\MissingRenderletParameter;
use Cornelsen\Renderlets\Provider\Exception\UnknownRenderletParameters;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

final class RenderletImplementation extends AbstractFusionObject
{
    private function cacheId(): CacheId
    {
        return CacheId::fromString($this->fusionValue('cacheId'));
    }

    private function parameters(): array
    {
        return $this->fusionValue('parameters');
    }

    private function contentType(): string
    {
        return $this->fusionValue('contentType');
    }

    public function evaluate(): string
    {
        $context = $this->runtime->getCurrentContext();
        $parameters = $context['parameters'];
        foreach ($this->parameters() as $parameterName => $required) {
            if ($required && empty($context['parameters'][$parameterName])) {
                throw new MissingRenderletParameter(sprintf('Missing/empty parameter "%s"', $parameterName), 1637760230);
            }
            unset($parameters[$parameterName]);
        }
        if ($parameters !== []) {
            throw new UnknownRenderletParameters(sprintf('Unknown parameter(s) "%s"', implode('", "', array_keys($parameters))), 1637831451);
        }
        $this->runtime->pushContextArray($context);
        $content = $this->runtime->render($this->path . '/renderer');
        $this->runtime->popContext();
        return Renderlet::fromContentCacheIdAndContentType(
            $content,
            $this->cacheId(),
            $this->contentType()
        )->toJson();
    }
}
