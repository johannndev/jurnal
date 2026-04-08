<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

use App\Models\Group;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!isset($data['group_id']) || empty($data['group_id'])) {
            $data['group_id'] = auth()->user()->group_id > 0 ? auth()->user()->group_id : Group::getActiveGroupId();
        }

        return $data;
    }

    public function getTitle(): string
    {
        return 'Create Operator'; // Ubah heading di halaman user
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
