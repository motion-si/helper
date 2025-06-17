<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected ?int $roleId = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $roleId = $data['role_id'];
        unset($data['role_id']);
        $this->roleId = $roleId;

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->syncRoles([$this->roleId]);
        $this->record->refresh();
    }
}
