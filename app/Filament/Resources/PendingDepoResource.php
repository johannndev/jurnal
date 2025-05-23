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

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                                return \App\Models\Bank::all()->pluck('label', 'id');
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


                            $transaction = Transaction::create([
                                'operator_id' =>  Auth::id(),
                                'member_id' => $data['member_id'],
                                'bank_id' => $record->bank_id,
                                'total' =>  $record->nominal,
                                'fee' => 0,
                                'bonus' =>0,
                                'note' =>  $data['notes'] ?? null,
                                'type' => 'deposit',
                                'deposit' => $record->nominal,
                                'withdraw' => 0,
                            ]);

                            $member = Member::find($data['member_id']);
                            $bank = Bank::where('id', $record->bank_id)->lockForUpdate()->first();
                            $group = Group::where('id', $member->group_id)->lockForUpdate()->first();

                            $group->decrement('koin',  $record->nominal);
                            $group->increment('saldo',  $record->nominal);

                            $koin = -$record->nominal;


                            $history = Koinhistory::create([
                                'group_id' => $member->group_id,
                                'keterangan' => 'deposit',
                                'member_id' => $member->id,
                                'koin' => $koin,
                                'saldo' => $group->saldo,
                                'operator_id' => Auth::id(),
                                
                            ]);

                            $log = Logtransaksi::create([
                                'operator_id' =>  $transaction->operator_id,
                                'bank_id' =>  $transaction->bank_id,
                                'type_transaksi' => 'TR',
                                'type' =>  $transaction->type,
                                'rekenin_name' => $bank->label,
                                'deposit' => $transaction->total,
                                'withdraw' => 0,
                                'saldo' => $bank->saldo,
                                'note' =>  $member->name,
                            ]);

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
                       
                        $group = Group::where('id', $record->operator->group_id)->lockForUpdate()->first();
                        $bank = Bank::where('id', $record->bank_id)->lockForUpdate()->first();

                        $bank->decrement('saldo',  $record->nominal);
                        $group->decrement('saldo',  $record->nominal);

                        $log = Logtransaksi::create([
                            'operator_id' =>  Auth::id(),
                            'bank_id' =>  $record->bank_id,
                            'type_transaksi' => 'DPR',
                            'type' =>  'withdraw',
                            'rekenin_name' => $record->bank->label,
                            'deposit' => 0,
                            'withdraw' => $record->nominal,
                            'saldo' => $bank->saldo,
                            'note' =>  'DP Gantung Refund',
                        ]);

                       

                        if( $record->status == 2){

                            $group->increment('koin', $record->nominal);

                            $history = Koinhistory::create([
                                'group_id' => $record->operator->group_id,
                                'keterangan' => 'Deposit gantung refund',
                                'member_id' => 0,
                                'koin' => $record->nominal,
                                'saldo' => $group->saldo,
                                'operator_id' => Auth::id(),
                                
                            ]);

                             

                        }

                        
                        $record->delete();

                   
                    })
                    ->visible(fn () =>
                        auth()->user()->hasPermissionTo('create_pendingdepo')
                    ),
            ])
            
            ->filters([
                //
            ]);
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //     ]),
            // ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
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
