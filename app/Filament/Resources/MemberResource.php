<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MemberResource\Pages;
use App\Filament\Resources\MemberResource\Pages\MemberTransaction;
use App\Filament\Resources\MemberResource\RelationManagers;
use App\Models\Member;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MemberResource extends Resource
{
    protected static ?string $model = Member::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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

        return parent::getEloquentQuery()->when($user->group_id > 0, function ($query) use ($user) {
            return $query->where('group_id', $user->group_id);
        });
    }


    public static function table(Table $table): Table
    {
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
                //
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
            'koin-history' => Pages\KoinHistory::route('/transactions/koin-history'),  
        ];
    }

    public static function getWidgets(): array
    {
        return [
            MemberResource\Widgets\GroupStat::class,
        ];
    }

   
}
