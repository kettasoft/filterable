<?php

namespace Kettasoft\Filterable\Tests\Feature\Engines\Attributes\Authorizations;

// use Illuminate\Contracts\Auth\Access\Authorizable as AccessAuthorizable;
use Kettasoft\Filterable\Contracts\Authorizable;

class CanMakeFilter implements Authorizable
{
    public function authorize(): bool
    {
        return true;
    }
}
