<?php

namespace App\Services;

use App\Enums\SearchModuleType;
use App\Search\SearchInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

/**
 * Summary of GlobalSearchService
 */
readonly class GlobalSearchService implements GlobalSearchServiceInterface
{
    /**
     * @var int
     */
    private const GATHER_LIMIT = 10000;

    /**
     * @param Container $container
     * @param Request $request
     */
    public function __construct(
        private Container $container,
        private Request $request,
    ) {}

    /**
     * @param array<int, string>|null $moduleValues
     * @return LengthAwarePaginatorContract
     */
    public function search(?array $moduleValues): LengthAwarePaginatorContract
    {
        $page = max(1, (int) $this->request->get('page', 1));
        $perPage = (int) $this->request->get('perPage', -1);

        $items = $this->collectItems($moduleValues);

        usort($items, static fn (array $a, array $b): int => strcasecmp($a['name'], $b['name']));

        $total = count($items);
        $length = $perPage <= 0 ? max($total, 1) : $perPage;
        $slice = array_slice($items, ($page - 1) * $length, $length);

        return new LengthAwarePaginator($slice, $total, $length, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'query' => $this->request->query(),
        ]);
    }

    /**
     * @param array<int, string>|null $moduleValues
     * @return array<int, array{name: string, description: string|null, link: string|null}>
     */
    private function collectItems(?array $moduleValues): array
    {
        $query = $this->request->query;
        $originalPerPage = $query->get('perPage');
        $originalPage = $query->get('page');
        $originalSort = $query->get('sort');

        $query->set('perPage', self::GATHER_LIMIT);
        $query->set('page', 1);
        $query->remove('sort');

        $items = [];

        try {
            foreach ($this->resolveModules($moduleValues) as $module) {
                /** @var SearchInterface $search */
                $search = $this->container->make($module->searchClass());

                foreach ($search->search()->items() as $model) {
                    /** @var Model $model */
                    $items[] = [
                        'name' => $module->resolveName($model),
                        'description' => $module->resolveDescription($model),
                        'link' => $module->showLink($model),
                    ];
                }
            }
        } finally {
            $this->restoreQueryValue('perPage', $originalPerPage);
            $this->restoreQueryValue('page', $originalPage);
            $this->restoreQueryValue('sort', $originalSort);
        }

        return $items;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    private function restoreQueryValue(string $key, mixed $value): void
    {
        if ($value === null) {
            $this->request->query->remove($key);

            return;
        }

        $this->request->query->set($key, $value);
    }

    /**
     * @param array<int, string>|null $moduleValues
     * @return array<int, SearchModuleType>
     */
    private function resolveModules(?array $moduleValues): array
    {
        if (empty($moduleValues)) {
            return SearchModuleType::cases();
        }

        return array_values(array_filter(
            SearchModuleType::cases(),
            static fn (SearchModuleType $module): bool => in_array($module->value, $moduleValues, true)
        ));
    }
}
