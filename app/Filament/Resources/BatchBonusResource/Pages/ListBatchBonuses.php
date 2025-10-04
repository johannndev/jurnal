<?php

namespace App\Filament\Resources\BatchBonusResource\Pages;

use App\Filament\Resources\BatchBonusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBatchBonuses extends ListRecords
{
    protected static string $resource = BatchBonusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
