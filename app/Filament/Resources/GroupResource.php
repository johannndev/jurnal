<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GroupResource\Pages;
use App\Filament\Resources\GroupResource\RelationManagers;
use App\Models\Group;
use App\Models\Koinhistory;
use Filament\Actions\Action;
use Filament\Tables\Actions\Action as ActionTable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupResource extends Resource 
{
    protected static ?string $model = Group::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Checkbox::make('is_default')
                    ->label('Default')
                    ->inline(false),
                // Forms\Components\TextInput::make('koin')
                //     ->default(0)
                //     ->required(),
                // Forms\Components\TextInput::make('saldo')
                //     ->default(0)
                //     ->required(),
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('koin')->numeric(locale: 'id'),
                Tables\Columns\TextColumn::make('saldo')->numeric(locale: 'id'),
                Tables\Columns\IconColumn::make('is_default')
                    ->boolean() // Otomatis tampilkan ceklis dan silang
                    ->trueIcon('heroicon-o-check-circle') // default sudah check
                    ->falseIcon('') // kosong jika 0
                    ->trueColor('success'),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionTable::make('update_koin')
                    ->label('Update Koin')
                    ->modalHeading('Update Koin')
                    ->icon('heroicon-s-plus')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('nominal')
                            ->label('Nominal')
                            ->numeric(),
                        
                    ])
                    ->action(function (array $data, $record) {

                         $history = Koinhistory::create([
                            'group_id' => $record->id,
                            'keterangan' => 'Tambah Koin',
                            'member_id' => 0,
                            'koin' => $data['nominal'],
                            'saldo' => $record->saldo,
                            'operator_id' => Auth::id(),
                            
                        ]);

                        $record->increment('koin',$data['nominal']);

                       
                    
                        Notification::make()
                            ->title('Dana gantung berhasil diproses!')
                            ->success()
                            ->send();

                    })
 
                    ->visible(fn ($record) =>
                        auth()->user()->hasPermissionTo('view_group')
                    ),
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
            'index' => Pages\ListGroups::route('/'),
            'create' => Pages\CreateGroup::route('/create'),
            'edit' => Pages\EditGroup::route('/{record}/edit'),
        ];
    }
}
