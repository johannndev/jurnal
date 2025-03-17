<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return 'Operator'; // Ubah heading di halaman user
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Create Operator'),
        ];
    }
}
