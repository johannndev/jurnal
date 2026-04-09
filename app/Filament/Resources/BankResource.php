<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankResource\Pages;
use App\Filament\Resources\BankResource\Pages\CreateAdjustment;
use App\Filament\Resources\BankResource\Pages\CreateInOut;
use App\Filament\Resources\BankResource\Pages\CreatePindahDana;
use App\Filament\Resources\BankResource\Pages\HistoryLogBank;
use App\Filament\Resources\BankResource\Pages\LogAdjustment;
use App\Filament\Resources\BankResource\Pages\LogExpanses;
use App\Filament\Resources\BankResource\Pages\LogIncome;
use App\Filament\Resources\BankResource\Pages\LogPindahDana;
use App\Filament\Resources\BankResource\RelationManagers;
use App\Filament\Resources\BankResource\Widgets\BankSummaryWidget;
use App\Filament\Resources\BankResource\Widgets\BankTotalBalance;
use App\Filament\Resources\BankResource\Widgets\JamWidget;
use App\Filament\Resources\BankResource\Widgets\SaldoWidget;
use App\Filament\Widgets\RealtimeClock;
use App\Models\Bank;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Group;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BankResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Bank::class;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form

    {
        return $form
            ->schema([
                Forms\Components\Select::make('bankname_id')
                    ->relationship('bankname', 'bank_nama')
                    ->label('Bank')
                    ->createOptionForm([
                        Forms\Components\TextInput::make('bank_nama')
                            ->required()
                            ->maxLength(255),

                    ])
                    ->required(),
                Forms\Components\TextInput::make('bank_account_name')
                    ->required(),
                Forms\Components\TextInput::make('bank_number')
                    ->required(),
                Forms\Components\Select::make('group_id')
                    ->relationship('group', 'name', fn ($query) => $query->where('id', Group::getActiveGroupId()))
                    ->default(fn () => Group::getActiveGroupId())
                    ->required()
                    ->dehydrated(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $groupId = Group::getActiveGroupId();

        return static::getModel()::query()
            ->when($groupId, fn ($query) => $query->where('group_id', $groupId))
            ->orderBy('created_at', 'desc');
    }

    public static function table(Table $table): Table
    {
        $dft = Group::getActiveGroupId();

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label'),
                Tables\Columns\TextColumn::make('bankname.bank_nama')
                    ->label('Bank')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bank_account_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bank_number'),
                Tables\Columns\TextColumn::make('saldo')->numeric(locale: 'id'),
                Tables\Columns\TextColumn::make('group_id')->label('Group ID'),
            ])
            ->filters([
                Filter::make('by_group') // nama dummy filter
                ->form([
                    Select::make('group_id') // nama field yang digunakan di dalam form filter
                        ->label('Group')
                        ->options(Group::pluck('name', 'id')->toArray())
                        ->default($dft)
                        ->live()
                        ->afterStateUpdated(function ($state) {
                            // redirect ke URL bersih
                            return redirect('/admin/banks' . ($state ? '?group_id=' . $state : ''));
                        }),
                ])
                ->indicateUsing(function (array $data): ?string {
                    if ($data['group_id'] ?? false) {
                        $group = Group::find($data['group_id']);
                        return 'Group: ' . ($group?->name ?? 'Unknown');
                    }
                    return null;
                })
                ->visible(fn () => auth()->user()->group_id == 0),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),
                ActionGroup::make([
                    Action::make('history_rekening')
                        ->url(fn ($record) => BankResource::getUrl('history', ['record' => $record]))
                        ->label('History Rekening'),
                    Action::make('pindah_dana_log')
                        ->label('Input Pindah Dana')
                        ->url(fn ($record) => BankResource::getUrl('createPindahDana', ['record' => $record])),
                    Action::make('ei_input')
                        ->label('Input Expanse/Income')
                        ->url(fn ($record) => BankResource::getUrl('createInOut', ['record' => $record])),
   
                    Action::make('adjust_input')
                        ->label('Input Adjustment')
                        ->url(fn ($record) => BankResource::getUrl('createAdjustment', ['record' => $record])),
                       
                    
                        
                          
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanks::route('/'),
            'create' => Pages\CreateBank::route('/create'),
            'edit' => Pages\EditBank::route('/{record}/edit'),
            'history' => HistoryLogBank::route('/{record}/history'),
            'createPindahDana' => CreatePindahDana::route('/{record}/pindah-dana/create'),
            'createInOut' => CreateInOut::route('/{record}/expanse-income/create'),
            'createAdjustment' => CreateAdjustment::route('/{record}/adjustment/create'),
            'logPindahDana' => LogPindahDana::route('/pindah-dana/log'),
            'logExpanses' => LogExpanses::route('/expanses/log'),
            'logIncome' => LogIncome::route('/income/log'),
            'logAdjustment' => LogAdjustment::route('/adjustment/log'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
            'history',
            'log_pindah_dana',
            'create_pindah_dana',
            'log_expanse',
            'log_income',
            'create_expanse_income',
            'log_adjutment',
            'create_adjustment',
            
        ];
    }
    
    public function getHeaderWidgetsColumns(): int | array
    {
        return 3;
    }

    public static function getWidgets(): array
    {
        return [
            BankTotalBalance::class,
            BankSummaryWidget::class,
          
        ];
    }
}
