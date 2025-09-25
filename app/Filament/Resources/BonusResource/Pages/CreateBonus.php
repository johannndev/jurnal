<?php

namespace App\Filament\Resources\BonusResource\Pages;

use App\Filament\Resources\BonusResource;
use App\Models\Bonus;
use App\Models\Group;
use App\Models\Koinhistory;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateBonus extends CreateRecord
{
    protected static string $resource = BonusResource::class;

     protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function handleRecordCreation(array $data): Bonus
    {
     

        return DB::transaction(function () use ($data) {
      

            // dd(Auth::user()->group_id);

            if(Auth::user()->group_id){
                $gid = Auth::user()->group_id;
            }else{
                $gid = $data['group_id'];
            }

            $bonus = Bonus::create([
                'operator_id' => Auth::id(),
                'member_id' =>  $data['member_id'],
                'group_id' => $gid,
                'nominal' =>  $data['nominal'],
            ]);

            $group = Group::where('id',$gid)->lockForUpdate()->first();

            $group->decrement('koin',$data['nominal']);

            $history = Koinhistory::create([
                'group_id' => $gid,
                'keterangan' => 'Bounus Koin',
                'member_id' =>   $data['member_id'],
                'koin' => -$data['nominal'],
                'saldo' => $group->saldo,
                'operator_id' => Auth::id(),
                
            ]);

            return  $bonus;

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
