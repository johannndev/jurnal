<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BonusResource\Pages;
use App\Filament\Resources\BonusResource\RelationManagers;
use App\Models\Bonus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BonusResource extends Resource
{
    protected static ?string $model = Bonus::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Bonus Koin';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('group_id')
                    ->relationship('group', 'name')
                    ->required()
                    ->default(function () {
                        // Ambil group_id dari user login
                        return auth()->user()->group_id;
                    })
                    ->disabled(fn () => auth()->user()->group_id !== null),
                Forms\Components\Select::make('member_id')
                    ->label('Member')
                    ->required()
                    ->options(function (callable $get) {
                        // Ambil group_id dari form (atau user login)
                        $groupId = auth()->user()->group_id ?? $get('group_id');
                        return \App\Models\Member::where('group_id', $groupId)
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('nominal')
                    ->label('Nominal')
                    ->numeric()
                    ->extraAttributes([
                        'id' => 'nominal', // Tambahkan id untuk akses di JS
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('member.name')
                    ->label('Username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('group.name')
                    ->label('Group'),
                Tables\Columns\TextColumn::make('nominal')->label('Nominal'),
                 Tables\Columns\TextColumn::make('operator.name')
                    ->label('Operator'),
            ])
            ->filters([
                //
            ])
            ->actions([
                
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBonuses::route('/'),
            'create' => Pages\CreateBonus::route('/create'),
           
        ];
    }
}
