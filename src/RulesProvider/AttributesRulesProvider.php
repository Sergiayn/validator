<?php

declare(strict_types=1);

namespace Yiisoft\Validator\RulesProvider;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;
use Yiisoft\Validator\RuleInterface;
use Yiisoft\Validator\RulesProviderInterface;

use function is_object;

final class AttributesRulesProvider implements RulesProviderInterface
{
    /**
     * @var array<RuleInterface[]>|null
     */
    private ?array $rules = null;

    public function __construct(
        /**
         * @param class-string|object $class
         */
        private string|object $source,
        private int $propertyVisibility = ReflectionProperty::IS_PRIVATE | ReflectionProperty::IS_PROTECTED | ReflectionProperty::IS_PUBLIC
    ) {
    }

    /**
     * @return array<RuleInterface[]>
     */
    public function getRules(): array
    {
        if ($this->rules === null) {
            $this->rules = $this->parseRules();
        }
        return $this->rules;
    }

    /**
     * @return array<RuleInterface[]>
     */
    private function parseRules(): array
    {
        $rules = [];

        $reflection = is_object($this->source)
            ? new ReflectionObject($this->source)
            : new ReflectionClass($this->source);
        foreach ($reflection->getProperties($this->propertyVisibility) as $property) {
            $attributes = $property->getAttributes(RuleInterface::class, ReflectionAttribute::IS_INSTANCEOF);
            foreach ($attributes as $attribute) {
                $rules[$property->getName()][] = $attribute->newInstance();
            }
        }

        return $rules;
    }
}
