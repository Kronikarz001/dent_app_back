<?php

namespace Tests\Unit\Searches;

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
    public function testResolveNullFilterReturnsNullForRegularString(): void
    {
        $this->assertNull($this->service->resolveNullFilter('some_value'));
    }

    /**
     * @return void
     */
    public function testResolveNullFilterReturnsNullSentinelForNull(): void
    {
        $this->assertSame('null', $this->service->resolveNullFilter(null));
    }

    /**
     * @return void
     */
    public function testResolveNullFilterReturnsNullSentinelForNullString(): void
    {
        $this->assertSame('null', $this->service->resolveNullFilter('null'));
    }

    /**
     * @return void
     */
    public function testResolveNullFilterReturnsNotNullForNotNullString(): void
    {
        $this->assertSame('not_null', $this->service->resolveNullFilter('not_null'));
    }

    /**
     * @return void
     */
    public function testResolveNullFilterReturnsNotNullForExclamationNull(): void
    {
        $this->assertSame('not_null', $this->service->resolveNullFilter('!null'));
    }

    /**
     * @return void
     */
    public function testResolveNullFilterReturnsNullForArrayWithNullValue(): void
    {
        $this->assertSame('null', $this->service->resolveNullFilter(['null']));
    }

    /**
     * @return void
     */
    public function testResolveNullFilterReturnsNullForArrayWithNullKey(): void
    {
        $this->assertSame('null', $this->service->resolveNullFilter(['null' => true]));
    }

    /**
     * @return void
     */
    public function testResolveNullFilterReturnsNotNullForArrayWithNotNullValue(): void
    {
        $this->assertSame('not_null', $this->service->resolveNullFilter(['not_null']));
    }

    /**
     * @return void
     */
    public function testResolveNullFilterReturnsNotNullForArrayWithExclamationNull(): void
    {
        $this->assertSame('not_null', $this->service->resolveNullFilter(['!null']));
    }

    /**
     * @return void
     */
    public function testResolveNullFilterReturnsNullForRegularArray(): void
    {
        $this->assertNull($this->service->resolveNullFilter(['value1', 'value2']));
    }

    /**
     * @return void
     */
    public function testIsJsonFieldReturnsTrueForAllowedJsonField(): void
    {
        $this->assertTrue($this->service->isJsonField('data.name', ['data']));
    }

    /**
     * @return void
     */
    public function testIsJsonFieldReturnsFalseForEmptyAllowedFields(): void
    {
        $this->assertFalse($this->service->isJsonField('data.name', []));
    }

    /**
     * @return void
     */
    public function testIsJsonFieldReturnsFalseForNotAllowedField(): void
    {
        $this->assertFalse($this->service->isJsonField('other.name', ['data']));
    }

    /**
     * @return void
     */
    public function testJsonPathFromFieldConvertsDotsToArrows(): void
    {
        $this->assertSame('data->name->value', $this->service->jsonPathFromField('data.name.value'));
    }

    /**
     * @return void
     */
    public function testCanFilterByRelationReturnsTrueForExactMatch(): void
    {
        $this->assertTrue($this->service->canFilterByRelation('user', ['user', 'patient'], []));
    }

    /**
     * @return void
     */
    public function testCanFilterByRelationReturnsTrueForNestedLoadedRelation(): void
    {
        $this->assertTrue($this->service->canFilterByRelation('user', ['user.profile'], []));
    }

    /**
     * @return void
     */
    public function testCanFilterByRelationReturnsTrueForRecursiveRelation(): void
    {
        $this->assertTrue($this->service->canFilterByRelation('children', [], ['children' => 3]));
    }

    /**
     * @return void
     */
    public function testCanFilterByRelationReturnsFalseForUnknownRelation(): void
    {
        $this->assertFalse($this->service->canFilterByRelation('unknown', ['user'], []));
    }

    /**
     * @return void
     */
    public function testIsRelationCountFieldReturnsTrueForMatchingField(): void
    {
        $this->assertTrue($this->service->isRelationCountField('job_positions_count', ['jobPositions']));
    }

    /**
     * @return void
     */
    public function testIsRelationCountFieldReturnsFalseForNonMatchingField(): void
    {
        $this->assertFalse($this->service->isRelationCountField('other_count', ['jobPositions']));
    }

    /**
     * @return void
     */
    public function testGetSortDirectionReturnsAscForConfiguredCharacter(): void
    {
        config(['search.sort_asc_default_character' => 'asc']);

        $this->assertSame('asc', $this->service->getSortDirection('asc'));
    }

    /**
     * @return void
     */
    public function testGetSortDirectionReturnsDescForOtherCharacter(): void
    {
        config(['search.sort_asc_default_character' => 'asc']);

        $this->assertSame('desc', $this->service->getSortDirection('desc'));
    }

    /**
     * @return void
     */
    public function testBuildRecursiveRelationPathsGeneratesCorrectDepthPaths(): void
    {
        $paths = $this->service->buildRecursiveRelationPaths(['children' => 3]);

        $this->assertSame(['children', 'children.children', 'children.children.children'], $paths);
    }

    /**
     * @return void
     */
    public function testBuildRecursiveRelationPathsGeneratesSinglePathForDepthOne(): void
    {
        $paths = $this->service->buildRecursiveRelationPaths(['children' => 1]);

        $this->assertSame(['children'], $paths);
    }

    /**
     * @return void
     */
    public function testBuildRecursiveRelationPathsSkipsZeroDepth(): void
    {
        $paths = $this->service->buildRecursiveRelationPaths(['children' => 0]);

        $this->assertSame([], $paths);
    }

    /**
     * @return void
     */
    public function testBuildRecursiveRelationPathsReturnsEmptyForEmptyInput(): void
    {
        $this->assertSame([], $this->service->buildRecursiveRelationPaths([]));
    }

    /**
     * @return void
     */
    public function testIsApplicableNullSentinelFieldReturnsTrueForValidField(): void
    {
        $this->assertTrue($this->service->isApplicableNullSentinelField('name', 'null', ['name', 'email']));
    }

    /**
     * @return void
     */
    public function testIsApplicableNullSentinelFieldReturnsFalseForNonNullValue(): void
    {
        $this->assertFalse($this->service->isApplicableNullSentinelField('name', 'some_value', ['name']));
    }

    /**
     * @return void
     */
    public function testIsApplicableNullSentinelFieldReturnsFalseForDottedField(): void
    {
        $this->assertFalse($this->service->isApplicableNullSentinelField('user.name', 'null', ['user.name']));
    }

    /**
     * @return void
     */
    public function testIsApplicableNullSentinelFieldReturnsFalseForNonFillableField(): void
    {
        $this->assertFalse($this->service->isApplicableNullSentinelField('name', 'null', ['email']));
    }

    /**
     * @return void
     */
    public function testApplyRelationsCallsWithWhenRelationsNotEmpty(): void
    {
        $this->repo->shouldReceive('with')->once()->with(Mockery::any(), ['user', 'profile']);
        $this->repo->shouldNotReceive('withCount');

        $query = Mockery::mock(Builder::class);
        $this->service->applyRelations($query, ['user', 'profile'], []);
    }

    /**
     * @return void
     */
    public function testApplyRelationsCallsWithCountWhenCountsNotEmpty(): void
    {
        $this->repo->shouldNotReceive('with');
        $this->repo->shouldReceive('withCount')->once()->with(Mockery::any(), ['jobPositions']);

        $query = Mockery::mock(Builder::class);
        $this->service->applyRelations($query, [], ['jobPositions']);
    }

    /**
     * @return void
     */
    public function testApplyRelationsCallsNeitherWhenBothEmpty(): void
    {
        $this->repo->shouldNotReceive('with');
        $this->repo->shouldNotReceive('withCount');

        $query = Mockery::mock(Builder::class);
        $this->service->applyRelations($query, [], []);
    }

    /**
     * @return void
     */
    public function testApplyNullSentinelsReturnsParamsUnchangedWhenEmpty(): void
    {
        $query = Mockery::mock(Builder::class);

        $result = $this->service->applyNullSentinels($query, [], ['name']);

        $this->assertSame([], $result);
    }

    /**
     * @return void
     */
    public function testApplyNullSentinelsAppliesWhereNullAndRemovesField(): void
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
    public function testApplyNullSentinelsAppliesWhereNotNullForNotNullValue(): void
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
    public function testApplyNullSentinelsSkipsNonApplicableFields(): void
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
    public function testApplyDirectSortOrdersByTableFieldWhenInFillableFields(): void
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
    public function testApplyDirectSortDoesNothingWhenFieldNotInFillableFields(): void
    {
        $query = Mockery::mock(Builder::class);

        $this->repo->shouldNotReceive('orderBy');

        $this->service->applyDirectSort($query, 'unknown', 'asc', ['name', 'email'], []);
    }

    /**
     * @return void
     */
    public function testApplyDirectSortOrdersByCountFieldForRelationCount(): void
    {
        $query = Mockery::mock(Builder::class);

        $this->repo->shouldReceive('orderBy')->once()->with($query, 'job_positions_count', 'desc');

        $this->service->applyDirectSort($query, 'job_positions_count', 'desc', ['job_positions_count'], ['jobPositions']);
    }
}
