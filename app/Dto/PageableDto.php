<?php

namespace App\Dto;

/**
 * Summary of PageableDto
 */
class PageableDto extends Dto
{
    public function __construct(
        private readonly array $filters = [],
        private readonly array $sorts = [],
        private readonly int $page = 1,
        private int $perPage = 10
    ) {}

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getSorts(): array
    {
        return $this->sorts;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
    }
}
