<?php

namespace App\Filament\Resources\BankResource\Pages;

use App\Filament\Resources\BankResource;
use App\Filament\Resources\BankResource\Widgets\BankTotalBalance;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;

class ListBanks extends ListRecords
{
    protected static string $resource = BankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ActionGroup::make([
                Action::make('pindahDana')->label('Pindah Dana')
                    ->url(BankResource::getUrl('logPindahDana')),
                Action::make('expanse')->label('Expanse')
                     ->url(BankResource::getUrl('logExpanses')),
                Action::make('income')->label('Income')
                    ->url(BankResource::getUrl('logIncome')),
                Action::make('adjustment')->label('Adjustment')
                    ->url(BankResource::getUrl('logAdjustment')),
            ])->label('Log Bank')->button()->color('gray')->icon(''),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BankTotalBalance::class
        ];
    }
}
