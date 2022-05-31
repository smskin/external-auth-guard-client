<?php

namespace SMSkin\IdentityServiceClient\Enums\Models;

use Illuminate\Support\Collection;
use SMSkin\LaravelSupport\Models\EnumItem;

class ScopeGroup extends EnumItem
{
    /**
     * @var Collection<EnumItem>
     */
    public Collection $scopes;

    /**
     * @param Collection<EnumItem> $scopes
     * @return ScopeGroup
     */
    public function setScopes(Collection $scopes): ScopeGroup
    {
        $this->scopes = $scopes;
        return $this;
    }
}