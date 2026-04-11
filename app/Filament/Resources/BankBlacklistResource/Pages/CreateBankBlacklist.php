<?php

namespace App\Filament\Resources\BankBlacklistResource\Pages;

use App\Filament\Resources\BankBlacklistResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBankBlacklist extends CreateRecord
{
    protected static string $resource = BankBlacklistResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
