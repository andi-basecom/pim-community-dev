<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface Clock
{
    public function now(): \DateTimeImmutable;
}
