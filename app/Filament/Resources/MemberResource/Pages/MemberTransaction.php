<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Filament\Resources\MemberResource\Widgets\GroupStat;
use App\Filament\Widgets\RealtimeClock;
use App\Models\Bank;
use App\Models\Group;
use App\Models\Koinhistory as KH;
use App\Models\Logtransaksi ;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Actions\Action as TabelAction;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Models\Transaction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MemberTransaction extends Page implements Tables\Contracts\HasTable
{
    use InteractsWithTable;

    protected static string $resource = MemberResource::class;
    protected static string $view = 'filament.resources.member-resource.pages.member-transaction';

    public ?int $userId = null;
    public float $totalDeposit = 0;
    public float $totalWithdraw = 0;
    public float $balance = 0;

    public function mount(int $record): void
    {
        $this->userId = $record;
        $this->calculateBalance();
    }

 

    protected function getTableQuery()
    {
        return Transaction::query()->where('member_id', $this->userId)->orderBy('created_at', 'desc');
    }

    protected function calculateBalance(): void
    {
        $result = Transaction::where('member_id', $this->userId)
            ->selectRaw("
                SUM(CASE WHEN type = 'deposit' THEN total ELSE 0 END) as total_deposit,
                SUM(CASE WHEN type = 'withdraw' THEN total ELSE 0 END) as total_withdraw
            ")
            ->first();

        $this->totalDeposit = $result->total_deposit ?? 0;
        $this->totalWithdraw = $result->total_withdraw ?? 0;
        $this->balance = $this->totalDeposit - $this->totalWithdraw;
    }


    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_deposit')
                ->label('Deposit')
                ->url(fn () => route('filament.admin.resources.members.create-transaction', ['record' => $this->userId,'type'=>'deposit']))
                ->icon('heroicon-o-plus'),

            Action::make('add_withdraw')
                ->label('Withdraw')
                ->url(fn () => route('filament.admin.resources.members.create-transaction', ['record' => $this->userId,'type'=>'withdraw']))
                ->icon('heroicon-o-plus'),

            Action::make('add_bonus')
                ->label('Bonus Koin')
                ->url(fn () => route('filament.admin.resources.members.create-bonus', ['record' => $this->userId]))
                ->icon('heroicon-o-plus'),
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            
            TextColumn::make('created_at')->label('Tanggal')->searchable(),
            TextColumn::make('operator.name')->label('Operator'),
            TextColumn::make('member.username')->label('username'),
            TextColumn::make('bank.label')->label('Rek. Depo'),
            TextColumn::make('deposit')->label('Deposit')->numeric(locale: 'id'),
            TextColumn::make('withdraw')->label('Withdraw')->numeric(locale: 'id'),
            TextColumn::make('note')->label('Note'),
            TextColumn::make('fee')->label('B.trf')->numeric(locale: 'id'),
            TextColumn::make('bonus')->label('Bonus')->numeric(locale: 'id'),
            TextColumn::make('total')->label('Total')->numeric(locale: 'id'),
            
        ];
    }

   protected function getTableActions(): array
    {
        return [
            TabelAction::make('delete')
                ->label('Hapus')
                ->icon('heroicon-s-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi delete data')
                ->modalDescription('Yakin ingin menghapus data ini?')
                ->form([
                    TextInput::make('password')
                        ->password()
                        ->required()
                        ->label('Password')
                        ->placeholder('Masukkan password Anda'),
                ])
                ->action(function ($record, array $data) {

                     // cek password user yang sedang login
                    $user = auth()->user();

                    if (! $user || ! isset($data['password']) || ! Hash::check($data['password'], $user->password)) {
                        Notification::make()
                            ->title('Password salah')
                            ->danger()
                            ->body('Password yang Anda masukkan tidak cocok. Penghapusan dibatalkan.')
                            ->send();

                        return;
                    }

                    DB::transaction(function () use($record) {

                        $bank = Bank::where('id', $record->bank_id)->lockForUpdate()->first();
                        $group = Group::where('id', $record->group_id)->lockForUpdate()->first();

                        if ($record->type === 'deposit') {
                            $bank->decrement('saldo',$record->total);
                            $group->increment('koin',$record->total);
                            $group->decrement('saldo',$record->total);

                            $returnType = 'withdraw';

                            $koin = $record->total;

                        } elseif ($record->type === 'withdraw') {
                            $bank->increment('saldo',$record->total);
                            $group->decrement('koin',$record->total);
                            $group->increment('saldo',$record->total);

                            $returnType = 'deposit';
                            
                            $koin = -$record->total;
                        }

                        $history = KH::create([
                            'group_id' =>  $record->group_id,
                            'keterangan' => 'delete transaksi '.$record->type,
                            'member_id' => $record->member_id,
                            'koin' => $koin,
                            'saldo' => $group->saldo,
                            'operator_id' => $record->operator_id,
                            
                        ]);

                    

                        $log = Logtransaksi::create([
                            'operator_id' =>  Auth::id(),
                            'bank_id' =>  $record->bank_id,
                            'type_transaksi' => 'TR',
                            'type' =>   $returnType,
                            'rekenin_name' => $bank->label,
                            'deposit' =>  $returnType === 'deposit' ? $record->total : 0,
                            'withdraw' =>  $returnType === 'withdraw' ? $record->total : 0,
                            'saldo' => $bank->saldo,
                            'note' => 'delete transaksi '.$record->type.' '.$record->member->name,
                        ]);

                        $record->delete();
                    });
                }),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            Tables\Actions\DeleteBulkAction::make(),
        ];
    }

     public function getHeaderWidgetsColumns(): int | array
    {
        return 3;
    }

    protected function getHeaderWidgets(): array
    {
        $groupId = request()->get('group_id');

        $gd = Group::where('is_default',1)->first();
        $dft = 1;
        if($gd){
            $dft = $gd->id;
        }

        return [
            RealtimeClock::class,
            MemberResource\Widgets\GroupStat::make([
                'groupId' =>  $groupId,
                'groupDefault' =>  $dft,
            ])
        ];
    }


    
}
