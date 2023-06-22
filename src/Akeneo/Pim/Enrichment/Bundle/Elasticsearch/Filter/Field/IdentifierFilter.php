<?php

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Filter\Field;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidOperatorException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterHelper;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\FieldFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetMainIdentifierAttributeCode;
use Akeneo\Tool\Component\Elasticsearch\QueryString;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;

/**
 * Identifier filter for an Elasticsearch query
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IdentifierFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    const IDENTIFIER_KEY = 'identifier';

    /**
     * @param array<string> $supportedFields
     * @param array<string> $supportedOperators
     */
    public function __construct(
        array $supportedFields = [],
        array $supportedOperators = [],
        private readonly GetMainIdentifierAttributeCode $getMainIdentifierAttributeCode
    ) {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $channel = null, $options = [])
    {
        if (null === $this->searchQueryBuilder) {
            throw new \LogicException('The search query builder is not initialized in the filter.');
        }

        if (Operators::IS_EMPTY !== $operator && Operators::IS_NOT_EMPTY !== $operator) {
            $this->checkValue($field, $operator, $value);
        }

        $this->applyFilter($field, $operator, $value);

        return $this;
    }

    /**
     * Checks the identifier is a string or an array depending on the operator
     *
     * @param string $property
     * @param string $operator
     * @param mixed  $value
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkValue($property, $operator, $value)
    {
        if (Operators::IN_LIST === $operator || Operators::NOT_IN_LIST === $operator) {
            FieldFilterHelper::checkArrayOfStrings($property, $value, self::class);
        } else {
            FieldFilterHelper::checkString($property, $value, self::class);
        }
    }

    /**
     * Apply the filtering conditions to the search query builder
     *
     * @param string $field
     * @param string $operator
     * @param mixed  $value
     */
    protected function applyFilter($field, $operator, $value)
    {
        $productDocumentType = str_replace('\\', '\\\\', ProductInterface::class);
        $productModelDocumentType = str_replace('\\', '\\\\', ProductModelInterface::class);

        $mainIdentifierAttributeCode = ($this->getMainIdentifierAttributeCode)();
        $productIdentifierField = \sprintf('values.%s-%s.<all_channels>.<all_locales>',
            $mainIdentifierAttributeCode,
            AttributeTypes::BACKEND_TYPE_TEXT
        );
        $productModelIdentifierField = self::IDENTIFIER_KEY;

        switch ($operator) {
            case Operators::STARTS_WITH:
                $this->searchQueryBuilder->addFilter(
                    $this->buildIdentifierSearchFilter(
                        QueryString::escapeValue($value) . '*',
                        $productDocumentType,
                        $productIdentifierField,
                        $productModelDocumentType,
                        $productModelIdentifierField
                    )
                );

                break;

            case Operators::CONTAINS:
                $this->searchQueryBuilder->addFilter(
                    $this->buildIdentifierSearchFilter(
                        '*' . QueryString::escapeValue($value) . '*',
                        $productDocumentType,
                        $productIdentifierField,
                        $productModelDocumentType,
                        $productModelIdentifierField
                    )
                );

                break;

            case Operators::DOES_NOT_CONTAIN:
                $this->searchQueryBuilder->addMustNot($this->buildIdentifierSearchFilter(
                    '*' . QueryString::escapeValue($value) . '*',
                    $productDocumentType,
                    $productIdentifierField,
                    $productModelDocumentType,
                    $productModelIdentifierField
                ));
                $this->searchQueryBuilder->addFilter($this->buildFieldShouldExistClause(
                    $productDocumentType,
                    $productIdentifierField,
                    $productModelDocumentType,
                    $productModelIdentifierField
                ));
                break;

            case Operators::EQUALS:
                $this->searchQueryBuilder->addFilter(
                    $this->buildIdentifierSearchFilter(
                        QueryString::escapeValue($value),
                        $productDocumentType,
                        $productIdentifierField,
                        $productModelDocumentType,
                        $productModelIdentifierField
                    )
                );
                break;

            case Operators::NOT_EQUAL:
                $this->searchQueryBuilder->addMustNot(
                    $this->buildIdentifierSearchFilter(
                        QueryString::escapeValue($value),
                        $productDocumentType,
                        $productIdentifierField,
                        $productModelDocumentType,
                        $productModelIdentifierField
                    )
                );
                $this->searchQueryBuilder->addFilter($this->buildFieldShouldExistClause(
                    $productDocumentType,
                    $productIdentifierField,
                    $productModelDocumentType,
                    $productModelIdentifierField
                ));
                break;

            case Operators::IN_LIST:
                $this->searchQueryBuilder->addFilter($this->buildIdentifierTermsFilter(
                    $value,
                    $productDocumentType,
                    $productIdentifierField,
                    $productModelDocumentType,
                    $productModelIdentifierField
                ));
                break;

            case Operators::NOT_IN_LIST:
                $this->searchQueryBuilder->addMustNot($this->buildIdentifierTermsFilter(
                    $value,
                    $productDocumentType,
                    $productIdentifierField,
                    $productModelDocumentType,
                    $productModelIdentifierField
                ));
                $this->searchQueryBuilder->addFilter($this->buildFieldShouldExistClause(
                    $productDocumentType,
                    $productIdentifierField,
                    $productModelDocumentType,
                    $productModelIdentifierField
                ));
                break;

            case Operators::IS_EMPTY:
                $this->searchQueryBuilder->addMustNot($this->buildFieldShouldExistClause(
                    $productDocumentType,
                    $productIdentifierField,
                    $productModelDocumentType,
                    $productModelIdentifierField
                ));
                break;

            case Operators::IS_NOT_EMPTY:
                $this->searchQueryBuilder->addFilter($this->buildFieldShouldExistClause(
                    $productDocumentType,
                    $productIdentifierField,
                    $productModelDocumentType,
                    $productModelIdentifierField
                ));
                break;

            default:
                throw InvalidOperatorException::notSupported($operator, static::class);
        }
    }

    private function buildIdentifierSearchFilter(
        string $searchString,
        string $productDocumentType,
        string $productIdentifierField,
        string $productModelDocumentType,
        string $productModelIdentifierField
    ): array
    {
        $productClause = [
            'bool' => [
                'should' => [
                    [
                        'query_string' => [
                            'default_field' => $productIdentifierField,
                            'query'         => $searchString,
                        ],
                    ],
                    [
                        'query_string' => [
                            'default_field' => 'document_type',
                            'query'         => $productDocumentType,
                        ],
                    ],
                ],
                'minimum_should_match' => 2,
            ],
        ];

        $productModelClause = [
            'bool' => [
                'should' => [
                    [
                        'query_string' => [
                            'default_field' => $productModelIdentifierField,
                            'query'         => $searchString,
                        ],
                    ],
                    [
                        'query_string' => [
                            'default_field' => 'document_type',
                            'query'         => $productModelDocumentType,
                        ],
                    ],
                ],
                'minimum_should_match' => 2,
            ],
        ];

        return [
            'bool' => [
                'should' => [$productClause, $productModelClause],
                'minimum_should_match' => 1,
            ],
        ];
    }

    private function buildIdentifierTermsFilter(
        array $value,
        string $productDocumentType,
        string $productIdentifierField,
        string $productModelDocumentType,
        string $productModelIdentifierField
    ): array
    {
        $productClause = [
            'bool' => [
                'should' => [
                    [
                        'terms' => [
                            $productIdentifierField => $value,
                        ],
                    ],
                    [
                        'query_string' => [
                            'default_field' => 'document_type',
                            'query'         => $productDocumentType,
                        ],
                    ],
                ],
                'minimum_should_match' => 2,
            ],
        ];

        $productModelClause = [
            'bool' => [
                'should' => [
                    [
                        'terms' => [
                            $productModelIdentifierField => $value,
                        ],
                    ],
                    [
                        'query_string' => [
                            'default_field' => 'document_type',
                            'query'         => $productModelDocumentType,
                        ],
                    ],
                ],
                'minimum_should_match' => 2,
            ],
        ];

        return [
            'bool' => [
                'should' => [$productClause, $productModelClause],
                'minimum_should_match' => 1,
            ],
        ];
    }

    private function buildFieldShouldExistClause(
        string $productDocumentType,
        string $productIdentifierField,
        string $productModelDocumentType,
        string $productModelIdentifierField
    ): array
    {
        return [
            'bool' => [
                'should' => [
                    [
                        'bool' => [
                            'should' => [
                                [
                                    'exists' => ['field' => $productIdentifierField],
                                ],
                                [
                                    'query_string' => [
                                        'default_field' => 'document_type',
                                        'query'         => $productDocumentType,
                                    ],
                                ]
                            ],
                            'minimum_should_match' => 2,
                        ],
                    ],
                    [
                        'bool' => [
                            'should' => [
                                [
                                    'exists' => ['field' => $productModelIdentifierField],
                                ],
                                [
                                    'query_string' => [
                                        'default_field' => 'document_type',
                                        'query'         => $productModelDocumentType,
                                    ],
                                ]
                            ],
                            'minimum_should_match' => 2,
                        ],
                    ],
                ],
                'minimum_should_match' => 1,
            ],
        ];
    }
}
