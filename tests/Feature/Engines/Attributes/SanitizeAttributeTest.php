<?php

namespace Kettasoft\Filterable\Tests\Feature\Engines\Attributes;

use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Sanitize;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

class SanitizeAttributeTest extends TestCase
{
    public function test_sanitize_attribute_converts_to_lowercase()
    {
        request()->merge([
            'status' => 'ACTIVE',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['status'];

            #[Sanitize('lowercase')]
            public function status(Payload $payload)
            {
                $this->builder->where('status', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString("\"status\" = 'active'", $sql);
    }

    public function test_sanitize_attribute_converts_to_uppercase()
    {
        request()->merge([
            'status' => 'active',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['status'];

            #[Sanitize('uppercase')]
            public function status(Payload $payload)
            {
                $this->builder->where('status', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString("\"status\" = 'ACTIVE'", $sql);
    }

    public function test_sanitize_attribute_applies_ucfirst()
    {
        request()->merge([
            'title' => 'hello world',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['title'];

            #[Sanitize('ucfirst')]
            public function title(Payload $payload)
            {
                $this->builder->where('title', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString("\"title\" = 'Hello world'", $sql);
    }

    public function test_sanitize_attribute_strips_html_tags()
    {
        request()->merge([
            'title' => '<b>hello</b> <i>world</i>',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['title'];

            #[Sanitize('strip_tags')]
            public function title(Payload $payload)
            {
                $this->builder->where('title', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString("\"title\" = 'hello world'", $sql);
    }

    public function test_sanitize_attribute_applies_multiple_rules_in_order()
    {
        request()->merge([
            'status' => '  <b>ACTIVE</b>  ',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['status'];

            #[Sanitize('trim', 'strip_tags', 'lowercase')]
            public function status(Payload $payload)
            {
                $this->builder->where('status', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString("\"status\" = 'active'", $sql);
    }

    public function test_sanitize_attribute_converts_to_slug()
    {
        request()->merge([
            'title' => 'Hello World Post',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['title'];

            #[Sanitize('slug')]
            public function title(Payload $payload)
            {
                $this->builder->where('title', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString("\"title\" = 'hello-world-post'", $sql);
    }

    public function test_sanitize_attribute_does_not_affect_non_string_values()
    {
        request()->merge([
            'views' => '42',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['views'];

            #[Sanitize('lowercase')]
            public function views(Payload $payload)
            {
                $this->builder->where('views', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString('"views"', $sql);
    }
}
