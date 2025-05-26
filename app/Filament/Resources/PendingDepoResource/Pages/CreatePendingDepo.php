<?php

namespace App\Filament\Resources\PendingDepoResource\Pages;

use App\Filament\Resources\PendingDepoResource;
use App\Models\Bank;
use App\Models\Group;
use App\Models\Koinhistory;
use App\Models\Logtransaksi;
use App\Models\Pendingdepo;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreatePendingDepo extends CreateRecord
{
    protected static string $resource = PendingDepoResource::class;

     protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['operator_id'] = Auth::id();
        return $data;
    }

    protected function handleRecordCreation(array $data): Pendingdepo
    {
        return DB::transaction(function () use ($data) {
            // Buat record pending depo
            $pendingDepo = Pendingdepo::create($data);

            // Lock untuk konsistensi saldo
            $group = Group::where('id', Auth::user()->group_id)->lockForUpdate()->first();
            $bank = Bank::where('id', $data['bank_id'])->lockForUpdate()->first();

            // Update saldo
            $bank->increment('saldo', $data['nominal']);

            // Simpan log transaksi
            Logtransaksi::create([
                'operator_id'     => Auth::id(),
                'bank_id'         => $data['bank_id'],
                'type_transaksi'  => 'DPT', // Deposit Pending Transaction
                'type'            => 'deposit',
                'rekenin_name'    => $bank->label,
                'deposit'         => $data['nominal'],
                'withdraw'        => 0,
                'saldo'           => $bank->saldo,
                'note'            => 'Pending Deposit',
            ]);

            return $pendingDepo; // return jika semua berhasil
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
