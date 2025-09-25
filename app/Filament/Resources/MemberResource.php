<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Filament\Resources\MemberResource\Pages\MemberTransaction;
use App\Filament\Resources\MemberResource\RelationManagers;
use App\Models\Group;
use App\Models\Member;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 3;

    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('username')
                    ->required(),
                Forms\Components\TextInput::make('phone')
                    ->required(),
                Forms\Components\TextInput::make('bank_name')
                    ->required(),
                Forms\Components\TextInput::make('bank_number')
                    ->required(),
                Forms\Components\Select::make('group_id')
                    ->relationship('group', 'name')
                    ->hidden(fn () => auth()->user()->group_id > 0)
                    ->required(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();


        $gd = Group::where('is_default',1)->first();
        $dft = 1;
        if($gd){
            $dft = $gd->id;
        }

        $groupId = request('group_id') ?? $dft; // default ke 1 jika tidak ada query string
        return static::getModel()::query()
            ->when($groupId, fn ($query) => $query->where('group_id', $groupId))
            ->orderBy('created_at', 'desc');
    }


    public static function table(Table $table): Table
    {
        $gd = Group::where('is_default', 1)->first();
        $dft = $gd ? $gd->id : 1;

        return $table
        
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
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
                        ->default(fn () => request()->get('group_id', $dft)) // tetap bisa isi awal jika sudah terset
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
        $groupId = request()->get('group_id', 1);

        return [
            MemberResource\Widgets\GroupStat::class,
        ];
    }

   
}
