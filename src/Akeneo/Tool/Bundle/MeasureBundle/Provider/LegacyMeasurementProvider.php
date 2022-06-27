<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Provider;

use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Akeneo\Tool\Bundle\MeasureBundle\Persistence\MeasurementFamilyRepositoryInterface;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 */
class LegacyMeasurementProvider
{
    private array $legacyMeasurementFamily = [];

    public function __construct(
        private MeasurementFamilyRepositoryInterface $measurementFamilyRepository,
        private LegacyMeasurementAdapter $adapter
    ) {
    }

    public function getMeasurementFamilies(): array
    {
        if (empty($this->legacyMeasurementFamily)) {
            $this->legacyMeasurementFamily = $this->loadLegacyMeasurementFamilies();
        }

        return $this->legacyMeasurementFamily;
    }

    private function loadLegacyMeasurementFamilies(): array
    {
        $measurementFamilies = array_map(
            fn (MeasurementFamily $family) => $this->adapter->adapts($family),
            $this->measurementFamilyRepository->all()
        );

        $result = [];
        foreach ($measurementFamilies as $familyCode => $family) {
            $result = array_merge($result, $family);
        }

        return $result;
    }
}
