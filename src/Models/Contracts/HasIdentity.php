<?php

namespace SMSkin\IdentityServiceClient\Models\Contracts;

use Illuminate\Contracts\Queue\QueueableEntity;
use SMSkin\IdentityServiceClient\Api\DTO\Identity\RIdentity;

interface HasIdentity extends QueueableEntity
{
    public function setIdentity(RIdentity $identity): void;

    public function getIdentity(): ?RIdentity;

    public function hasScope(string $scope): bool;
}
