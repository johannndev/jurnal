<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PendingwdResource\Pages;
use App\Filament\Resources\PendingwdResource\RelationManagers;
use App\Filament\Resources\PendingwdResource\Widgets\PendingWdOverview;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\DatePicker;


class PendingwdResource extends Resource
{
    protected static ?string $model = Pending::class;

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
                    Forms\Components\TextInput::make('nama_rek')
                        ->label('Nama Rekening')
                        ->required(),

                    Forms\Components\Select::make('bank_id')
                        ->label('Rekening Depo')
                        ->options(Bank::pluck('label', 'id'))
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $bank = Bank::find($state);
                            $saldo = $bank?->saldo ?? 0;

                            $set('saldo_awal', $saldo);
                            $set('saldo_akhir', $saldo);
                        }),

                    Forms\Components\TextInput::make('nominal')
                        ->label('Nominal')
                        ->numeric()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            $saldoAwal = $get('saldo_awal') ?? 0;
                            $set('saldo_akhir', $saldoAwal - $state);
                        }),

                    Forms\Components\TextInput::make('saldo_awal')
                        ->label('Saldo Awal')
                        ->disabled(),

                    Forms\Components\TextInput::make('saldo_akhir')
                        ->label('Saldo Akhir')
                        ->disabled(),
                ])
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
                        1 => 'warning',  // Hijau
                        2 => 'success',  // Merah
                        default => 'secondary',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        1 => 'Pending',
                        2 => 'Success',
                        default => ucfirst($state),
                    }),
            ])
            ->filters([
                //
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
                        $bank = Bank::where('id', $record->bank_id)->lockForUpdate()->first();

                        $bank->decrement('saldo',  $record->nominal);
                        $group->decrement('saldo',  $record->nominal);


                        $log = Logtransaksi::create([
                            'operator_id' =>  Auth::id(),
                            'bank_id' =>  $record->bank_id,
                            'type_transaksi' => 'WDP',
                            'type' =>  'withdraw',
                            'rekenin_name' => $record->bank->label,
                            'deposit' => 0,
                            'withdraw' => $record->nominal,
                            'saldo' => $bank->saldo,
                            'note' =>  'WD Gantung',
                        ]);
                    })
                    ->visible(fn ($record) => $record->status === 1),

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

                        $group->decrement('koin', $record->nominal);

                        $history = Koinhistory::create([
                            'group_id' => $record->operator->group_id,
                            'keterangan' => 'Withdraw gantung refund',
                            'member_id' => 0,
                            'koin' => $record->nominal,
                            'saldo' => $group->saldo,
                            'operator_id' => Auth::id(),
                            
                        ]);

                        if( $record->type == 1 && $record->status == 2){

                            $bank->increment('saldo',  $record->nominal);
                            $group->increment('saldo',  $record->nominal);

                                $log = Logtransaksi::create([
                                    'operator_id' =>  Auth::id(),
                                    'bank_id' =>  $record->bank_id,
                                    'type_transaksi' => 'WDR',
                                    'type' =>  'deposite',
                                    'rekenin_name' => $record->bank->label,
                                    'deposit' => $record->nominal,
                                    'withdraw' => 0,
                                    'saldo' => $bank->saldo,
                                    'note' =>  'WD Gantung Refund',
                                ]);

                             

                        }else {

                        }

                        
                        $record->delete();

                   
                    }),
            ])
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
            ->where('type', 1)
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
