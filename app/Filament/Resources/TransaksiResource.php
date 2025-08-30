<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransaksiResource\Pages;
use App\Filament\Resources\TransaksiResource\RelationManagers;
use App\Filament\Resources\TransaksiResource\Widgets\TransactionSummaryWidget;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class TransaksiResource extends Resource
{
    protected static ?string $model = Transaction::class;

    public static function getNavigationLabel(): string
    {
        return 'Dashboard';
    }

    protected static ?string $modelLabel = 'Dashboard';

    public static function getSlug(): string
    {
        return '/'; // ini akan mengubah URL-nya
    }

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Waktu')->dateTime('d/m/Y H:i:s'),
                TextColumn::make('operator.name')->label('Operator'),
                TextColumn::make('group.name')->label('Sitename'),
                TextColumn::make('member.username')->label('Username'),
                TextColumn::make('deposit')->label('Deposit'),
                TextColumn::make('withdraw')->label('Withdraw'),
                TextColumn::make('fee')->label('Biaya Trf'),
                TextColumn::make('note')->label('Keterangan'),
                TextColumn::make('rekening_deposit')
                    ->label('Rekening Deposit')
                    ->getStateUsing(fn ($record) => $record->type === 'deposit' ? ($record->bank->label ?? '-') : '-'),

                // Kolom untuk Rekening Withdraw
                TextColumn::make('rekening_withdraw')
                    ->label('Rekening Withdraw')
                    ->getStateUsing(fn ($record) => $record->type === 'withdraw' ? ($record->bank->label ?? '-') : '-'),
            ])
            ->filters([
               // Filter Tanggal
            Filter::make('Tanggal')
                ->form([
                    DatePicker::make('start_date')->label('Dari'),
                    DatePicker::make('end_date')->label('Sampai'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['start_date'], fn ($q) => $q->whereDate('created_at', '>=', $data['start_date']))
                        ->when($data['end_date'], fn ($q) => $q->whereDate('created_at', '<=', $data['end_date']));
                }),

            // Filter Type: All, Deposit Only, Withdraw Only
            Filter::make('type')
                ->label('Tipe Transaksi')
                ->form([
                    Select::make('type')
                        ->options([
                            'all' => 'All',
                            'deposit' => 'Deposit Only',
                            'withdraw' => 'Withdraw Only',
                        ])
                        ->default('all'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['type'] === 'deposit', fn ($q) => $q->whereNotNull('deposit')->where('deposit', '>', 0))
                        ->when($data['type'] === 'withdraw', fn ($q) => $q->whereNotNull('withdraw')->where('withdraw', '>', 0));
                }),

            // Filter Operator
            Filter::make('operator_id')
                ->form([
                    Select::make('operator_id')
                        ->label('Operator')
                        ->searchable()
                        ->options(fn () => DB::table('users')->pluck('name', 'id')->toArray()),
                ])
                ->query(fn ($query, array $data) => $query->when($data['operator_id'], fn ($q) => $q->where('operator_id', $data['operator_id']))),

            // Filter Group
            Filter::make('group_id')
                ->form([
                    Select::make('group_id')
                        ->label('Group')
                        ->searchable()
                        ->options(fn () => DB::table('groups')->pluck('name', 'id')->toArray()),
                ])
                ->query(fn ($query, array $data) => $query->when($data['group_id'], fn ($q) => $q->where('group_id', $data['group_id']))),

            // Filter Member
            Filter::make('member_id')
                ->form([
                    Select::make('member_id')
                        ->label('Member')
                        ->searchable()
                        ->options(fn () => DB::table('members')->pluck('username', 'id')->toArray()),
                ])
                ->query(fn ($query, array $data) => $query->when($data['member_id'], fn ($q) => $q->where('member_id', $data['member_id']))),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getWidgets(): array
    {
        return [
            TransactionSummaryWidget::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransaksis::route('/'),
            // 'create' => Pages\CreateTransaksi::route('/create'),
            // 'edit' => Pages\EditTransaksi::route('/{record}/edit'),
        ];
    }
}
