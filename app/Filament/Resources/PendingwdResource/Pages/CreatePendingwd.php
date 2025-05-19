<?php

namespace App\Filament\Resources\PendingwdResource\Pages;

use App\Filament\Resources\PendingwdResource;
use App\Models\Group;
use App\Models\Koinhistory;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePendingwd extends CreateRecord
{
    protected static string $resource = PendingwdResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
      
        $data['operator_id'] = Auth::id();

        return $data;
    }

    protected function afterCreate(): void
    {


        $group = Group::where('id', Auth::user()->group_id)->lockForUpdate()->first();

        $group->increment('koin', $this->record->nominal);

        $history = Koinhistory::create([
            'group_id' => Auth::user()->group_id,
            'keterangan' => 'Withdraw gantung',
            'member_id' => 0,
            'koin' => $this->record->nominal,
            'saldo' => $group->saldo,
            'operator_id' => Auth::id(),
            
        ]);
    }
}
