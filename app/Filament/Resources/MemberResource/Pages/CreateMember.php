<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Models\BankBlacklist;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\HtmlString;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    public $blacklistId = null;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label(__('filament-panels::resources/pages/create-record.form.actions.create.label'))
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

                    $this->create();
                }),
            $this->getCancelFormAction(),
        ];
    }
}
