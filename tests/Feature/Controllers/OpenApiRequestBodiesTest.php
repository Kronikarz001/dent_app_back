<?php

namespace Tests\Feature\Controllers;

use App\Http\Controllers\Controller;
use OpenApi\Annotations\Operation;
use OpenApi\Attributes as OA;
use OpenApi\Undefined;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Summary of OpenApiRequestBodiesTest
 */
class OpenApiRequestBodiesTest extends TestCase
{
    /**
     * @var string
     */
    private const REF_PREFIX = '#/components/requestBodies/';

    /**
     * @return void
     */
    public function testEveryReferencedRequestBodyIsDefined(): void
    {
        $defined = $this->definedRequestBodies();

        foreach ($this->referencedRequestBodies() as $location => $name) {
            $this->assertContains($name, $defined, "Request body [{$name}] used in [{$location}] is not defined in Controller.");
        }
    }

    /**
     * @return void
     */
    public function testEveryDefinedRequestBodyIsUsed(): void
    {
        $referenced = array_values($this->referencedRequestBodies());

        foreach ($this->definedRequestBodies() as $name) {
            $this->assertContains($name, $referenced, "Request body [{$name}] is defined in Controller but never used.");
        }
    }

    /**
     * @return string[]
     */
    private function definedRequestBodies(): array
    {
        return array_map(
            fn ($attribute) => $attribute->newInstance()->request,
            (new ReflectionClass(Controller::class))->getAttributes(OA\RequestBody::class)
        );
    }

    /**
     * @return array<string, string>
     */
    private function referencedRequestBodies(): array
    {
        $references = [];

        foreach (glob(app_path('Http/Controllers/*Controller.php')) as $file) {
            $class = 'App\\Http\\Controllers\\'.basename($file, '.php');

            foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                foreach ($method->getAttributes() as $attribute) {
                    if (! is_subclass_of($attribute->getName(), Operation::class)) {
                        continue;
                    }

                    $ref = $attribute->newInstance()->requestBody->ref ?? Undefined::UNDEFINED;

                    if (is_string($ref) && str_starts_with($ref, self::REF_PREFIX)) {
                        $references["{$class}::{$method->getName()}"] = substr($ref, strlen(self::REF_PREFIX));
                    }
                }
            }
        }

        $this->assertNotEmpty($references);

        return $references;
    }
}
