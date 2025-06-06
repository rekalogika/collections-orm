<?php

declare(strict_types=1);

/*
 * This file is part of rekalogika/collections package.
 *
 * (c) Priyadi Iman Nurcahyo <https://rekalogika.dev>
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Rekalogika\Collections\ORM;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\QueryBuilder;
use Rekalogika\Collections\ORM\Trait\QueryBuilderPageableTrait;
use Rekalogika\Contracts\Collections\PageableRecollection;
use Rekalogika\Contracts\Collections\ReadableRecollection;
use Rekalogika\Domain\Collections\Common\Configuration;
use Rekalogika\Domain\Collections\Common\Count\CountStrategy;
use Rekalogika\Domain\Collections\Common\Internal\ParameterUtil;
use Rekalogika\Domain\Collections\Common\KeyTransformer\KeyTransformer;
use Rekalogika\Domain\Collections\Common\Pagination;
use Rekalogika\Domain\Collections\Common\Trait\PageableTrait;
use Rekalogika\Domain\Collections\Common\Trait\ReadableCollectionTrait;
use Rekalogika\Domain\Collections\Common\Trait\ReadableRecollectionTrait;
use Rekalogika\Domain\Collections\Common\Trait\SafeCollectionTrait;
use Rekalogika\Rekapager\Adapter\Common\SeekMethod;

/**
 * @template TKey of array-key
 * @template T
 * @implements ReadableRecollection<TKey,T>
 * @api
 */
class QueryRecollection implements ReadableRecollection
{
    /** @use QueryBuilderPageableTrait<TKey,T> */
    use QueryBuilderPageableTrait;

    /** @use ReadableCollectionTrait<TKey,T> */
    use ReadableCollectionTrait;

    /** @use PageableTrait<TKey,T> */
    use PageableTrait;

    /** @use SafeCollectionTrait<TKey,T> */
    use SafeCollectionTrait;

    /** @use ReadableRecollectionTrait<TKey,T> */
    use ReadableRecollectionTrait;

    private readonly ?string $indexBy;

    /**
     * @var int<1,max>
     */
    private int $itemsPerPage;

    /**
     * @param int<1,max> $itemsPerPage
     * @param null|int<1,max> $softLimit
     * @param null|int<1,max> $hardLimit
     * @param null|LockMode|LockMode::* $lockMode
     * @param list<string> $boundaryFields
     */
    public function __construct(
        private QueryBuilder $queryBuilder,
        ?string $indexBy = null,
        ?int $itemsPerPage = null,
        private readonly ?CountStrategy $count = null,
        private readonly ?int $softLimit = null,
        private readonly ?int $hardLimit = null,
        private readonly ?KeyTransformer $keyTransformer = null,
        private readonly ?Pagination $pagination = null,
        private readonly SeekMethod $seekMethod = SeekMethod::Approximated,
        private readonly LockMode|int|null $lockMode = null,
        private readonly null|array $boundaryFields = null,
    ) {
        $this->indexBy = $indexBy ?? Configuration::$defaultIndexBy;
        $this->itemsPerPage = $itemsPerPage ?? Configuration::$defaultItemsPerPage;
    }


    #[\Override]
    private function getCountStrategy(): CountStrategy
    {
        return $this->count ?? ParameterUtil::getDefaultCountStrategyForFullClasses();
    }

    /**
     * @return null|int<1,max>
     */
    #[\Override]
    private function getSoftLimit(): ?int
    {
        return $this->softLimit;
    }

    /**
     * @return null|int<1,max>
     */
    #[\Override]
    private function getHardLimit(): ?int
    {
        return $this->hardLimit;
    }

    /**
     * @param int<1,max> $itemsPerPage
     */
    #[\Override]
    public function withItemsPerPage(int $itemsPerPage): static
    {
        $instance = clone $this;
        $instance->itemsPerPage = $itemsPerPage;
        $instance->pageable = null;

        return $instance;
    }

    final public function getQueryBuilder(): QueryBuilder
    {
        return clone $this->queryBuilder;
    }

    /**
     * @param \Closure(QueryBuilder):void $function
     */
    final public function updateQueryBuilder(\Closure $function): static
    {
        $instance = clone $this;
        $function($instance->queryBuilder);

        return $instance;
    }

    final protected function withQueryBuilder(QueryBuilder $queryBuilder): static
    {
        $instance = clone $this;
        $instance->queryBuilder = $queryBuilder;

        return $instance;
    }

    /**
     * @return self<TKey,T>
     */
    final protected function createQueryRecollection(
        QueryBuilder $queryBuilder,
        ?string $indexBy = null,
        ?CountStrategy $count = null,
        ?Pagination $pagination = null,
        ?SeekMethod $seekMethod = null,
        ?LockMode $lockMode = null,
    ): self {
        /** @var QueryRecollection<TKey,T> */
        return new QueryRecollection(
            queryBuilder: $queryBuilder,
            indexBy: $indexBy ?? $this->indexBy,
            count: $count ?? $this->count,
            itemsPerPage: $this->itemsPerPage,
            softLimit: $this->softLimit,
            hardLimit: $this->hardLimit,
            pagination: $pagination ?? $this->pagination,
            seekMethod: $seekMethod ?? $this->seekMethod,
            lockMode: $lockMode ?? $this->lockMode,
            boundaryFields: $this->boundaryFields,
        );
    }

    /**
     * @return PageableRecollection<TKey,T>
     */
    final protected function createQueryPageable(
        QueryBuilder $queryBuilder,
        ?string $indexBy = null,
        ?CountStrategy $count = null,
        ?Pagination $pagination = null,
        ?SeekMethod $seekMethod = null,
        ?LockMode $lockMode = null,
    ): PageableRecollection {
        /**
         * @var PageableRecollection<TKey,T>
         * @phpstan-ignore-next-line
         */
        return new QueryPageable(
            queryBuilder: $queryBuilder,
            itemsPerPage: $this->itemsPerPage,
            indexBy: $indexBy ?? $this->indexBy,
            count: $count ?? $this->count,
            pagination: $pagination ?? $this->pagination,
            seekMethod: $seekMethod ?? $this->seekMethod,
            lockMode: $lockMode ?? $this->lockMode,
            boundaryFields: $this->boundaryFields,
        );
    }
}
