<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle;

use Akeneo\Pim\Structure\Component\Clock;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SystemClock implements Clock
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable();
    }
}
