<?php

namespace App\Search;

/**
 * Summary of SearchConfig
 */
readonly class SearchConfig
{
    /**
     * @param array $fillableSearchFields
     * @param array $jsonSearchableFields
     * @param array $loadedRelations
     * @param array $recursiveRelations
     * @param bool $includeEmptyRelations
     */
    public function __construct(
        public array $fillableSearchFields,
        public array $jsonSearchableFields,
        public array $loadedRelations,
        public array $recursiveRelations,
        public bool $includeEmptyRelations = false,
    ) {}
}
