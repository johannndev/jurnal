<?php

namespace App\Filament\Resources\BankResource\Pages;

use App\Filament\Resources\BankResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBank extends EditRecord
{
    protected static string $resource = BankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->record->label = $this->record->bankname->bank_nama."/".$this->record->bank_number."/".$this->record->bank_account_name;
        $this->record->save();

       
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
