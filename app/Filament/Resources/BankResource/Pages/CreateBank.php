<?php

namespace App\Filament\Resources\BankResource\Pages;

use App\Filament\Resources\BankResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBank extends CreateRecord
{
    protected static string $resource = BankResource::class;

    protected function afterCreate(): void
    {
        $this->record->label = $this->record->bankname->bank_nama."/".$this->record->bank_number."/".$this->record->bank_account_name;
        $this->record->save();

       
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
