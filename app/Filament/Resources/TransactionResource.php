<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use App\Models\Group;
use App\Models\Member;
use App\Models\Bank;
use App\Models\Koinhistory;
use App\Models\Logtransaksi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Transaksi';

    protected static ?string $modelLabel = 'Transaksi';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('member_id')
                    ->relationship('member', 'username', fn ($query) => $query->where('group_id', Group::getActiveGroupId()))
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('bank_id')
                    ->relationship('bank', 'label', fn ($query) => $query->where('group_id', Group::getActiveGroupId()))
                    ->required(),
                Forms\Components\Select::make('type')
                    ->options([
                        'deposit' => 'Deposit',
                        'withdraw' => 'Withdraw',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('deposit')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('withdraw')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('fee')
                    ->numeric()
                    ->default(0)
                    ->label('Biaya Transfer'),
                Forms\Components\TextInput::make('bonus')
                    ->numeric()
                    ->default(0),
                Forms\Components\Textarea::make('note')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('member.username')
                    ->label('Member')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'deposit' => 'success',
                        'withdraw' => 'danger',
                        'bonus' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('deposit')
                    ->label('Deposit')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('withdraw')
                    ->label('Withdraw')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bank.label')
                    ->label('Rekening'),
                Tables\Columns\TextColumn::make('operator.name')
                    ->label('Operator'),
            ])
            ->filters([
                SelectFilter::make('member_id')
                    ->label('Cari Member')
                    ->searchable()
                    ->options(fn () => Member::where('group_id', Group::getActiveGroupId())->pluck('username', 'id'))
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['value'])) {
                            $query->where('member_id', $data['value']);
                        } else {
                            $query->whereRaw('1 = 0');
                        }
                    }),
            ], layout: Tables\Enums\FiltersLayout::AboveContent)
            ->headerActions([
                Action::make('deposit')
                    ->label('Deposit')
                    ->color('success')
                    ->icon('heroicon-o-arrow-down-circle')
                    ->visible(fn ($livewire) => !empty($livewire->tableFilters['member_id']['value']))
                    ->form(fn () => static::getTransactionForm('deposit'))
                    ->action(function (array $data, $livewire) {
                        $memberId = $livewire->tableFilters['member_id']['value'];
                        $member = Member::find($memberId);
                        static::executeTransaction($member, 'deposit', $data);
                    }),

                Action::make('withdraw')
                    ->label('Withdraw')
                    ->color('danger')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->visible(fn ($livewire) => !empty($livewire->tableFilters['member_id']['value']))
                    ->form(fn () => static::getTransactionForm('withdraw'))
                    ->action(function (array $data, $livewire) {
                        $memberId = $livewire->tableFilters['member_id']['value'];
                        $member = Member::find($memberId);
                        static::executeTransaction($member, 'withdraw', $data);
                    }),

                Action::make('bonus')
                    ->label('Bonus Koin')
                    ->color('warning')
                    ->icon('heroicon-o-gift')
                    ->visible(fn ($livewire) => !empty($livewire->tableFilters['member_id']['value']))
                    ->form([
                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah Bonus')
                            ->numeric()
                            ->required(),
                        Forms\Components\Textarea::make('note')
                            ->label('Keterangan'),
                    ])
                    ->action(function (array $data, $livewire) {
                        $memberId = $livewire->tableFilters['member_id']['value'];
                        $member = Member::find($memberId);
                        static::executeBonusKoin($member, $data);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Silakan cari member terlebih dahulu')
            ->emptyStateDescription('Input ID atau Username member pada filter di atas untuk menampilkan data transaksi.')
            ->emptyStateIcon('heroicon-o-magnifying-glass');
    }

    protected static function getTransactionForm(string $type): array
    {
        return [
            Forms\Components\Grid::make(2)
                ->schema([
                    Forms\Components\Select::make('bank_id')
                        ->label($type === 'deposit' ? 'Rekening Depo' : 'Rekening WD')
                        ->options(function () {
                            $groupId = Group::getActiveGroupId();
                            return Bank::where('group_id', $groupId)->pluck('label', 'id');
                        })
                        ->required(),
                    Forms\Components\TextInput::make('amount')
                        ->label('Nominal')
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('fee')
                        ->label('Biaya Transfer')
                        ->numeric()
                        ->default(0),
                    Forms\Components\TextInput::make('bonus')
                        ->label('Bonus')
                        ->numeric()
                        ->default(0)
                        ->visible($type === 'deposit'),
                    Forms\Components\Textarea::make('note')
                        ->label('Keterangan')
                        ->columnSpanFull(),
                ]),
        ];
    }

    protected static function executeTransaction(Member $member, string $type, array $data): void
    {
        $amount = (float) $data['amount'];
        $fee = (float) ($data['fee'] ?? 0);
        $bonus = (float) ($data['bonus'] ?? 0);
        $total = $amount - $fee + $bonus;

        DB::transaction(function () use ($member, $type, $data, $amount, $fee, $bonus, $total) {
            $firstDepo = 'N';
            if ($member->first_depo == 'Y' && $type === 'deposit') {
                $firstDepo = 'Y';
                $member->update(['first_depo' => 'N']);
            }

            $transaction = Transaction::create([
                'group_id' => $member->group_id,
                'operator_id' => Auth::id(),
                'member_id' => $member->id,
                'first_depo' => $firstDepo,
                'bank_id' => $data['bank_id'],
                'total' => $total,
                'fee' => $fee,
                'bonus' => $bonus,
                'note' => $data['note'] ?? null,
                'type' => $type,
                'deposit' => $type === 'deposit' ? $amount : 0,
                'withdraw' => $type === 'withdraw' ? $amount : 0,
            ]);

            $bank = Bank::where('id', $data['bank_id'])->lockForUpdate()->first();
            $group = Group::where('id', $member->group_id)->lockForUpdate()->first();

            if ($type === 'deposit') {
                $bank->increment('saldo', $total);
                $group->decrement('koin', $total);
                $group->increment('saldo', $total);
                $koinValue = -$total;
            } else {
                $bank->decrement('saldo', $total);
                $group->increment('koin', $total);
                $group->decrement('saldo', $total);
                $koinValue = $total;
            }

            Koinhistory::create([
                'group_id' => $member->group_id,
                'keterangan' => $type,
                'member_id' => $member->id,
                'koin' => $koinValue,
                'saldo' => $group->saldo,
                'operator_id' => Auth::id(),
            ]);

            Logtransaksi::create([
                'operator_id' => Auth::id(),
                'bank_id' => $data['bank_id'],
                'type_transaksi' => 'TR',
                'type' => $type,
                'rekenin_name' => $bank->label,
                'deposit' => $type === 'deposit' ? $total : 0,
                'withdraw' => $type === 'withdraw' ? $total : 0,
                'saldo' => $bank->saldo,
                'note' => $member->name,
            ]);
        });

        Notification::make()
            ->title('Transaksi berhasil ditambahkan!')
            ->success()
            ->send();
    }

    protected static function executeBonusKoin(Member $member, array $data): void
    {
        $amount = (float) $data['amount'];

        DB::transaction(function () use ($member, $data, $amount) {
            $group = Group::where('id', $member->group_id)->lockForUpdate()->first();
            $group->decrement('koin', $amount);

            Koinhistory::create([
                'group_id' => $member->group_id,
                'keterangan' => 'bonus',
                'member_id' => $member->id,
                'koin' => -$amount,
                'saldo' => $group->saldo,
                'operator_id' => Auth::id(),
            ]);

            Transaction::create([
                'group_id' => $member->group_id,
                'operator_id' => Auth::id(),
                'member_id' => $member->id,
                'type' => 'bonus',
                'bonus' => $amount,
                'total' => $amount,
                'deposit' => 0,
                'withdraw' => 0,
                'bank_id' => 0, // ID dummy atau buat kolom bank_id nullable
                'note' => $data['note'] ?? 'Bonus Koin',
            ]);
        });

        Notification::make()
            ->title('Bonus koin berhasil ditambahkan!')
            ->success()
            ->send();
    }

    public static function getEloquentQuery(): Builder
    {
        $groupId = Group::getActiveGroupId();
        
        return parent::getEloquentQuery()
            ->where('transactions.group_id', $groupId);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
