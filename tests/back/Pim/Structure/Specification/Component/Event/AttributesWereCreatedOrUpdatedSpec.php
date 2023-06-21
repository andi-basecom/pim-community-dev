<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Event;

use Akeneo\Pim\Structure\Component\Event\AttributesWereCreatedOrUpdated;
use Akeneo\Pim\Structure\Component\Event\AttributeWasCreatedOrUpdated;
use Akeneo\Pim\Structure\Component\Event\Status;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributesWereCreatedOrUpdatedSpec extends ObjectBehavior
{
    function it_is_traversable()
    {
        $date = new \DateTimeImmutable();
        $this->beConstructedWith([
            new AttributeWasCreatedOrUpdated(1, 'name', $date, Status::Created),
            new AttributeWasCreatedOrUpdated(2, 'desc', $date, Status::Updated),
        ]);

        $this->shouldImplement(\Traversable::class);
    }

    function it_cannot_be_constructed_with_wrong_item()
    {
        $this->beConstructedWith([
            new AttributeWasCreatedOrUpdated(1, 'name', new \DateTimeImmutable(), Status::Created),
            new \stdClass(),
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_normalized()
    {
        $date = new \DateTimeImmutable();
        $this->beConstructedWith([
            new AttributeWasCreatedOrUpdated(1, 'name', $date, Status::Created),
            new AttributeWasCreatedOrUpdated(2, 'desc', $date, Status::Updated),
        ]);

        $this->normalize()->shouldReturn([
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

    function it_can_be_denormalized()
    {
        // Fake construct is needed for phpspec even if we don't use it
        $date = new \DateTimeImmutable();
        $this->beConstructedWith([new AttributeWasCreatedOrUpdated(1, 'name', $date, Status::Created)]);

        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $denormalize = [
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
        ];

        $this::denormalize($denormalize)->shouldBeLike(new AttributesWereCreatedOrUpdated([
            new AttributeWasCreatedOrUpdated(1, 'name', $date, Status::Created),
            new AttributeWasCreatedOrUpdated(2, 'desc', $date, Status::Updated),
        ]));
    }

    function it_returns_the_older_date()
    {
        $date1 = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $date2 = new \DateTimeImmutable('2020-11-22T22:02:12+00:00');
        $date3 = new \DateTimeImmutable('2020-11-28T22:02:12+00:00');
        $this->beConstructedWith([
            new AttributeWasCreatedOrUpdated(1, 'name', $date1, Status::Created),
            new AttributeWasCreatedOrUpdated(2, 'desc', $date2, Status::Updated),
            new AttributeWasCreatedOrUpdated(3, 'author', $date3, Status::Updated),
        ]);

        $this->getOlderEventDate()->shouldReturn($date2);
    }
}
