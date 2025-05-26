<?php

namespace App\Filament\Resources\PendingwdResource\Pages;

use App\Filament\Resources\PendingwdResource;
use App\Models\Group;
use App\Models\Koinhistory;
use App\Models\Pendingwd;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    protected function handleRecordCreation(array $data): Pendingwd
    {
     

        return DB::transaction(function () use ($data) {

            $pendingwd = Pendingwd::create($data);

            $group = Group::where('id', Auth::user()->group_id)->lockForUpdate()->first();

            $group->increment('koin', $data['nominal']);
            $group->decrement('saldo', $data['nominal']);

            $history = Koinhistory::create([
                'group_id' => Auth::user()->group_id,
                'keterangan' => 'Withdraw gantung',
                'member_id' =>  $data['member_id'],
                'koin' => $data['nominal'],
                'saldo' => $group->saldo,
                'operator_id' => Auth::id(),
                
            ]);

            return  $pendingwd;

        });

    }

     protected function handleRecordCreationException(\Throwable $e): void
    {
        Notification::make()
            ->title('Gagal menyimpan data')
            ->body('Terjadi kesalahan: ' . $e->getMessage())
            ->danger()
            ->send();

        parent::handleRecordCreationException($e);
    }


}
