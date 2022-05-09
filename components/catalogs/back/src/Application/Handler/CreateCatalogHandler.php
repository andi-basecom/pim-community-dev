<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Persistence\UpsertCatalogQueryInterface;
use Akeneo\Catalogs\Domain\Command\CreateCatalogCommand;
use Akeneo\Catalogs\Domain\Event\CatalogWasCreated;
use Akeneo\Catalogs\Domain\Model\Catalog;
use Psr\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateCatalogHandler
{
    public function __construct(
        private UpsertCatalogQueryInterface $upsertCatalogQuery,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(CreateCatalogCommand $command): void
    {
        $catalog = new Catalog(
            $command->getId(),
            $command->getName(),
        );

        $this->upsertCatalogQuery->execute($catalog);

        $this->eventDispatcher->dispatch(new CatalogWasCreated($catalog->getId()));
    }
}
