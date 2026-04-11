<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Models\BankBlacklist;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditMember extends EditRecord
{
    protected static string $resource = MemberResource::class;

    public $blacklistId = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->keyBindings(['mod+s'])
                ->action(function () {
                    $this->form->validate();
                    $data = $this->form->getRawState();
                    
                    $blacklist = BankBlacklist::with('bankname')->where('nomor_rekening', $data['bank_number'] ?? null)->first();

                    if ($blacklist) {
                        $this->blacklistId = $blacklist->id;
                        $this->dispatch('open-modal', id: 'blacklist-modal');
                        return;
                    }

                    $this->save();
                }),
            $this->getCancelFormAction(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
