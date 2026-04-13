<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PendingDepoResource\Pages;
use App\Filament\Resources\PendingDepoResource\RelationManagers;
use App\Filament\Resources\PendingDepoResource\Widgets\PendingDepoOverview;
use App\Models\Bank;
use App\Models\Group;
use App\Models\Koinhistory;
use App\Models\Logtransaksi;
use App\Models\Member;
use App\Models\Pendingdepo;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class PendingdepoResource extends Resource
{
    protected static ?string $model = Pendingdepo::class;

     protected static ?string $modelLabel = 'Pending Deposit';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationLabel = 'Pending Deposit';

    protected static ?string $navigationGroup = 'Pending Transaction';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Grid::make()
                    ->columns(1) // Sesuaikan jumlah kolom agar cukup
                    ->schema([
                        Forms\Components\TextInput::make('nama_rek')
                            ->label('Nama Rekening')
                            ->required(),

                       Forms\Components\Select::make('bank_id')
                            ->label('Rekening Depo')
                            ->required()
                            ->options(function () {
                                $groupId = Group::getActiveGroupId();
                                return \App\Models\Bank::when($groupId, fn ($query) => $query->where('group_id', $groupId))
                                    ->get()
                                    ->pluck('label', 'id');
                            })
                            ->extraAttributes([
                                'id' => 'bank_id_select',
                            ]),

                        Forms\Components\TextInput::make('nominal')
                            ->label('Nominal')
                            ->numeric()
                            ->extraAttributes([
                                'id' => 'nominal', // Tambahkan id untuk akses di JS
                            ]),

                        Forms\Components\TextInput::make('saldo_awal')
                            ->label('Saldo Awal')
                            ->disabled()
                            ->extraAttributes([
                                'id' => 'saldo_awal_display', // ID untuk akses di JS
                            ]),

                        Forms\Components\TextInput::make('saldo_akhir')
                            ->label('Saldo Akhir')
                             ->extraAttributes([
                                'id' => 'saldo_akhir_display', // ID untuk akses di JS
                            ])
                            ->disabled(),
                      
                    ])
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Waktu')->dateTime('d/m/Y H:i:s'),
                TextColumn::make('operator.name')->label('Operator'),
                TextColumn::make('nama_rek')->label('Nama Rekening'),
                TextColumn::make('bank.label')->label('Rekening'),
                TextColumn::make('nominal')->label('Nominal'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        "P" => 'warning',  // Hijau
                        "S" => 'success',  // Merah
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        "P" => 'Pending',
                        "S"=> 'Success',
                        default => ucfirst($state),
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('proses')
                    ->label('Proses')
                    ->modalHeading('Proses Dana Gantung')
                    ->icon('heroicon-s-clipboard-document-check')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('nama_rek')
                            ->label('Nama Rekening')
                            ->default(fn ($record) => $record->nama_rek)
                            ->disabled(),
                        Forms\Components\Select::make('member_id')
                            ->label('User ID')
                            ->relationship('member', 'username')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $member = Member::with('group')->where('id',$state)->first();
                                if ($member) {
                                    $set('sitename', $member->group->name);
                                } else {
                                    $set('sitename', null);
                                }
                            }),
                        Forms\Components\TextInput::make('sitename')
                            ->label('Sitename')
                            ->disabled(),
                        Forms\Components\TextInput::make('nominal')
                            ->label('Nominal')
                            ->default(fn ($record) => $record->nominal)
                            ->disabled(),
                        Forms\Components\Textarea::make('notes')
                                ->autosize(),
                        Forms\Components\TextInput::make('rekening')
                            ->label('Ke Rekening')
                            ->default(fn ($record) => $record->bank->label)
                            ->disabled(),
                        
                    ])
                    ->action(function (array $data, $record) {
                       

                        DB::transaction(function () use ($data, $record) {


                            $member = Member::find($data['member_id']);

                            $firstDepo = 'N';

                            if($member->first_depo == 'Y'){
                                $firstDepo = 'Y';
                                $member->update(['first_depo' => 'N']);
                            }

                            $group = Group::where('id', $member->group_id)->lockForUpdate()->first();

                            $systemType = $group->is_coin_system ? 'coin' : 'non-coin';

                            $transaction = Transaction::create([
                                'group_id' =>   $member->group_id,
                                'operator_id' =>  Auth::id(),
                                'member_id' => $data['member_id'],
                                'first_depo' => $firstDepo,
                                'bank_id' => $record->bank_id,
                                'total' =>  $record->nominal,
                                'fee' => 0,
                                'bonus' =>0,
                                'note' =>  $data['notes'] ?? null,
                                'type' => 'deposit',
                                'system_type' => $systemType,
                                'deposit' => $record->nominal,
                                'withdraw' => 0,
                            ]);

                            // Both systems: group and bank saldo increase
                            $group->increment('saldo',  $record->nominal);
                            $bank = Bank::where('id', $record->bank_id)->lockForUpdate()->first();
                            // No need to increment bank saldo here because it was already incremented when PendingDepo was created
                            // Unless is_non_coin_system check was applied there.
                            // In CreatePendingDepo, I already added if ($group->is_non_coin_system) { $bank->increment('saldo', $data['nominal']); }

                            if ($systemType === 'coin') {
                                $group->decrement('koin',  $record->nominal);

                                $koin = -$record->nominal;

                                $history = Koinhistory::create([
                                    'group_id' => $member->group_id,
                                    'keterangan' => 'deposit',
                                    'member_id' => $member->id,
                                    'koin' => $koin,
                                    'saldo' => $group->saldo,
                                    'operator_id' => Auth::id(),
                                    
                                ]);
                            }

                            

                            $record->delete();

                            Notification::make()
                                ->title('Dana gantung berhasil diproses!')
                                ->success()
                                ->send();
                        });

                    })
 
                    ->visible(fn ($record) =>
                        auth()->user()->hasPermissionTo('create_pendingdepo') && $record->status == "P"
                    )
                    ->slideOver(),
               
                  
               
                Action::make('delete')
                    ->label('Hapus')
                    ->icon('heroicon-s-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi delete data')
                    ->modalDescription('Yakin ingin menghapus data ini?')
                    ->action(function (\App\Models\Pendingdepo $record) {
                        $bank = Bank::where('id', $record->bank_id)->lockForUpdate()->first();

                        if ($record->bank->group->is_non_coin_system) {
                            $bank->decrement('saldo',  $record->nominal);
                        }

                        $log = Logtransaksi::create([
                            'operator_id' =>  Auth::id(),
                            'bank_id' =>  $record->bank_id,
                            'type_transaksi' => 'DPR',
                            'type' =>  'withdraw',
                            'rekenin_name' => $record->bank->label,
                            'deposit' => 0,
                            'withdraw' => $record->nominal,
                            'saldo' => $bank->saldo,
                            'note' =>  'Delete Depo Gantung',
                        ]);

                        
                        $record->delete();

                   
                    })
                    ->visible(fn () =>
                        auth()->user()->hasPermissionTo('create_pendingdepo')
                    ),
            ])
            
            ->filters([
                Tables\Filters\SelectFilter::make('group_id')
                    ->label('Group')
                    ->relationship('operator.group', 'name')
                    ->default(Group::getActiveGroupId())
                    ->visible(fn () => auth()->user()->group_id == 0),
            ]);
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $groupId = Group::getActiveGroupId();

        return parent::getEloquentQuery()
            ->when($groupId, function ($query) use ($groupId) {
                return $query->whereHas('operator', function ($q) use ($groupId) {
                    $q->where('group_id', $groupId);
                });
            })
            ->orderBy('created_at', 'desc');
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 3;
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
            PendingDepoOverview::class,
          
          
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPendingDepos::route('/'),
            'create' => Pages\CreatePendingDepo::route('/create'),
            'edit' => Pages\EditPendingDepo::route('/{record}/edit'),
        ];
    }
}
