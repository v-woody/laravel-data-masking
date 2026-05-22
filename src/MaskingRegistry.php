<?php

namespace VWoody\DataMasking;

use Illuminate\Support\Arr;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use VWoody\DataMasking\Attributes\Mask;
use VWoody\DataMasking\Contracts\Masker;
use VWoody\DataMasking\Contracts\MasksFields;
use VWoody\DataMasking\Maskers\StringMasker;

class MaskingRegistry
{
    /** @var array<string, array<string, string>> */
    private array $configRules = [];

    /** @var array<string, Masker> */
    private array $resolvedMaskers = [];

    /** @param array<string, array<string, string>> $configRules */
    public function setConfigRules(array $configRules): void
    {
        $this->configRules = $configRules;
    }

    /**
     * Returns a map of field name => Masker for the given object or class.
     *
     * Priority: PHP Attributes > MasksFields interface > config rules.
     *
     * @return array<string, Masker>
     */
    public function resolveFor(object|string $target): array
    {
        $className = is_string($target) ? $target : $target::class;

        $maskers = [];

        $maskers = array_merge($maskers, $this->resolveFromConfig($className));

        if (is_object($target) && $target instanceof MasksFields) {
            $maskers = array_merge($maskers, $this->resolveFromInterface($target));
        }

        $maskers = array_merge($maskers, $this->resolveFromAttributes($className));

        return $maskers;
    }

    /** @return array<string, Masker> */
    private function resolveFromConfig(string $className): array
    {
        $rules = Arr::get($this->configRules, $className, []);
        $maskers = [];

        foreach ($rules as $field => $maskerClass) {
            $maskers[$field] = $this->resolveMasker($maskerClass);
        }

        return $maskers;
    }

    /** @return array<string, Masker> */
    private function resolveFromInterface(MasksFields $target): array
    {
        $maskers = [];

        foreach ($target->maskedFields() as $fieldDefinition) {
            [$field, $maskerClass] = $this->parseFieldDefinition($fieldDefinition);
            $maskers[$field] = $this->resolveMasker($maskerClass);
        }

        return $maskers;
    }

    /** @return array<string, Masker> */
    private function resolveFromAttributes(string $className): array
    {
        $reflection = new ReflectionClass($className);
        $maskers = [];

        foreach ($reflection->getProperties() as $property) {
            $maskAttribute = $this->findMaskAttribute($property);

            if ($maskAttribute === null) {
                continue;
            }

            $maskers[$property->getName()] = $this->resolveMasker($maskAttribute->masker);
        }

        return $maskers;
    }

    private function findMaskAttribute(ReflectionProperty $property): ?Mask
    {
        $attributes = $property->getAttributes(Mask::class, ReflectionAttribute::IS_INSTANCEOF);

        if (empty($attributes)) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    private function resolveMasker(string $maskerClass): Masker
    {
        if (isset($this->resolvedMaskers[$maskerClass])) {
            return $this->resolvedMaskers[$maskerClass];
        }

        $masker = app($maskerClass);

        $this->resolvedMaskers[$maskerClass] = $masker;

        return $masker;
    }

    /**
     * Returns a map of field name => ['masker' => Masker, 'source' => string] for the given object or class.
     *
     * @return array<string, array{masker: Masker, source: string}>
     */
    public function resolveWithSources(object|string $target): array
    {
        $className = is_string($target) ? $target : $target::class;

        $results = [];

        foreach ($this->resolveFromConfig($className) as $field => $masker) {
            $results[$field] = ['masker' => $masker, 'source' => 'config'];
        }

        if (is_object($target) && $target instanceof MasksFields) {
            foreach ($this->resolveFromInterface($target) as $field => $masker) {
                $results[$field] = ['masker' => $masker, 'source' => 'interface'];
            }
        }

        foreach ($this->resolveFromAttributes($className) as $field => $masker) {
            $results[$field] = ['masker' => $masker, 'source' => 'attribute'];
        }

        return $results;
    }

    /**
     * Parse a field definition which may be "field:MaskerClass" or just "field"
     * falling back to the default string masker.
     *
     * @return array{0: string, 1: string}
     */
    private function parseFieldDefinition(string $fieldDefinition): array
    {
        if (! str_contains($fieldDefinition, ':')) {
            return [$fieldDefinition, StringMasker::class];
        }

        [$field, $maskerClass] = explode(':', $fieldDefinition, 2);

        return [$field, $maskerClass];
    }
}
