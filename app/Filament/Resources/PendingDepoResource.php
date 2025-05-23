<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PendingDepoResource\Pages;
use App\Filament\Resources\PendingDepoResource\RelationManagers;
use App\Filament\Resources\PendingDepoResource\Widgets\PendingDepoOverview;
use App\Models\Bank;
use App\Models\Group;
use App\Models\Koinhistory;
use App\Models\Logtransaksi;
use App\Models\Pending;
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

class PendingDepoResource extends Resource
{
    protected static ?string $model = Pending::class;

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
                        "1" => 'warning',  // Hijau
                        "2" => 'success',  // Merah
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        "1" => 'Pending',
                        "2"=> 'Success',
                        default => ucfirst($state),
                    }),
            ])
         
            ->actions([
               
                  
                Action::make('setSuccess')
                    ->label('Set Success')
                    ->icon('heroicon-s-clipboard-document-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Update Status')
                    ->modalDescription('Yakin ingin mengubah status menjadi Success?')
                    ->action(function (\App\Models\Pending $record) {
                        $record->update(['status' => 2]);

                        $group = Group::where('id', $record->operator->group_id)->lockForUpdate()->first();
                        
                        $group->decrement('koin', $record->nominal);
                        
                        $history = Koinhistory::create([
                            'group_id' => $record->operator->group_id,
                            'keterangan' => 'Deposit gantung',
                            'member_id' => 0,
                            'koin' => $record->nominal,
                            'saldo' => $group->saldo,
                            'operator_id' => Auth::id(),
                            
                        ]);

                       
                    })
                    ->visible(fn ($record) => $record->status == 1),

                Action::make('delete')
                    ->label('Hapus')
                    ->icon('heroicon-s-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi delete data')
                    ->modalDescription('Yakin ingin menghapus data ini?')
                    ->action(function (\App\Models\Pending $record) {
                       
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

                   
                    }),
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
