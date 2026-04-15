<?php

namespace Kettasoft\Filterable\Tests\Feature\Engines\Attributes;

use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Trim;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

class TrimAttributeTest extends TestCase
{
    public function test_trim_attribute_trims_whitespace_from_both_sides()
    {
        request()->merge([
            'title' => '  hello world  ',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['title'];

            #[Trim]
            public function title(Payload $payload)
            {
                $this->builder->where('title', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString("\"title\" = 'hello world'", $sql);
    }

    public function test_trim_attribute_trims_left_only()
    {
        request()->merge([
            'title' => '  hello world  ',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['title'];

            #[Trim(side: 'left')]
            public function title(Payload $payload)
            {
                $this->builder->where('title', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString("\"title\" = 'hello world  '", $sql);
    }

    public function test_trim_attribute_trims_right_only()
    {
        request()->merge([
            'title' => '  hello world  ',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['title'];

            #[Trim(side: 'right')]
            public function title(Payload $payload)
            {
                $this->builder->where('title', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString("\"title\" = '  hello world'", $sql);
    }

    public function test_trim_attribute_trims_custom_characters()
    {
        request()->merge([
            'title' => '---hello world---',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['title'];

            #[Trim(characters: '-')]
            public function title(Payload $payload)
            {
                $this->builder->where('title', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString("\"title\" = 'hello world'", $sql);
    }

    public function test_trim_attribute_does_not_affect_non_string_values()
    {
        request()->merge([
            'views' => '42',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['views'];

            #[Trim]
            public function views(Payload $payload)
            {
                $this->builder->where('views', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString('"views"', $sql);
    }
}
