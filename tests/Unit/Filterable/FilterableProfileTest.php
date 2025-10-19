<?php

namespace Kettasoft\Filterable\Tests\Unit\Filterable;

use Kettasoft\Filterable\Filterable;
use Kettasoft\Filterable\Tests\TestCase;
use Kettasoft\Filterable\Foundation\Contracts\FilterableProfile;

class FilterableProfileTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    public function test_it_uses_a_filterable_profile_by_instance()
    {
        $profile = new class implements FilterableProfile {
            public function __invoke(Filterable $context): Filterable
            {
                $context->strict();
                $context->setAllowedFields(['field1', 'field2']);
                return $context;
            }
        };

        $filter = Filterable::create()->useProfile($profile);

        $this->assertInstanceOf(Filterable::class, $filter);
        $this->assertEquals(['field1', 'field2'], $filter->getAllowedFields());
        $this->assertTrue($filter->isStrict());
    }

    public function test_it_uses_a_filterable_profile_by_class_name()
    {
        $profileClass = new class implements FilterableProfile {
            public function __invoke(Filterable $context): Filterable
            {
                $context->permissive();
                $context->setAllowedFields(['fieldA', 'fieldB', 'fieldC']);
                return $context;
            }
        };

        $filter = Filterable::create()->useProfile(get_class($profileClass));

        $this->assertInstanceOf(Filterable::class, $filter);
        $this->assertEquals(['fieldA', 'fieldB', 'fieldC'], $filter->getAllowedFields());
        $this->assertFalse($filter->isStrict());
    }

    public function test_it_uses_a_filterable_profile_by_registred_name()
    {
        $profileClass = new class implements FilterableProfile {
            public function __invoke(Filterable $context): Filterable
            {
                $context->strict();
                $context->setAllowedFields(['name', 'email']);
                return $context;
            }
        };

        config()->set('filterable.profiles.users-global', get_class($profileClass));


        $filter = Filterable::create()->useProfile('users-global');

        $this->assertInstanceOf(Filterable::class, $filter);
        $this->assertEquals(['name', 'email'], $filter->getAllowedFields());
        $this->assertTrue($filter->isStrict());
    }
}
