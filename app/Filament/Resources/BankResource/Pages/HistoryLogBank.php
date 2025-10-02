<?php

namespace App\Filament\Resources\BankResource\Pages;

use App\Filament\Resources\BankResource;
use App\Models\Bank;
use App\Models\Transaction;
use Filament\Tables;
use App\Filament\Resources\BankResource\Pages;
use App\Filament\Widgets\RealtimeClock;
use App\Models\Logtransaksi;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;


class HistoryLogBank extends Page implements Tables\Contracts\HasTable
{
    use InteractsWithTable;

    protected static string $resource = BankResource::class;

    protected static string $view = 'filament.resources.bank-resource.pages.history-log-bank';

    public static function canAccess(array $parameter = []):bool
    {
        return auth()->user()?->can('history_bank');
    }

   
    public Bank $record;

    public function mount(Bank $record): void
    {
        $this->record = $record;
    }

    public function getTitle(): string
    {
        return $this->record->label; // ambil label dari bank
    }

    protected function getTableQuery()
    {
        return Logtransaksi::query()
            //->whereIn('type_transaksi', ['TR','WDP','WDR','DPR','DPT','PD'])
            ->where('bank_id', $this->record->id)
            ->orderBy('created_at', 'desc');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('created_at')->label('Waktu')->dateTime('d/m/Y H:i:s'),
            TextColumn::make('type')
                ->label('Remark')
                ->badge()
                ->color(fn ($state) => match ($state) {
                    'deposit' => 'success',  // Hijau
                    'withdraw' => 'danger',  // Merah
                    'edit' => 'warning',  // Merah
                    default => 'secondary',
                })
                ->formatStateUsing(fn ($state) => match ($state) {
                    'edit' => 'Edit Transaksi',
                    default => ucfirst($state),
                }),
            TextColumn::make('note')->label('Keterangan'),
            TextColumn::make('deposit')->label('Dana Masuk')->numeric(locale: 'id'),
            TextColumn::make('withdraw')->label('Dana Keluar')->numeric(locale: 'id'),
            TextColumn::make('saldo')->label('Saldo')->numeric(locale: 'id'),
            TextColumn::make('operator.name')->label('Operator'),
           
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            Filter::make('created_at')
                ->form([
                    DatePicker::make('start_date')->label('Dari Tanggal'),
                    DatePicker::make('end_date')->label('Sampai Tanggal'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['start_date'], fn ($q) => $q->whereDate('created_at', '>=', $data['start_date']))
                        ->when($data['end_date'], fn ($q) => $q->whereDate('created_at', '<=', $data['end_date']));
                }),
        ];
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 3;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RealtimeClock::class,
           
        ];
    }

  
}
