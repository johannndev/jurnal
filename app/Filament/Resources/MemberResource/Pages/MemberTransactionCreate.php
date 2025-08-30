<?php

namespace App\Filament\Resources\MemberResource\Pages;

use Filament\Forms;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use App\Filament\Resources\MemberResource;
use App\Models\Bank;
use App\Models\Group;
use App\Models\Koinhistory;
use App\Models\Logtransaksi;
use App\Models\Transaction;
use App\Models\Member;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MemberTransactionCreate extends Page
{
    protected static string $resource = MemberResource::class;

    protected static string $view = 'filament.resources.member-resource.pages.member-transaction-create';

    public Member $member;

    public ?float $amount = 0;
    public ?float $b_trf = 0;
    public ?float $bonus = 0;
    public ?float $total = 0;
    public ?float $deposit = 0;
    public ?float $withdraw = 0;
    public ?string $status = 'pending';
    public ?int $bank_id = null;
    public ?string $type = null;
    public ?string $note = null;
    public ?array $dataStore = [];
    public ?int $operator = 0;

    public function mount(Member $record)
    {
        $this->member = $record;
        $this->type = request()->query('type'); 
        $this->operator =  Auth::id();
    }
    

    public function save()
    {
        $this->total = (float) ($this->amount - $this->b_trf + $this->bonus);

        if (!$this->bank_id || !Bank::find($this->bank_id)) {
            Notification::make()
                ->title('Bank tidak ditemukan!')
                ->danger()
                ->send();
            return;
        }

        DB::transaction(function () {

            $member = Member::find($this->member->id);

            $firstDepo = 'N';

            if($member->first_depo == 'Y'){
                $firstDepo = 'Y';
                $member->update(['first_depo'=>'N']);
            }

            $transaction = Transaction::create([
                'group_id' =>   $this->member->group_id,
                'operator_id' => $this->operator,
                'member_id' => $this->member->id,
                'first_depo' => $firstDepo,
                'bank_id' => $this->bank_id,
                'total' => $this->total,
                'fee' => $this->b_trf,
                'bonus' => $this->bonus,
                'note' => $this->note ?? null,
                'type' => $this->type,
                'deposit' => $this->type === 'deposit' ? $this->amount : 0,
                'withdraw' => $this->type === 'withdraw' ? $this->amount : 0,
            ]);

            $bank = Bank::where('id', $this->bank_id)->lockForUpdate()->first();
            $group = Group::where('id', $this->member->group_id)->lockForUpdate()->first();

            if ($this->type === 'deposit') {
                $bank->increment('saldo', $this->total);
                $group->decrement('koin', $this->total);
                $group->increment('saldo', $this->total);

                $koin = -$this->total;

            } elseif ($this->type === 'withdraw') {
                $bank->decrement('saldo', $this->total);
                $group->increment('koin', $this->total);
                $group->decrement('saldo', $this->total);
                
                $koin = $this->total;
            }

            $history = Koinhistory::create([
                'group_id' => $this->member->group_id,
                'keterangan' => $this->type,
                'member_id' => $this->member->id,
                'koin' => $koin,
                'saldo' => $group->saldo,
                'operator_id' => $this->operator,
                
            ]);

            $log = Logtransaksi::create([
                
                'operator_id' =>  $transaction->operator_id,
                'bank_id' =>  $transaction->bank_id,
                'type_transaksi' => 'TR',
                'type' =>  $transaction->type,
                'rekenin_name' => $bank->label,
                'deposit' => $transaction->type === 'deposit' ? $transaction->total : 0,
                'withdraw' => $transaction->type === 'withdraw' ? $transaction->total : 0,
                'saldo' => $bank->saldo,
                'note' =>  $this->member->name,
            ]);
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
                    Forms\Components\Select::make('bank_id')
                        ->label('Rekening Depo')
                        ->options(Bank::pluck('label', 'id')) 
                        ->required(),
                    Forms\Components\TextInput::make('amount')
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('b_trf')
                        ->numeric()
                        ->label('Biaya Transfer'),
                    Forms\Components\TextInput::make('bonus')
                        ->numeric(),
                    Forms\Components\Textarea::make('note')


                ]),
        ];
    }



    
}
