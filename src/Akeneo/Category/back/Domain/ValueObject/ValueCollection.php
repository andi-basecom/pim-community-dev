<?php

namespace Akeneo\Category\Domain\ValueObject;

use Akeneo\Category\Application\Converter\Checker\AttributeRequirementChecker;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @implements \IteratorAggregate<int, ValueCollection>
 */
final class ValueCollection implements \IteratorAggregate, \Countable
{
    public const SEPARATOR = '|';

    // @phpstan-ignore-next-line
    private function __construct(private ?array $values)
    {
        AttributeRequirementChecker::checkAttributes($values);
    }

    // @phpstan-ignore-next-line
    public static function fromArray(array $values): self
    {
        return new self($values);
    }

    // @phpstan-ignore-next-line
    public function getAttributeTextData(string $attributeCode, string $attributeIdentifier, string $localeCode): ?array
    {
        return $this->values[sprintf(
            '%s'.self::SEPARATOR.'%s'.self::SEPARATOR.'%s',
            $attributeCode,
            $attributeIdentifier,
            $localeCode,
        )];
    }

    // @phpstan-ignore-next-line
    public function getAttributeData(string $attributeCode, string $attributeIdentifier): ?array
    {
        return $this->values[sprintf('%s'.self::SEPARATOR.'%s', $attributeCode, $attributeIdentifier)];
    }

    /**
     * Set a value in value collection. If value already exist, update it.
     */
    public function setValue(
        string $attributeUuid,
        string $attributeCode,
        ?string $localeCode,
        mixed $value,
    ): ValueCollection {
        $compositeKey = $attributeCode.self::SEPARATOR.$attributeUuid;

        $localCompositeKey = sprintf(
            '%s%s%s',
            $attributeCode,
            self::SEPARATOR.$attributeUuid,
            isset($localeCode) ? self::SEPARATOR.$localeCode : '',
        );

        $this->values['attribute_codes'][] = $compositeKey;
        $this->values['attribute_codes'] = array_unique($this->values['attribute_codes']);

        $this->values[$localCompositeKey] = [
            'data' => $value,
            'locale' => $localeCode,
            'attribute_code' => $attributeCode.self::SEPARATOR.$attributeUuid,
        ];

        return new self($this->values);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function normalize(): array
    {
        return $this->values ?? [];
    }

    /**
     * @return \ArrayIterator<int, ValueCollection>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->values);
    }

    public function count()
    {
        return count($this->values);
    }
}
