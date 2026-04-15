<?php

namespace Kettasoft\Filterable\Tests\Feature\Engines\Attributes;

use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\MapValue;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

class MapValueAttributeTest extends TestCase
{
    public function test_map_value_attribute_maps_value_to_mapped_value()
    {
        request()->merge([
            'status' => 'active',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['status'];

            #[MapValue(['active' => 1, 'inactive' => 0])]
            public function status(Payload $payload)
            {
                $this->builder->where('status', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString('"status" = 1', $sql);
    }

    public function test_map_value_attribute_maps_inactive_to_zero()
    {
        request()->merge([
            'status' => 'inactive',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['status'];

            #[MapValue(['active' => 1, 'inactive' => 0])]
            public function status(Payload $payload)
            {
                $this->builder->where('status', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString('"status" = 0', $sql);
    }

    public function test_map_value_attribute_keeps_original_value_when_not_in_map_non_strict()
    {
        request()->merge([
            'status' => 'pending',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['status'];

            #[MapValue(['active' => 1, 'inactive' => 0])]
            public function status(Payload $payload)
            {
                $this->builder->where('status', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString("\"status\" = 'pending'", $sql);
    }

    public function test_map_value_attribute_skips_filter_in_strict_mode_when_not_in_map()
    {
        request()->merge([
            'status' => 'unknown',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['status'];

            #[MapValue(['active' => 1, 'inactive' => 0], strict: true)]
            public function status(Payload $payload)
            {
                $this->builder->where('status', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        // Filter should be skipped entirely, so no where clause
        $this->assertStringNotContainsString('"status" =', $sql);
    }

    public function test_map_value_attribute_maps_string_to_string()
    {
        request()->merge([
            'status' => 'published',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['status'];

            #[MapValue(['published' => 'live', 'draft' => 'hidden'])]
            public function status(Payload $payload)
            {
                $this->builder->where('status', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString("\"status\" = 'live'", $sql);
    }
}
