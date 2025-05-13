<?php

namespace App\Filament\Resources\PendingDepoResource\Pages;

use App\Filament\Resources\PendingDepoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPendingDepo extends EditRecord
{
    protected static string $resource = PendingDepoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
