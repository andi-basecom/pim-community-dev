<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEntityIdFactoryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\ProductEnrichment\GetEvaluableProductValuesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\CriterionEvaluationStatus;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductEntityIdCollection;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EvaluateCriteria
{
    public function __construct(
        private CriteriaEvaluationRegistry $evaluationRegistry,
        private GetEvaluableProductValuesQueryInterface $getEvaluableProductValuesQuery,
        private CriteriaByFeatureRegistry $criteriaByFeatureRegistry,
        private ProductEntityIdFactoryInterface $idFactory,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * @param CriterionCode[] $productCriterionCodes If empty all criteria are evaluated
     * @return void
     */
    public function forEntityIds(ProductEntityIdCollection $productIdCollection, array $productCriterionCodes): void
    {
        if ([] === $productCriterionCodes) {
            $productCriterionCodes = $this->criteriaByFeatureRegistry->getAllCriterionCodes();
        }

        foreach ($productIdCollection as $productId) {
            $productValues = $this->getEvaluableProductValuesQuery->byProductId(
                $this->idFactory->create((string) $productId)
            );
            foreach ($productCriterionCodes as $productCriterionCode) {
                $productCriterion = new Write\CriterionEvaluation(
                    $productCriterionCode,
                    $productId,
                    CriterionEvaluationStatus::pending()
                );
                $this->evaluateCriterion($productCriterion, $productValues);
            }
        }
    }

    private function evaluateCriterion(Write\CriterionEvaluation $criterionEvaluation, ProductValuesCollection $productValues): void
    {
        try {
            $evaluationService = $this->evaluationRegistry->get($criterionEvaluation->getCriterionCode());
            $criterionEvaluation->start();
            $result = $evaluationService->evaluate($criterionEvaluation, $productValues);
            $criterionEvaluation->end($result);
        } catch (\Exception $exception) {
            $this->logger->error(
                'Failed to evaluate criterion {criterion_code} for product id {product_id}',
                ['criterion_code' => $criterionEvaluation->getCriterionCode(), 'product_id' => $criterionEvaluation->getEntityId(), 'message' => $exception->getMessage()]
            );
            $criterionEvaluation->flagAsError();
        }
    }
}
