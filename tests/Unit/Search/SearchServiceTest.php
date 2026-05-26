<?php

namespace Tests\Unit\Search;

use App\Repositories\SearchRepositoryInterface;
use App\Services\SearchService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class SearchServiceTest extends TestCase
{
    private MockInterface $repo;

    private SearchService $service;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->repo = Mockery::mock(SearchRepositoryInterface::class);
        $this->service = new SearchService($this->repo);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->addToAssertionCount(Mockery::getContainer()->mockery_getExpectationCount());
        Mockery::close();
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function test_resolve_null_filter_returns_null_for_regular_string(): void
    {
        $this->assertNull($this->service->resolveNullFilter('some_value'));
    }

    /**
     * @return void
     */
    public function test_resolve_null_filter_returns_null_sentinel_for_null(): void
    {
        $this->assertSame('null', $this->service->resolveNullFilter(null));
    }

    /**
     * @return void
     */
    public function test_resolve_null_filter_returns_null_sentinel_for_null_string(): void
    {
        $this->assertSame('null', $this->service->resolveNullFilter('null'));
    }

    /**
     * @return void
     */
    public function test_resolve_null_filter_returns_not_null_for_not_null_string(): void
    {
        $this->assertSame('not_null', $this->service->resolveNullFilter('not_null'));
    }

    /**
     * @return void
     */
    public function test_resolve_null_filter_returns_not_null_for_exclamation_null(): void
    {
        $this->assertSame('not_null', $this->service->resolveNullFilter('!null'));
    }

    /**
     * @return void
     */
    public function test_resolve_null_filter_returns_null_for_array_with_null_value(): void
    {
        $this->assertSame('null', $this->service->resolveNullFilter(['null']));
    }

    /**
     * @return void
     */
    public function test_resolve_null_filter_returns_null_for_array_with_null_key(): void
    {
        $this->assertSame('null', $this->service->resolveNullFilter(['null' => true]));
    }

    /**
     * @return void
     */
    public function test_resolve_null_filter_returns_not_null_for_array_with_not_null_value(): void
    {
        $this->assertSame('not_null', $this->service->resolveNullFilter(['not_null']));
    }

    /**
     * @return void
     */
    public function test_resolve_null_filter_returns_not_null_for_array_with_exclamation_null(): void
    {
        $this->assertSame('not_null', $this->service->resolveNullFilter(['!null']));
    }

    /**
     * @return void
     */
    public function test_resolve_null_filter_returns_null_for_regular_array(): void
    {
        $this->assertNull($this->service->resolveNullFilter(['value1', 'value2']));
    }

    /**
     * @return void
     */
    public function test_is_json_field_returns_true_for_allowed_json_field(): void
    {
        $this->assertTrue($this->service->isJsonField('data.name', ['data']));
    }

    /**
     * @return void
     */
    public function test_is_json_field_returns_false_for_empty_allowed_fields(): void
    {
        $this->assertFalse($this->service->isJsonField('data.name', []));
    }

    /**
     * @return void
     */
    public function test_is_json_field_returns_false_for_not_allowed_field(): void
    {
        $this->assertFalse($this->service->isJsonField('other.name', ['data']));
    }

    /**
     * @return void
     */
    public function test_json_path_from_field_converts_dots_to_arrows(): void
    {
        $this->assertSame('data->name->value', $this->service->jsonPathFromField('data.name.value'));
    }

    /**
     * @return void
     */
    public function test_can_filter_by_relation_returns_true_for_exact_match(): void
    {
        $this->assertTrue($this->service->canFilterByRelation('user', ['user', 'patient'], []));
    }

    /**
     * @return void
     */
    public function test_can_filter_by_relation_returns_true_for_nested_loaded_relation(): void
    {
        $this->assertTrue($this->service->canFilterByRelation('user', ['user.profile'], []));
    }

    /**
     * @return void
     */
    public function test_can_filter_by_relation_returns_true_for_recursive_relation(): void
    {
        $this->assertTrue($this->service->canFilterByRelation('children', [], ['children' => 3]));
    }

    /**
     * @return void
     */
    public function test_can_filter_by_relation_returns_false_for_unknown_relation(): void
    {
        $this->assertFalse($this->service->canFilterByRelation('unknown', ['user'], []));
    }

    /**
     * @return void
     */
    public function test_is_relation_count_field_returns_true_for_matching_field(): void
    {
        $this->assertTrue($this->service->isRelationCountField('job_positions_count', ['jobPositions']));
    }

    /**
     * @return void
     */
    public function test_is_relation_count_field_returns_false_for_non_matching_field(): void
    {
        $this->assertFalse($this->service->isRelationCountField('other_count', ['jobPositions']));
    }

    /**
     * @return void
     */
    public function test_get_sort_direction_returns_asc_for_configured_character(): void
    {
        config(['search.sort_asc_default_character' => 'asc']);

        $this->assertSame('asc', $this->service->getSortDirection('asc'));
    }

    /**
     * @return void
     */
    public function test_get_sort_direction_returns_desc_for_other_character(): void
    {
        config(['search.sort_asc_default_character' => 'asc']);

        $this->assertSame('desc', $this->service->getSortDirection('desc'));
    }

    /**
     * @return void
     */
    public function test_build_recursive_relation_paths_generates_correct_depth_paths(): void
    {
        $paths = $this->service->buildRecursiveRelationPaths(['children' => 3]);

        $this->assertSame(['children', 'children.children', 'children.children.children'], $paths);
    }

    /**
     * @return void
     */
    public function test_build_recursive_relation_paths_generates_single_path_for_depth_one(): void
    {
        $paths = $this->service->buildRecursiveRelationPaths(['children' => 1]);

        $this->assertSame(['children'], $paths);
    }

    /**
     * @return void
     */
    public function test_build_recursive_relation_paths_skips_zero_depth(): void
    {
        $paths = $this->service->buildRecursiveRelationPaths(['children' => 0]);

        $this->assertSame([], $paths);
    }

    /**
     * @return void
     */
    public function test_build_recursive_relation_paths_returns_empty_for_empty_input(): void
    {
        $this->assertSame([], $this->service->buildRecursiveRelationPaths([]));
    }

    /**
     * @return void
     */
    public function test_is_applicable_null_sentinel_field_returns_true_for_valid_field(): void
    {
        $this->assertTrue($this->service->isApplicableNullSentinelField('name', 'null', ['name', 'email']));
    }

    /**
     * @return void
     */
    public function test_is_applicable_null_sentinel_field_returns_false_for_non_null_value(): void
    {
        $this->assertFalse($this->service->isApplicableNullSentinelField('name', 'some_value', ['name']));
    }

    /**
     * @return void
     */
    public function test_is_applicable_null_sentinel_field_returns_false_for_dotted_field(): void
    {
        $this->assertFalse($this->service->isApplicableNullSentinelField('user.name', 'null', ['user.name']));
    }

    /**
     * @return void
     */
    public function test_is_applicable_null_sentinel_field_returns_false_for_non_fillable_field(): void
    {
        $this->assertFalse($this->service->isApplicableNullSentinelField('name', 'null', ['email']));
    }

    /**
     * @return void
     */
    public function test_apply_relations_calls_with_when_relations_not_empty(): void
    {
        $this->repo->shouldReceive('with')->once()->with(Mockery::any(), ['user', 'profile']);
        $this->repo->shouldNotReceive('withCount');

        $query = Mockery::mock(Builder::class);
        $this->service->applyRelations($query, ['user', 'profile'], []);
    }

    /**
     * @return void
     */
    public function test_apply_relations_calls_with_count_when_counts_not_empty(): void
    {
        $this->repo->shouldNotReceive('with');
        $this->repo->shouldReceive('withCount')->once()->with(Mockery::any(), ['jobPositions']);

        $query = Mockery::mock(Builder::class);
        $this->service->applyRelations($query, [], ['jobPositions']);
    }

    /**
     * @return void
     */
    public function test_apply_relations_calls_neither_when_both_empty(): void
    {
        $this->repo->shouldNotReceive('with');
        $this->repo->shouldNotReceive('withCount');

        $query = Mockery::mock(Builder::class);
        $this->service->applyRelations($query, [], []);
    }

    /**
     * @return void
     */
    public function test_apply_null_sentinels_returns_params_unchanged_when_empty(): void
    {
        $query = Mockery::mock(Builder::class);

        $result = $this->service->applyNullSentinels($query, [], ['name']);

        $this->assertSame([], $result);
    }

    /**
     * @return void
     */
    public function test_apply_null_sentinels_applies_where_null_and_removes_field(): void
    {
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('qualifyColumn')->with('name')->andReturn('users.name');

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($model);

        $this->repo->shouldReceive('whereNull')->once()->with($query, 'users.name');

        $result = $this->service->applyNullSentinels($query, ['name' => 'null'], ['name']);

        $this->assertArrayNotHasKey('name', $result);
    }

    /**
     * @return void
     */
    public function test_apply_null_sentinels_applies_where_not_null_for_not_null_value(): void
    {
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('qualifyColumn')->with('name')->andReturn('users.name');

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($model);

        $this->repo->shouldReceive('whereNotNull')->once()->with($query, 'users.name');

        $result = $this->service->applyNullSentinels($query, ['name' => 'not_null'], ['name']);

        $this->assertArrayNotHasKey('name', $result);
    }

    /**
     * @return void
     */
    public function test_apply_null_sentinels_skips_non_applicable_fields(): void
    {
        $model = Mockery::mock(Model::class);

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($model);

        $this->repo->shouldNotReceive('whereNull');
        $this->repo->shouldNotReceive('whereNotNull');

        $result = $this->service->applyNullSentinels($query, ['name' => 'value'], ['name']);

        $this->assertSame(['name' => 'value'], $result);
    }

    /**
     * @return void
     */
    public function test_apply_direct_sort_orders_by_table_field_when_in_fillable_fields(): void
    {
        $model = Mockery::mock(Model::class);
        $model->shouldReceive('getTable')->andReturn('users');

        $query = Mockery::mock(Builder::class);
        $query->shouldReceive('getModel')->andReturn($model);

        $this->repo->shouldReceive('orderBy')->once()->with($query, 'users.name', 'asc');

        $this->service->applyDirectSort($query, 'name', 'asc', ['name', 'email'], []);
    }

    /**
     * @return void
     */
    public function test_apply_direct_sort_does_nothing_when_field_not_in_fillable_fields(): void
    {
        $query = Mockery::mock(Builder::class);

        $this->repo->shouldNotReceive('orderBy');

        $this->service->applyDirectSort($query, 'unknown', 'asc', ['name', 'email'], []);
    }

    /**
     * @return void
     */
    public function test_apply_direct_sort_orders_by_count_field_for_relation_count(): void
    {
        $query = Mockery::mock(Builder::class);

        $this->repo->shouldReceive('orderBy')->once()->with($query, 'job_positions_count', 'desc');

        $this->service->applyDirectSort($query, 'job_positions_count', 'desc', ['job_positions_count'], ['jobPositions']);
    }
}
