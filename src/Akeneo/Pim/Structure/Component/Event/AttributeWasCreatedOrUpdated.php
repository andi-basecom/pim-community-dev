<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Event;

use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeWasCreatedOrUpdated
{
    public function __construct(
        public readonly int $id,
        public readonly string $code,
        public readonly \DateTimeImmutable $date,
        public readonly Status $status,
    ) {
        Assert::stringNotEmpty($code);
    }

    public function normalize(): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'date' => $this->date->format(\DateTimeInterface::ATOM),
            'status' => $this->status->value,
        ];
    }

    public static function denormalize(array $normalized): AttributeWasCreatedOrUpdated
    {
        Assert::keyExists($normalized, 'id');
        Assert::integer($normalized['id']);

        Assert::keyExists($normalized, 'code');
        Assert::string($normalized['code']);

        Assert::keyExists($normalized, 'date');
        Assert::string($normalized['date']);
        $date = \DateTimeImmutable::createFromFormat(\DateTimeInterface::ATOM, $normalized['date']);
        Assert::isInstanceOf($date, \DateTimeImmutable::class, \sprintf('Date is not well formatted: %s', $normalized['date']));

        Assert::keyExists($normalized, 'status');
        $status = Status::tryFrom($normalized['status']);
        Assert::notNull($status, \sprintf('Status "%s" does not exist', $normalized['status']));

        return new AttributeWasCreatedOrUpdated(
            $normalized['id'],
            $normalized['code'],
            $date,
            $status
        );
    }
}
