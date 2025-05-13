<?php

namespace App\Filament\Resources\PendingwdResource\Pages;

use App\Filament\Resources\PendingwdResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendingwd extends EditRecord
{
    protected static string $resource = PendingwdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
