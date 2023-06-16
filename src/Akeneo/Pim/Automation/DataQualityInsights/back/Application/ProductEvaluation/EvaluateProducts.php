<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateProductScores;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Event\ProductsEvaluated;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductUuidCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EvaluateProducts
{
    public function __construct(
        private EvaluatePendingCriteria $evaluatePendingProductCriteria,
        private EvaluateCriteria $evaluateCriteria,
        private ConsolidateProductScores $consolidateProductScores,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @deprecated use forCriteria() instead
     * Pending criteria are fetched from the database (legacy). New way to evaluate products is done by events
     */
    public function forPendingCriteria(ProductUuidCollection $productUuidCollection): void
    {
        $this->evaluatePendingProductCriteria->evaluateAllCriteria($productUuidCollection);
        $this->consolidateProductScores->consolidate($productUuidCollection);
        $this->eventDispatcher->dispatch(new ProductsEvaluated($productUuidCollection));
    }

    /**
     * @param CriterionCode[] $productCriterionCodes
     */
    public function forCriteria(ProductUuidCollection $productUuidCollection, array $productCriterionCodes): void
    {
        $this->evaluateCriteria->forEntityIds($productUuidCollection, $productCriterionCodes);
        $this->consolidateProductScores->consolidate($productUuidCollection);
        $this->eventDispatcher->dispatch(new ProductsEvaluated($productUuidCollection));
    }
}
