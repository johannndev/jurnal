<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankBlacklistResource\Pages;
use App\Filament\Resources\BankBlacklistResource\RelationManagers;
use App\Models\BankBlacklist;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BankBlacklistResource extends Resource
{
    protected static ?string $model = BankBlacklist::class;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';

    protected static ?string $navigationLabel = 'Bank Blacklist';

    protected static ?string $pluralModelLabel = 'Bank Blacklist';

    protected static ?string $modelLabel = 'Bank Blacklist';

    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_rekening')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('bankname_id')
                    ->relationship('bankname', 'bank_nama')
                    ->label('Bank')
                    ->createOptionForm([
                        Forms\Components\TextInput::make('bank_nama')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->required(),
                Forms\Components\TextInput::make('nomor_rekening')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('keterangan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_rekening')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bankname.bank_nama')
                    ->label('Bank')
                    ->sortable(),
                Tables\Columns\TextColumn::make('nomor_rekening')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListBankBlacklists::route('/'),
            'create' => Pages\CreateBankBlacklist::route('/create'),
            'edit' => Pages\EditBankBlacklist::route('/{record}/edit'),
        ];
    }
}
