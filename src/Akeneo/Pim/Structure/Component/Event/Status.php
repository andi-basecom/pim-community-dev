<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Event;

enum Status: string
{
    case Created = 'created';
    case Updated ='updated';
}
