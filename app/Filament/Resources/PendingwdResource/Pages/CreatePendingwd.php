<?php

namespace App\Filament\Resources\PendingwdResource\Pages;

use App\Filament\Resources\PendingwdResource;
use App\Models\Group;
use App\Models\Koinhistory;
use App\Models\Member;
use App\Models\Pendingwd;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreatePendingwd extends CreateRecord
{
    protected static string $resource = PendingwdResource::class;

    public ?Member $member = null;

   public function mount(): void
    {
        parent::mount();

        $username = request()->query('username');

        if (! $username) {
            Notification::make()
                ->title('Username tidak diisi')
                ->danger()
                ->send();

            redirect()->route('filament.admin.resources.pendingwds.index');
            return;
        }

        $this->member = Member::with('group')->where('id', $username)->first();

        if (! $this->member) {
            Notification::make()
                ->title('Username tidak ditemukan')
                ->danger()
                ->send();

            redirect()->route('filament.admin.resources.pendingwds.index');
            return;
        }

        $this->form->fill([
            'member'       => $this->member->id,
            'member_id'    => $this->member->username,
            'sitename'     => $this->member->group->name ?? '',
            'member_name'  => $this->member->name,
            'bank'         => $this->member->bank_name,
        ]);
    }

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

         

            $member = Member::with('group')->where('id', $data['member'])->first();
      

            $pendingwd = Pendingwd::create([
                'operator_id' => $data['operator_id'],
                'member_id' =>  $member->id,
                'sitename' =>  $member->group->name,
                'member_name' =>  $member->name,
                'bank' =>  $member->bank_name,
                'nominal' =>  $data['nominal'],
            ]);

            $group = Group::where('id', Auth::user()->group_id)->lockForUpdate()->first();

            $group->increment('koin', $data['nominal']);
            $group->decrement('saldo', $data['nominal']);

            $history = Koinhistory::create([
                'group_id' => Auth::user()->group_id,
                'keterangan' => 'Withdraw gantung',
                'member_id' =>   $member->id,
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
