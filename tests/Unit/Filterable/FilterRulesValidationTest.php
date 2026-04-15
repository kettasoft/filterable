<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Illuminate\Validation\ValidationException;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

class FilterRulesValidationTest extends TestCase
{
    /**
     * It validate incomming request before filtering.
     *
     * @test
     */
    public function it_validate_incomming_reuqest_before_filtering()
    {
        $class = new class() extends Filterable {
            public function rules(): array
            {
                return [
                    'id' => ['required', 'array'],
                ];
            }
        };

        $request = request()->merge([
            'id' => null,
        ]);

        $this->assertThrows(function () use ($class, $request) {
            $result = Post::filter($class->withRequest($request));
        }, ValidationException::class);
    }
}
