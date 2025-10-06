<?php

namespace App\Filament\Resources\BatchBonusResource\Pages;

use App\Filament\Resources\BatchBonusResource;
use App\Models\Group;
use App\Models\Koinhistory;
use App\Models\Member;
use App\Models\Transaction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateBatchBonus extends CreateRecord
{
    protected static string $resource = BatchBonusResource::class;
    protected static string $view = 'filament.resources.batch-bonus.create';

    protected static ?string $title = 'Batch Bonus Koin';

    public $selectedGroup;
    public $attachment;
    public $countExists = 0;
    public $countNotExists = 0;
    public $show = false;
    public $dataArray;
    public $proses = false;


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function submitBonus()
    {
        $data = $this->form->getState();

        $this->handleRecordCreation($data);

        Notification::make()
            ->title('Batch bonus berhasil ditambahkan!')
            ->success()
            ->send();

        return redirect()->route('filament.admin.resources.batch-bonuses.create');
    }

    public function mount(): void
    {
        parent::mount();

        $user = Auth::user();

        // Set default value
        if( $user->group_id !== null){
            $this->form->fill([
                'selectedGroup' => $user->group_id,
            ]);
        }
        
    }

    protected function handleRecordCreation(array $data): Model
    {
   

        // dd($this->dataArray,$this->selectedGroup);


        if (!$this->dataArray || !$this->selectedGroup) {
            Notification::make()
                ->title('File atau Group belum dipilih!')
                ->danger()
                ->send();

            return new Transaction();
        }

        DB::transaction(function () use ($data) {

            $transactionsArray = [];
            foreach ($this->dataArray as $i => $member) {
                $transactionsArray[] = [
                    'group_id' => $this->selectedGroup,
                    'operator_id' => Auth::id(),
                    'member_id' => $member['id'],
                    'first_depo' => 'X',
                    'bank_id' => null,
                    'total' => $member['nominal'],
                    'fee' => 0,
                    'bonus' => $member['nominal'],
                    'note' => 'Bonus '.$data['bonus'],
                    'type' => 'bonus',
                    'deposit' => 0,
                    'withdraw' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (empty($transactionsArray)) {
                Notification::make()
                    ->title('Tidak ada member valid untuk group ini!')
                    ->danger()
                    ->send();

                return;
            }

            // Insert Transaction
            Transaction::insert($transactionsArray);

            $bonustotal = array_sum(array_column($transactionsArray, 'total'));

            // Lock group untuk update koin
            $group = Group::where('id', $this->selectedGroup)->lockForUpdate()->first();
            $group->decrement('koin', $bonustotal);

            // Insert Koinhistory
            $koinHistory = [];
            foreach ($transactionsArray as $row) {
                $koinHistory[] = [
                    'group_id' => $row['group_id'],
                    'keterangan' => 'Bonus '.$data['bonus'],
                    'member_id' => $row['member_id'],
                    'koin' => -$row['total'],
                    'saldo' => $group->koin,
                    'operator_id' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            Koinhistory::insert($koinHistory);

        });

        // Kembalikan dummy Transaction agar handleRecordCreation tidak error
        return new Transaction();
        
    }
}
