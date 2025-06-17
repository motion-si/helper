<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected ?int $roleId = null;

    protected function getActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->roleId = $data['role_id'];
        unset($data['role_id']);
        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['role_id'] = $this->record->roles->first()->id ?? null;

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->syncRoles([$this->roleId]);
        $this->record->refresh();
    }
}
