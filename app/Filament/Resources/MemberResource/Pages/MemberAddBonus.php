<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Models\Group;
use App\Models\Koinhistory;
use App\Models\Member;
use App\Models\Transaction;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class MemberAddBonus extends Page
{
    protected static string $resource = MemberResource::class;

    protected static string $view = 'filament.resources.member-resource.pages.member-add-bonus';

    protected static ?string $title = 'Bonus Koin';

    public Member $member;

    public ?float $nominal = 0;
    public ?float $b_trf = 0;
    public ?float $bonus = 0;
    public ?float $total = 0;
    public ?float $deposit = 0;
    public ?float $withdraw = 0;
    public ?string $status = 'pending';
    public ?int $bank_id = null;
    public ?string $type = null;
    public ?string $note = null;
    public ?string $username = null;
    public ?string $groupName = null;
    public ?array $dataStore = [];
    public ?int $operator = 0;

    public function mount(Member $record)
    {
        $record->load(['group']);

        $this->member = $record;
        $this->username = $record->username;
        $this->operator =  Auth::id();
        $this->groupName = $record->group->name;

        $this->form->fill([
            'username' => $this->username,
            'groupName' => $this->groupName
        ]);
    }

    public function save()
    {
        

        DB::transaction(function () {

            $this->total = $this->nominal;

            $member = Member::find($this->member->id);

            $firstDepo = 'X';

            // if($member->first_depo == 'Y'){
            //     $firstDepo = 'Y';
            //     $member->update(['first_depo'=>'N']);
            // }

            $transaction = Transaction::create([
                'group_id' =>   $this->member->group_id,
                'operator_id' => $this->operator,
                'member_id' => $this->member->id,
                'first_depo' => $firstDepo,
                'bank_id' => null,
                'total' => $this->total,
                'fee' => $this->b_trf,
                'bonus' => $this->nominal,
                'note' => 'Bonus Koin',
                'type' => 'bonus',
                'deposit' =>  0,
                'withdraw' => 0,
            ]);

            $group = Group::where('id', $this->member->group_id)->lockForUpdate()->first();
            $group->decrement('koin', $this->total);
            $koin = -$this->total;

            $history = Koinhistory::create([
                'group_id' => $this->member->group_id,
                'keterangan' => 'Bonus koin',
                'member_id' => $this->member->id,
                'koin' => $koin,
                'saldo' => $group->saldo,
                'operator_id' => $this->operator,
                
            ]);

            // $log = Logtransaksi::create([
                
            //     'operator_id' =>  $transaction->operator_id,
            //     'bank_id' =>  $transaction->bank_id,
            //     'type_transaksi' => 'TR',
            //     'type' =>  $transaction->type,
            //     'rekenin_name' => $bank->label,
            //     'deposit' => $transaction->type === 'deposit' ? $transaction->total : 0,
            //     'withdraw' => $transaction->type === 'withdraw' ? $transaction->total : 0,
            //     'saldo' => $bank->saldo,
            //     'note' =>  $this->member->name,
            // ]);
        });

       

        Notification::make()
            ->title('Transaksi berhasil ditambahkan!')
            ->success()
            ->send();

        return redirect()->route('filament.admin.resources.members.member-transaksi', ['record' => $this->member]);
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(2) // Secara otomatis membuat layout 2 kolom
                ->schema([
                    Forms\Components\TextInput::make('username')
                        ->label('Username')
                        ->disabled(),
                    Forms\Components\TextInput::make('groupName')
                        ->label('Group')
                        ->disabled(), 
                    Forms\Components\TextInput::make('nominal')
                        ->label('Nominal')
                        ->numeric()
                        ->extraAttributes([
                            'id' => 'nominal', // Tambahkan id untuk akses di JS
                        ]),


                ]),
        ];
    }
}
