<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter;

use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NonExistentChannelLocaleValuesFilter implements NonExistentValuesFilter
{
    private ChannelExistsWithLocaleInterface $channelsLocales;

    private GetAttributes $getAttributes;

    public function __construct(
        ChannelExistsWithLocaleInterface $channelsLocales,
        GetAttributes $getAttributes
    ) {
        $this->channelsLocales = $channelsLocales;
        $this->getAttributes = $getAttributes;
    }

    public function filter(OnGoingFilteredRawValues $onGoingFilteredRawValues): OnGoingFilteredRawValues
    {
        $filteredRawValues = [];
        foreach ($onGoingFilteredRawValues->nonFilteredRawValuesCollectionIndexedByType() as $type => $rawValuesIndexedByAttribute) {
            $filteredRawValues[$type] = [];
            foreach ($rawValuesIndexedByAttribute as $attributeCode => $rawValuesIndexedByProduct) {
                foreach ($rawValuesIndexedByProduct as $productIndex => $productValues) {
                    $productValues['values'] = $this->filterProductValues($productValues['values']);
                    $productValues['values'] = $this->filterLocaleSpecificProductValues($productValues['values'], $attributeCode);
                    $filteredRawValues[$type][$attributeCode][$productIndex] = $productValues;
                }
            }
        }

        return $onGoingFilteredRawValues->addFilteredValuesIndexedByType($filteredRawValues);
    }

    private function filterProductValues(array $productValues): array
    {
        $filteredProductValues = [];
        foreach ($productValues as $channel => $localeValues) {
            if ($this->doesChannelExist($channel)) {
                foreach ($localeValues as $locale => $value) {
                    if ($this->isLocaleActivatedForChannel($locale, $channel)) {
                        $filteredProductValues[$channel][$locale] = $value;
                    }
                }
            }
        }

        return $filteredProductValues;
    }

    private function filterLocaleSpecificProductValues(array $productValues, string $attributeCode): array
    {
        $filteredProductValues = [];
        $attribute = $this->getAttributes->forCode($attributeCode);

        foreach ($productValues as $channel => $localeValues) {
            foreach ($localeValues as $localeCode => $value) {
                if (!$attribute->isLocaleSpecific() || in_array($localeCode, $attribute->availableLocaleCodes())) {
                    $filteredProductValues[$channel][$localeCode] = $value;
                }
            }
        }

        return $filteredProductValues;
    }

    private function doesChannelExist(string $channel): bool
    {
        return $channel === '<all_channels>' || $this->channelsLocales->doesChannelExist($channel);
    }

    private function isLocaleActivatedForChannel(string $locale, string $channel): bool
    {
        return $locale === '<all_locales>'
            || (
                $channel === '<all_channels>'
                    ? $this->channelsLocales->isLocaleActive($locale)
                    : $this->channelsLocales->isLocaleBoundToChannel($locale, $channel)
            );
    }
}
