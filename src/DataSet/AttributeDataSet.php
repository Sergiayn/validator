<?php

declare(strict_types=1);

namespace Yiisoft\Validator\DataSet;

use ReflectionAttribute;
use ReflectionClass;
use Yiisoft\Validator\DataSetInterface;
use Yiisoft\Validator\RuleInterface;
use Yiisoft\Validator\RulesProviderInterface;

/**
 * This data set makes use of attributes introduced in PHP 8. It simplifies rules configuration process, especially for
 * nested data and relations. Please refer to the guide for example.
 *
 * @link https://www.php.net/manual/en/language.attributes.overview.php
 */
final class AttributeDataSet implements RulesProviderInterface, DataSetInterface
{
    use ArrayDataTrait;

    private object $baseAnnotatedObject;

    public function __construct(object $baseAnnotatedObject, array $data = [])
    {
        $this->baseAnnotatedObject = $baseAnnotatedObject;
        $this->data = $data;
    }

    public function getRules(): iterable
    {
        $classMeta = new ReflectionClass($this->baseAnnotatedObject);

        return $this->collectAttributes($classMeta);
    }

    private function collectAttributes(ReflectionClass $classMeta): iterable
    {
        $reflectionProperties = $classMeta->getProperties();
        if ($reflectionProperties === []) {
            return [];
        }

        foreach ($reflectionProperties as $property) {
            $attributes = $property->getAttributes(RuleInterface::class, ReflectionAttribute::IS_INSTANCEOF);
            if ($attributes === []) {
                continue;
            }

            yield $property->getName() => $this->createAttributes($attributes);
        }
    }

    /**
     * @param ReflectionAttribute[] $attributes
     *
     * @return iterable
     */
    private function createAttributes(array $attributes): iterable
    {
        foreach ($attributes as $attribute) {
            yield $attribute->newInstance();
        }
    }
}
