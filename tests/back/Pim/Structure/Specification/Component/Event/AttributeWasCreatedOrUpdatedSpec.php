<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Structure\Component\Event;

use Akeneo\Pim\Structure\Component\Event\AttributeWasCreatedOrUpdated;
use Akeneo\Pim\Structure\Component\Event\Status;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeWasCreatedOrUpdatedSpec extends ObjectBehavior
{
    function it_cannot_be_created_with_empty_code()
    {
        $this->beConstructedWith(10, '', new \DateTimeImmutable(), Status::Updated);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_normalized()
    {
        $date = new \DateTimeImmutable();

        $this->beConstructedWith(10, 'name', $date, Status::Updated);

        $this->normalize()->shouldReturn([
            'id' => 10,
            'code' => 'name',
            'date' => $date->format(\DateTimeInterface::ATOM),
            'status' => 'updated',
        ]);
    }

    function it_can_be_denormalized()
    {
        // Fake construct is needed for phpspec even if we don't use it
        $this->beConstructedWith(1, 'fake', new \DateTimeImmutable(), Status::Created);

        $date = new \DateTimeImmutable('2020-11-24T22:02:12+00:00');
        $denormalize = [
            'id' => 10,
            'code' => 'name',
            'date' => $date->format(\DateTimeInterface::ATOM),
            'status' => 'updated',
        ];

        $this::denormalize($denormalize)->shouldBeLike(new AttributeWasCreatedOrUpdated(10, 'name', $date, Status::Updated));
    }

    function it_cannot_be_denormalized_with_wrong_id()
    {
        // Fake construct is needed for phpspec even if we don't use it
        $this->beConstructedWith(1, 'fake', new \DateTimeImmutable(), Status::Created);

        $denormalize = [
            'id' => 'id',
            'code' => 'name',
            'date' => '2020-11-24T22:02:12+00:00',
            'status' => 'updated',
        ];

        $this->shouldThrow(\InvalidArgumentException::class)->during('denormalize', [$denormalize]);
    }

    function it_cannot_be_denormalized_with_wrong_code()
    {
        // Fake construct is needed for phpspec even if we don't use it
        $this->beConstructedWith(1, 'fake', new \DateTimeImmutable(), Status::Created);

        $denormalize = [
            'id' => 10,
            'code' => 8,
            'date' => '2020-11-24T22:02:12+00:00',
            'status' => 'updated',
        ];

        $this->shouldThrow(\InvalidArgumentException::class)->during('denormalize', [$denormalize]);
    }

    function it_cannot_be_denormalized_with_wrong_date_format()
    {
        // Fake construct is needed for phpspec even if we don't use it
        $this->beConstructedWith(1, 'fake', new \DateTimeImmutable(), Status::Created);

        $denormalize = [
            'id' => 10,
            'code' => 'name',
            'date' => 'whatever',
            'status' => 'updated',
        ];

        $this->shouldThrow(\InvalidArgumentException::class)->during('denormalize', [$denormalize]);
    }

    function it_cannot_be_denormalized_with_wrong_status()
    {
        // Fake construct is needed for phpspec even if we don't use it
        $this->beConstructedWith(1, 'fake', new \DateTimeImmutable(), Status::Created);

        $denormalize = [
            'id' => 10,
            'code' => 'name',
            'date' => '2020-11-24T22:02:12+00:00',
            'status' => 'unknown',
        ];

        $this->shouldThrow(\InvalidArgumentException::class)->during('denormalize', [$denormalize]);
    }
}
