<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BatchBonusResource\Pages;
use App\Filament\Resources\BatchBonusResource\RelationManagers;
use App\Models\BonusBatch;
use App\Models\Group;
use App\Models\Member;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class BatchBonusResource extends Resource
{
    protected static ?string $model = BonusBatch::class;
    

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Batch Bonus Koin';

    protected static ?string $navigationGroup = 'More';

    public static function getNavigationSort(): ?int
    {
        return 10;
    }

      // Tambahkan properti ini
    public $countExists = 0;
    public $countNotExists = 0;
    public $show = false;


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('selectedGroup')
                    ->label('Pilih Group')
                    ->options(function () {
                        return Group::pluck('name', 'id');
                    })
                    ->default(function () {
                        $user = Auth::user();
                        return $user->group_id; // default ke group user jika ada
                    })
                    ->disabled(function () {
                        $user = Auth::user();
                        return $user->group_id !== null; // disable kalau user punya group
                    })
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set, $livewire) {
                        $livewire->selectedGroup = $state; // simpan ke property Livewire
                    }),
                Forms\Components\FileUpload::make('attachment')
                    ->label('file upload')
                    ->reactive()
                    ->columnSpan(2)
                    ->disk('local')
                    ->directory('file')
                    ->afterStateUpdated(function ($state, $set, $livewire, $get) {

                        $groupId = $get('selectedGroup');
                                        // Baca file CSV/TXT
                        $assoc = collect(file($state->getRealPath(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES))
                            ->mapWithKeys(function ($line) {
                                [$username, $nominal] = explode(',', trim($line)) + [null, 0];
                                return [trim($username) => floatval($nominal)];
                            })
                            ->toArray();

                        // 2. Ambil member valid dari database: username => id
                        $members = Member::whereIn('username', array_keys($assoc))
                            ->where('group_id', $groupId)
                            ->pluck('id','username') // username => id
                            ->toArray();

                        // 3. Gabungkan hanya yang key sama (username ada di database)
                        $dataMember = [];
                        $existsCount = 0;
                        $notExistsCount = 0;

                        foreach ($assoc as $username => $nominal) {
                            if (isset($members[$username])) {
                                $dataMember[$username] = [
                                    'id' => $members[$username],  // ambil id dari database
                                    'nominal' => $nominal,        // ambil nominal dari file
                                ];
                                $existsCount++;
                            } else {
                                $notExistsCount++;
                            }
                        }

                        // Update state Livewire dan form
                        $set('countExists', $existsCount);
                        $set('countNotExists', $notExistsCount);
                        $set('show', true);

                        $livewire->countExists = $existsCount;
                        $livewire->countNotExists = $notExistsCount;
                        $livewire->show = true;
                        $livewire->selectedGroup = $groupId;

                        // Simpan array username + nominal yang valid
                        $livewire->dataArray = $dataMember; 
                    }),
                ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\CreateBatchBonus::route('/'),
            'create' => Pages\CreateBatchBonus::route('/create'),
            'edit' => Pages\EditBatchBonus::route('/{record}/edit'),
        ];
    }
}
