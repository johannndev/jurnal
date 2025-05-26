<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PendingwdResource\Pages;
use App\Filament\Resources\PendingwdResource\RelationManagers;
use App\Filament\Resources\PendingwdResource\Widgets\PendingWdOverview;
use App\Models\Bank;
use App\Models\Group;
use App\Models\Koinhistory;
use App\Models\Logtransaksi;
use App\Models\Member;
use App\Models\Pendingwd;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class PendingwdResource extends Resource
{
    protected static ?string $model = Pendingwd::class;

    protected static ?string $modelLabel = 'Pending Withdraw';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Pending Withdraw';

    protected static ?string $navigationGroup = 'Pending Transaction';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Grid::make()
                ->columns(1) // Sesuaikan jumlah kolom agar cukup
                ->schema([
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
                                    $set('username', $member->username);
                                    $set('sitename', $member->group->name);
                                    $set('member_name', $member->name);
                                    $set('bank', $member->bank_name);
                                } else {
                                    $set('sitename', null);
                                    $set('member_name', null);
                                    $set('bank', null);
                                    $set('username');

                                 
                                }
                            }),

                       
                       
                        Forms\Components\TextInput::make('nominal')
                            ->label('Nominal')
                            ->numeric()
                            ->required()
                            ->extraAttributes([
                                'id' => 'nominal', // Tambahkan id untuk akses di JS
                            ]),
                        Forms\Components\TextInput::make('sitename')
                            ->label('Sitename')
                            ->disabled(fn () => true)  // tampak disable
                            ->dehydrated(true), 
                        Forms\Components\TextInput::make('member_name')
                            ->label('Nama member')
                            ->disabled(fn () => true)  // tampak disable
                            ->dehydrated(true), 
                        Forms\Components\TextInput::make('bank')
                            ->label('Bank member')
                            ->disabled(fn () => true)  // tampak disable
                            ->dehydrated(true), 
                       
                        
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Waktu')->dateTime('d/m/Y H:i:s'),
                TextColumn::make('operator.name')->label('Operator'),
                TextColumn::make('sitename')->label('Sitename'),
                TextColumn::make('member_name')->label('Nama Member'),
                TextColumn::make('bank')->label('Bank Member'),
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
                        "S" => 'Success',
                        default => ucfirst($state),
                    }),
            ])
            ->filters([
                //
            ])

              ->actions([
                Action::make('proses')
                    ->label('Proses')
                    ->modalHeading('Proses Dana Gantung')
                    ->icon('heroicon-s-clipboard-document-check')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('username')
                            ->label('User ID')
                            ->default(fn ($record) => $record->member->username)
                            ->disabled(),
                        Forms\Components\TextInput::make('nominal')
                            ->label('Nominal')
                            ->default(fn ($record) => $record->nominal)
                            ->disabled(),
                        Forms\Components\TextInput::make('nama_rekening')
                            ->label('Nama Rekening')
                            ->default(fn ($record) => $record->member->bank_name)
                            ->disabled(),
                        Forms\Components\TextInput::make('nomor_rekening')
                            ->label('Nomor Rekening')
                            ->default(fn ($record) => $record->member->bank_number)
                            ->disabled(),
                        Forms\Components\Select::make('wd_bank')
                            ->label('Rekening WD')
                            ->relationship('bank', 'label')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('biaya_transfer')
                            ->label('Biaya Transfers'),
                                    
                    ])
                    ->action(function (array $data, $record) {
                       

                        DB::transaction(function () use ($data, $record) {

                            $total = (float) ($record->nominal + $data['biaya_transfer'] ?? 0);

                            $transaction = Transaction::create([
                                'operator_id' =>  Auth::id(),
                                'member_id' => $record->member_id,
                                'bank_id' => $data['wd_bank'],
                                'total' => $total,
                                'fee' => $data['biaya_transfer'] ?? 0,
                                'bonus' => 0,
                                'note' =>  null,
                                'type' => 'withdraw',
                                'deposit' => 0,
                                'withdraw' => $record->nominal,
                            ]);

                            $member = Member::find($record->member_id);
                            $bank = Bank::where('id', $data['wd_bank'])->lockForUpdate()->first();

                            $bank->decrement('saldo', $total);


                            $log = Logtransaksi::create([
                                'operator_id' =>  $transaction->operator_id,
                                'bank_id' =>  $transaction->bank_id,
                                'type_transaksi' => 'TR',
                                'type' =>  $transaction->type,
                                'rekenin_name' => $bank->label,
                                'deposit' => 0,
                                'withdraw' => $transaction->total,
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
                    ->action(function (\App\Models\Pendingwd $record) {
                       
                        $group = Group::where('id', $record->operator->group_id)->lockForUpdate()->first();
                        
                        $group->decrement('koin',  $record->nominal);
                        $group->increment('saldo',  $record->nominal);

                        $history = Koinhistory::create([
                            'group_id' => $record->operator->group_id,
                            'keterangan' => 'Delete WD gantung',
                            'member_id' => $record->member_id,
                            'koin' => $record->nominal,
                            'saldo' => $group->saldo,
                            'operator_id' => Auth::id(),
                            
                        ]);

                        
                        $record->delete();

                   
                    })
                    ->visible(fn () =>
                        auth()->user()->hasPermissionTo('create_pendingwd')
                    ),
            ])
            // ->actions([
               
                  
            //     Action::make('setSuccess')
            //         ->label('Set Success')
            //         ->icon('heroicon-s-clipboard-document-check')
            //         ->color('success')
            //         ->requiresConfirmation()
            //         ->modalHeading('Konfirmasi Update Status')
            //         ->modalDescription('Yakin ingin mengubah status menjadi Success?')
            //         ->action(function (\App\Models\Pendingwd $record) {
            //             $record->update(['status' => "S"]);

            //             $bank = Bank::where('id', $record->bank_id)->lockForUpdate()->first();
            //             $group = Group::where('id', Auth::user()->group_id)->lockForUpdate()->first();

            //             $bank->decrement('saldo',  $record->nominal);
            //             $group->decrement('saldo',  $record->nominal);

            //             $log = Logtransaksi::create([
            //                 'operator_id' =>  Auth::id(),
            //                 'bank_id' =>  $record->bank_id,
            //                 'type_transaksi' => 'DPP',
            //                 'type' =>  'withdraw',
            //                 'rekenin_name' => $bank->label,
            //                 'deposit' => 0,
            //                 'withdraw' => $record->nominal,
            //                 'saldo' => $bank->saldo,
            //                 'note' =>  'DP Gantung Refund',
            //             ]);
                       
            //         })
            //          ->visible(fn ($record) =>
            //             auth()->user()->hasPermissionTo('create_pendingwd') && $record->status == "P"
            //         ),
            //     Action::make('delete')
            //         ->label('Hapus')
            //         ->icon('heroicon-s-trash')
            //         ->color('danger')
            //         ->requiresConfirmation()
            //         ->modalHeading('Konfirmasi delete data')
            //         ->modalDescription('Yakin ingin menghapus data ini?')
            //         ->action(function (\App\Models\Pendingwd $record) {
                       
            //             $bank = Bank::where('id', $record->bank_id)->lockForUpdate()->first();
            //             $group = Group::where('id', $record->operator->group_id)->lockForUpdate()->first();
                      

            //             $group->decrement('koin', $record->nominal);

            //             $history = Koinhistory::create([
            //                 'group_id' => $record->operator->group_id,
            //                 'keterangan' => 'Withdraw gantung refund',
            //                 'member_id' => 0,
            //                 'koin' => $record->nominal,
            //                 'saldo' => $group->saldo,
            //                 'operator_id' => Auth::id(),
                            
            //             ]);

                        
            //             if( $record->status == 2){

            //                 $bank->increment('saldo',  $record->nominal);
            //                 $group->increment('saldo',  $record->nominal);

            //                 $log = Logtransaksi::create([
            //                     'operator_id' =>  Auth::id(),
            //                     'bank_id' =>  $record->bank_id,
            //                     'type_transaksi' => 'WDR',
            //                     'type' =>  'deposit',
            //                     'rekenin_name' => $bank->label,
            //                     'deposit' =>  $record->nominal,
            //                     'withdraw' => 0,
            //                     'saldo' => $bank->saldo,
            //                     'note' =>  'DP Gantung Refund',
            //                 ]);

                             

            //             }

                        
                     
                        
            //             $record->delete();

                   
            //         })
            //         ->visible(fn () =>
            //             auth()->user()->hasPermissionTo('create_pendingwd')
            //         ),
            // ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //           Tables\Actions\BulkAction::make('delete_with_refund')
            //             ->label('Delete & Refund')
            //             ->icon('heroicon-o-trash')
            //             ->requiresConfirmation()
            //             ->action(function (Collection $records) {

            //                 $wdp = $records->filter(fn ($r) => $r->status == 1 && $r->type == 1)->sum('nominal');
            //                 $wds = $records->filter(fn ($r) => $r->status == 1 && $r->type == 2)->sum('nominal');
                     

                          
            //             })
            //             ->deselectRecordsAfterCompletion(),
            //     ]),
            // ])
            ;
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 3;
    }

    public static function getWidgets(): array
    {
        return [
            PendingWdOverview::class,
          
          
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->orderBy('created_at', 'desc');
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
            'index' => Pages\ListPendingwds::route('/'),
            'create' => Pages\CreatePendingwd::route('/create'),
            
        ];
    }
}
