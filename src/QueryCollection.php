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

use Doctrine\ORM\QueryBuilder;
use Rekalogika\Collections\ORM\Trait\QueryBuilderPageableTrait;
use Rekalogika\Contracts\Collections\ReadableRecollection;
use Rekalogika\Domain\Collections\Common\CountStrategy;
use Rekalogika\Domain\Collections\Common\Trait\PageableTrait;
use Rekalogika\Domain\Collections\Common\Trait\ReadableCollectionTrait;
use Rekalogika\Domain\Collections\Common\Trait\ReadableRecollectionTrait;
use Rekalogika\Domain\Collections\Common\Trait\SafeCollectionTrait;

/**
 * @template TKey of array-key
 * @template T
 * @implements ReadableRecollection<TKey,T>
 */
class QueryCollection implements ReadableRecollection
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

    /**
     * @param int<1,max> $itemsPerPage
     * @param null|int<0,max> $count
     * @param null|int<1,max> $softLimit
     * @param null|int<1,max> $hardLimit
     */
    public function __construct(
        private QueryBuilder $queryBuilder,
        private readonly int $itemsPerPage = 50,
        private readonly CountStrategy $countStrategy = CountStrategy::Restrict,
        private ?int &$count = null,
        private readonly ?int $softLimit = null,
        private readonly ?int $hardLimit = null,
    ) {
    }

    private function getCountStrategy(): CountStrategy
    {
        return $this->countStrategy;
    }

    private function &getProvidedCount(): ?int
    {
        return $this->count;
    }

    /**
     * @return null|int<1,max>
     */
    private function getSoftLimit(): ?int
    {
        return $this->softLimit;
    }

    /**
     * @return null|int<1,max>
     */
    private function getHardLimit(): ?int
    {
        return $this->hardLimit;
    }

    /**
     * @param null|int<1,max> $itemsPerPage
     * @param null|int<0,max> $count
     * @param null|int<1,max> $softLimit
     * @param null|int<1,max> $hardLimit
     */
    protected function with(
        ?QueryBuilder $queryBuilder = null,
        ?int $itemsPerPage = 50,
        ?CountStrategy $countStrategy = CountStrategy::Restrict,
        ?int &$count = null,
        ?int $softLimit = null,
        ?int $hardLimit = null,
    ): static {
        $count = $count ?? $this->count;

        // @phpstan-ignore-next-line
        return new static(
            queryBuilder: $queryBuilder ?? $this->queryBuilder,
            itemsPerPage: $itemsPerPage ?? $this->itemsPerPage,
            countStrategy: $countStrategy ?? $this->countStrategy,
            count: $count,
            softLimit: $softLimit ?? $this->softLimit,
            hardLimit: $hardLimit ?? $this->hardLimit,
        );
    }

    final protected function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }
}
