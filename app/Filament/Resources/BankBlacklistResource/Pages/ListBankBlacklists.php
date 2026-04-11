<?php

namespace App\Filament\Resources\BankBlacklistResource\Pages;

use App\Filament\Resources\BankBlacklistResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBankBlacklists extends ListRecords
{
    protected static string $resource = BankBlacklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
