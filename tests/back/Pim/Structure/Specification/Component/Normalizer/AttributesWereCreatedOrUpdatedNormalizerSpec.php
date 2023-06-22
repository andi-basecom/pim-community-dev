<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Normalizer;

use Akeneo\Pim\Structure\Component\Event\AttributesWereCreatedOrUpdated;
use Akeneo\Pim\Structure\Component\Event\AttributeWasCreatedOrUpdated;
use Akeneo\Pim\Structure\Component\Event\Status;
use Akeneo\Pim\Structure\Component\Normalizer\AttributesWereCreatedOrUpdatedNormalizer;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributesWereCreatedOrUpdatedNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer_and_a_denormalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
        $this->shouldImplement(DenormalizerInterface::class);
        $this->shouldHaveType(AttributesWereCreatedOrUpdatedNormalizer::class);
    }

    function it_supports_only_attributes_were_created_or_updated_for_normalization()
    {
        $date = new \DateTimeImmutable();
        $attributesWereCreatedOrUpdated = new AttributesWereCreatedOrUpdated([
            new AttributeWasCreatedOrUpdated(1, 'name', $date, Status::Created),
            new AttributeWasCreatedOrUpdated(2, 'desc', $date, Status::Updated),
        ]);

        $this->supportsNormalization($attributesWereCreatedOrUpdated)->shouldReturn(true);
        $this->supportsNormalization(new \stdClass())->shouldReturn(false);
    }

    function it_normalizes_an_object()
    {
        $date = new \DateTimeImmutable();
        $attributesWereCreatedOrUpdated = new AttributesWereCreatedOrUpdated([
            new AttributeWasCreatedOrUpdated(1, 'name', $date, Status::Created),
            new AttributeWasCreatedOrUpdated(2, 'desc', $date, Status::Updated),
        ]);

        $this->normalize($attributesWereCreatedOrUpdated)->shouldReturn([
            [
                'id' => 1,
                'code' => 'name',
                'date' => $date->format(\DateTimeInterface::ATOM),
                'status' => 'created',
            ],
            [
                'id' => 2,
                'code' => 'desc',
                'date' => $date->format(\DateTimeInterface::ATOM),
                'status' => 'updated',
            ],
        ]);
    }

    function it_supports_only_attributes_were_created_or_updated_for_denormalization()
    {
        $date = new \DateTimeImmutable();
        $attributesWereCreatedOrUpdated = new AttributesWereCreatedOrUpdated([
            new AttributeWasCreatedOrUpdated(1, 'name', $date, Status::Created),
            new AttributeWasCreatedOrUpdated(2, 'desc', $date, Status::Updated),
        ]);

        $this->supportsDenormalization([], AttributesWereCreatedOrUpdated::class)->shouldReturn(true);
        $this->supportsDenormalization([], \stdClass::class)->shouldReturn(false);
    }

    function it_denormalizes()
    {
        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $attributesWereCreatedOrUpdated = new AttributesWereCreatedOrUpdated([
            new AttributeWasCreatedOrUpdated(1, 'name', $date, Status::Created),
            new AttributeWasCreatedOrUpdated(2, 'desc', $date, Status::Updated),
        ]);

        $this->denormalize([
            [
                'id' => 1,
                'code' => 'name',
                'date' => $date->format(\DateTimeInterface::ATOM),
                'status' => 'created',
            ],
            [
                'id' => 2,
                'code' => 'desc',
                'date' => $date->format(\DateTimeInterface::ATOM),
                'status' => 'updated',
            ],
        ], AttributesWereCreatedOrUpdated::class)->shouldBeLike($attributesWereCreatedOrUpdated);
    }
}
