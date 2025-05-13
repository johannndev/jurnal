<?php

namespace App\Filament\Resources\PendingDepoResource\Pages;

use App\Filament\Resources\PendingDepoResource;
use App\Models\Bank;
use App\Models\Group;
use App\Models\Koinhistory;
use App\Models\Logtransaksi;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreatePendingDepo extends CreateRecord
{
    protected static string $resource = PendingDepoResource::class;

     protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 1;
        $data['type'] = 2;
        $data['operator_id'] = Auth::id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $group = Group::where('id', Auth::user()->group_id)->lockForUpdate()->first();
        $bank = Bank::where('id', $this->record->bank_id)->lockForUpdate()->first();

        $bank->increment('saldo',  $this->record->nominal);
        $group->increment('saldo',  $this->record->nominal);

        $log = Logtransaksi::create([
            'operator_id' =>  Auth::id(),
            'bank_id' =>  $this->record->bank_id,
            'type_transaksi' => 'DPP',
            'type' =>  'deposite',
            'rekenin_name' => $this->record->bank->label,
            'deposit' => $this->record->nominal,
            'withdraw' => 0,
            'saldo' => $bank->saldo,
            'note' =>  'DP Gantung',
        ]);


       
    }
}
