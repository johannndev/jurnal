<?php

namespace App\Filament\Resources\BankResource\Pages;

use App\Filament\Resources\BankResource;
use App\Models\Logtransaksi;
use Filament\Resources\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\Filter;

class LogIncome extends Page implements Tables\Contracts\HasTable
{
    use InteractsWithTable;

    protected static string $resource = BankResource::class;

    protected static string $view = 'filament.resources.bank-resource.pages.log-income';
    
    protected function getTableQuery()
    {
        return Logtransaksi::query()
            ->where('type_transaksi', 'IN')
            ->orderBy('created_at', 'desc');
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('created_at')->label('Waktu')->dateTime('d/m/Y H:i:s'),
            TextColumn::make('type')
                ->label('Jenis')
                ->badge()
                ->color(fn ($state) => match ($state) {
                    'deposit' => 'success',  // Hijau
                    'withdraw' => 'danger',  // Merah
                    'edit' => 'warning',  // Merah
                    default => 'secondary',
                })
                ->formatStateUsing(fn ($state) => match ($state) {
                    'deposit' => 'Dana Masuk',
                    'withdraw' => 'Dana Keluar',
                    default => ucfirst($state),
                }),
            TextColumn::make('rekenin_name')->label('Nama Rekening'),
            TextColumn::make('deposit')->label('Nominal')->numeric(locale: 'id'),
            TextColumn::make('note')->label('Note'),
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
}
