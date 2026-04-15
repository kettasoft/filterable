<?php

namespace Kettasoft\Filterable\Tests\Feature\Engines\Attributes;

use Kettasoft\Filterable\Engines\Foundation\Attributes\Annotations\Regex;
use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Support\Payload;
use Kettasoft\Filterable\Tests\Models\Post;
use Kettasoft\Filterable\Tests\TestCase;

class RegexAttributeTest extends TestCase
{
    public function test_regex_attribute_allows_matching_value()
    {
        request()->merge([
            'status' => 'active',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['status'];

            #[Regex('/^[a-z]+$/')]
            public function status(Payload $payload)
            {
                $this->builder->where('status', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString("\"status\" = 'active'", $sql);
    }

    public function test_regex_attribute_skips_filter_when_value_does_not_match()
    {
        request()->merge([
            'status' => 'ACTIVE123',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['status'];

            #[Regex('/^[a-z]+$/')]
            public function status(Payload $payload)
            {
                $this->builder->where('status', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        // Filter should be skipped
        $this->assertStringNotContainsString('"status" =', $sql);
    }

    public function test_regex_attribute_validates_email_pattern()
    {
        request()->merge([
            'title' => 'test@example.com',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['title'];

            #[Regex('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/')]
            public function title(Payload $payload)
            {
                $this->builder->where('title', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString("\"title\" = 'test@example.com'", $sql);
    }

    public function test_regex_attribute_skips_filter_for_invalid_email()
    {
        request()->merge([
            'title' => 'not-an-email',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['title'];

            #[Regex('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/')]
            public function title(Payload $payload)
            {
                $this->builder->where('title', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringNotContainsString('"title" =', $sql);
    }

    public function test_regex_attribute_validates_numeric_pattern()
    {
        request()->merge([
            'views' => '12345',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['views'];

            #[Regex('/^\d+$/')]
            public function views(Payload $payload)
            {
                $this->builder->where('views', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString('"views"', $sql);
        $this->assertStringContainsString('12345', $sql);
    }

    public function test_regex_attribute_skips_filter_for_non_numeric_value_with_numeric_pattern()
    {
        request()->merge([
            'views' => 'abc',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['views'];

            #[Regex('/^\d+$/')]
            public function views(Payload $payload)
            {
                $this->builder->where('views', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringNotContainsString('"views" =', $sql);
    }

    public function test_regex_attribute_validates_slug_pattern()
    {
        request()->merge([
            'title' => 'hello-world-post',
        ]);

        $class = new class() extends Filterable {
            protected $filters = ['title'];

            #[Regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')]
            public function title(Payload $payload)
            {
                $this->builder->where('title', '=', $payload->value);
            }
        };

        $sql = Post::filter($class)->toRawSql();

        $this->assertStringContainsString("\"title\" = 'hello-world-post'", $sql);
    }
}
