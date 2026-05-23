<?php

namespace App\Dto;

/**
 * Summary of PageableDto
 */
class PageableDto extends Dto
{
    /**
     * @param  array  $filters
     * @param  array  $sorts
     * @param  int  $perPage
     * @param  int  $page
     */
    public function __construct(
        private readonly array $filters = [],
        private readonly array $sorts = [],
        private readonly int $page = 1,
        private int $perPage = 10
    ) {}

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @return array
     */
    public function getSorts(): array
    {
        return $this->sorts;
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param  int  $perPage
     * @return void
     */
    public function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
    }
}
