<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Group;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'User';

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $slug = 'user';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create'),
                Forms\Components\Select::make('roles')
                    ->relationship('roles', 'name')
                    ->preload()
                    ->multiple(),
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('group.name')->label('Group'),
            ])
            ->filters([
                Filter::make('by_group')
                ->form([
                    Select::make('group_id')
                        ->label('Group')
                        ->options(Group::pluck('name', 'id')->toArray())
                        ->default($dft)
                        ->live()
                        ->afterStateUpdated(function ($state) {
                            return redirect('/admin/user' . ($state ? '?group_id=' . $state : ''));
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
