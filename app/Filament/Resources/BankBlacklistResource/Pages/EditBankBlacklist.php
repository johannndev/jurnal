<?php

namespace App\Filament\Resources\BankBlacklistResource\Pages;

use App\Filament\Resources\BankBlacklistResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBankBlacklist extends EditRecord
{
    protected static string $resource = BankBlacklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
