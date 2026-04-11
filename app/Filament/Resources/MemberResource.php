<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Filament\Resources\MemberResource\Pages\MemberTransaction;
use App\Models\Group;
use App\Models\Member;
use App\Models\BankBlacklist;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Notifications\Notification;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 3;

    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(1)
                    ->schema([
                        Forms\Components\TextInput::make('username')
                            ->required(),
                        Forms\Components\Select::make('group_id')
                            ->label('Site Name')
                            ->options(fn () => Group::where('id', Group::getActiveGroupId())->pluck('name', 'id'))
                            ->default(fn () => Group::getActiveGroupId())
                            ->required()
                            ->dehydrated(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Member')
                            ->required(),
                        Forms\Components\TextInput::make('phone')
                            ->label('No. Handphone')
                            ->required(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->suffix('@gmail.com')
                            ->dehydrateStateUsing(fn ($state) => !empty($state) && !str_contains($state, '@') ? $state . '@gmail.com' : $state),
                        Forms\Components\Select::make('bank_name')
                            ->label('Nama Bank')
                            ->options(\App\Models\Bankname::pluck('bank_nama', 'bank_nama')->toArray())
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('bank_nama')
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->createOptionUsing(function (array $data) {
                                return \App\Models\Bankname::create($data)->bank_nama;
                            })
                            ->required(),
                        Forms\Components\TextInput::make('bank_number')
                            ->label('No Rekening')
                            ->required(),
                        Forms\Components\Select::make('rek_depo')
                            ->label('Rek Depo')
                            ->options(function (Forms\Get $get) {
                                $groupId = $get('group_id');
                                if (!$groupId) {
                                    return [];
                                }
                                return \App\Models\Bank::with('bankname')
                                    ->where('group_id', $groupId)
                                    ->get()
                                    ->mapWithKeys(function ($bank) {
                                        $label = "{$bank->bankname?->bank_nama} / {$bank->bank_number} / {$bank->bank_account_name}";
                                        return [$label => $label];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->required(),
                        Forms\Components\Placeholder::make('blacklist_modal_trigger')
                            ->hiddenLabel()
                            ->content(fn () => view('filament.resources.member.blacklist-modal'))
                            ->columnSpanFull(),
                    ]),
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->suffix(fn ($state) => $state && !str_contains($state, '@') ? '@gmail.com' : ''),
                Tables\Columns\TextColumn::make('bank_name'),
                Tables\Columns\TextColumn::make('bank_number'),
                Tables\Columns\TextColumn::make('group.name')->label('Group'),
                
            ])
            ->defaultSort('created_at', 'desc') 
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
                            return redirect('/admin/members' . ($state ? '?group_id=' . $state : ''));
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
                Action::make('transactions')
                ->label('Transaction')
                ->url(fn ($record) => route('filament.admin.resources.members.member-transaksi', ['record' => $record->id]))
                ->color('primary'),
                
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
            'index' => Pages\ListMembers::route('/'),
            'create' => Pages\CreateMember::route('/create'),
            'edit' => Pages\EditMember::route('/{record}/edit'),
            'member-transaksi' => MemberTransaction::route('/{record}/transactions'),
            'create-transaction' => Pages\MemberTransactionCreate::route('/{record}/transactions/create'),
            'create-bonus' => Pages\MemberAddBonus::route('/{record}/transactions/add-bonus'),
            'koin-history' => Pages\KoinHistory::route('/transactions/koin-history'),  
        ];
    }

    public static function getWidgets(): array
    {
        $groupId = Group::getActiveGroupId();

        return [
            MemberResource\Widgets\GroupStat::class,
        ];
    }

   
}
