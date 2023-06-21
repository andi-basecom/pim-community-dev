<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Event;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributesWereCreatedOrUpdated implements \IteratorAggregate
{
    /**
     * @param AttributeWasCreatedOrUpdated[] $attributeWasCreatedOrUpdatedList
     */
    public function __construct(public readonly array $attributeWasCreatedOrUpdatedList)
    {
        Assert::notEmpty($attributeWasCreatedOrUpdatedList);
        Assert::allIsInstanceOf($attributeWasCreatedOrUpdatedList, AttributeWasCreatedOrUpdated::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->attributeWasCreatedOrUpdatedList);
    }

    public function normalize(): array
    {
        return \array_map(
            static fn (AttributeWasCreatedOrUpdated $attributeWasCreatedOrUpdated): array => $attributeWasCreatedOrUpdated->normalize(),
            $this->attributeWasCreatedOrUpdatedList
        );
    }

    public static function denormalize(array $normalized): AttributesWereCreatedOrUpdated
    {
        Assert::allIsArray($normalized);

        return new AttributesWereCreatedOrUpdated(
            \array_map(
                static fn (array $itemNormalized): AttributeWasCreatedOrUpdated => AttributeWasCreatedOrUpdated::denormalize($itemNormalized),
                $normalized
            )
        );
    }

    public function getOlderEventDate(): \DateTimeImmutable
    {
        $minDate = current($this->attributeWasCreatedOrUpdatedList)?->date;
        Assert::notNull($minDate);
        foreach ($this->attributeWasCreatedOrUpdatedList as $attributeWasCreatedOrUpdated) {
            if (null === $minDate || $minDate > $attributeWasCreatedOrUpdated->date) {
                $minDate = $attributeWasCreatedOrUpdated->date;
            }
        }

        return $minDate;
    }
}
