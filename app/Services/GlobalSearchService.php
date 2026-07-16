<?php

namespace App\Services;

use App\Enums\SearchModuleType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator as LengthAwarePaginatorContract;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Summary of GlobalSearchService
 */
readonly class GlobalSearchService implements GlobalSearchServiceInterface
{
    /**
     * @param Request $request
     */
    public function __construct(
        private Request $request,
    ) {}

    /**
     * @param array<int, string>|null $moduleValues
     * @return LengthAwarePaginatorContract
     */
    public function search(?array $moduleValues): LengthAwarePaginatorContract
    {
        $search = $this->searchString();

        $union = $this->buildUnion($this->resolveModules($moduleValues), $search);

        $query = DB::query()->fromSub($union, 'results')->orderBy('name');

        $paginator = $query->paginate(
            $this->perPage($query),
            ['*'],
            'page',
            max(1, (int) $this->request->get('page', 1))
        );

        $paginator->getCollection()->transform(fn (object $row): array => [
            'name' => $row->name,
            'description' => $row->description,
            'link' => SearchModuleType::from($row->module_name)->showLinkForUuid($row->uuid),
        ]);

        return $paginator;
    }

    /**
     * @param array<int, SearchModuleType> $modules
     * @param string|null $search
     * @return Builder
     */
    private function buildUnion(array $modules, ?string $search): Builder
    {
        $subqueries = array_map(
            fn (SearchModuleType $module): Builder => $this->moduleSubquery($module, $search),
            $modules
        );

        $union = array_shift($subqueries);

        foreach ($subqueries as $subquery) {
            $union->unionAll($subquery);
        }

        return $union;
    }

    /**
     * @param SearchModuleType $module
     * @param string|null $search
     * @return Builder
     */
    private function moduleSubquery(SearchModuleType $module, ?string $search): Builder
    {
        $model = new ($module->modelClass());

        $query = DB::table($model->getTable())->select([
            DB::raw("'{$module->value}' as module_name"),
            DB::raw($model->getKeyName().' as uuid'),
            DB::raw($module->nameExpression().' as name'),
            DB::raw($module->descriptionExpression().' as description'),
        ]);

        if ($module->usesSoftDeletes()) {
            $query->whereNull('deleted_at');
        }

        if ($search !== null && $search !== '') {
            $like = '%'.$search.'%';

            $query->where(function (Builder $where) use ($module, $like): void {
                $where->whereRaw($module->nameExpression().' ilike ?', [$like])
                    ->orWhereRaw($module->descriptionExpression().' ilike ?', [$like]);
            });
        }

        return $query;
    }

    /**
     * @param Builder $query
     * @return int
     */
    private function perPage(Builder $query): int
    {
        $perPage = (int) $this->request->get('perPage', -1);

        if ($perPage > 0) {
            return $perPage;
        }

        return max(1, (clone $query)->count());
    }

    /**
     * @return string|null
     */
    private function searchString(): ?string
    {
        $value = $this->request->get(config('search.search_string_keyword', 'searchString'));

        return is_string($value) ? $value : null;
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
