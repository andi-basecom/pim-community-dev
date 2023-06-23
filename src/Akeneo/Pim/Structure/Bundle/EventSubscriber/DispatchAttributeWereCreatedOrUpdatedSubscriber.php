<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\EventSubscriber;

use Akeneo\Pim\Structure\Component\Clock;
use Akeneo\Pim\Structure\Component\Event\AttributesWereCreatedOrUpdated;
use Akeneo\Pim\Structure\Component\Event\AttributeWasCreatedOrUpdated;
use Akeneo\Pim\Structure\Component\Event\Status;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DispatchAttributeWereCreatedOrUpdatedSubscriber implements EventSubscriberInterface
{
    /** @var array<string, string> */
    private array $createdAttributesByCode = [];

    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly Clock $clock
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE => 'beforeSave',
            StorageEvents::PRE_SAVE_ALL => 'beforeBulkSave',
            StorageEvents::POST_SAVE => 'onUnitarySave',
            StorageEvents::POST_SAVE_ALL => 'onBulkSave',
        ];
    }

    public function beforeSave(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof AttributeInterface
            || !$event->hasArgument('unitary')
            || false === $event->getArgument('unitary')
        ) {
            return;
        }

        if (null === $subject->getId()) {
            $attributeCode = $subject->getCode();
            $this->createdAttributesByCode[$attributeCode] = $attributeCode;
        }
    }

    public function onUnitarySave(GenericEvent $event): void
    {
        $subject = $event->getSubject();
        if (!$subject instanceof AttributeInterface
            || !$event->hasArgument('unitary')
            || false === $event->getArgument('unitary')
        ) {
            return;
        }

        $this->messageBus->dispatch(new AttributesWereCreatedOrUpdated([
            new AttributeWasCreatedOrUpdated(
                $subject->getId(),
                $subject->getCode(),
                $this->clock->now(),
                \array_key_exists($subject->getCode(), $this->createdAttributesByCode)
                    ? Status::Created
                    : Status::Updated
            ),
        ]));
        unset($this->createdAttributesByCode[$subject->getCode()]);
    }

    public function beforeBulkSave(GenericEvent $event): void
    {
        $subjects = $event->getSubject();
        if (!\is_array($subjects) || [] === $subjects
            || !current($subjects) instanceof AttributeInterface
        ) {
            return;
        }

        foreach ($subjects as $attribute) {
            if (null === $attribute->getId()) {
                $attributeCode = $attribute->getCode();
                $this->createdAttributesByCode[$attributeCode] = $attributeCode;
            }
        }
    }

    public function onBulkSave(GenericEvent $event): void
    {
        $subjects = $event->getSubject();
        if (!\is_array($subjects)
            || [] === $subjects
            || !current($subjects) instanceof AttributeInterface
        ) {
            return;
        }

        $now = $this->clock->now();
        $AttributeWasCreatedOrUpdatedList = [];
        foreach ($subjects as $attribute) {
            $AttributeWasCreatedOrUpdatedList[] = new AttributeWasCreatedOrUpdated(
                $attribute->getId(),
                $attribute->getCode(),
                $now,
                \array_key_exists($attribute->getCode(), $this->createdAttributesByCode)
                    ? Status::Created
                    : Status::Updated
            );
            unset($this->createdAttributesByCode[$attribute->getCode()]);
        }

        $this->messageBus->dispatch(new AttributesWereCreatedOrUpdated($AttributeWasCreatedOrUpdatedList));
    }
}
