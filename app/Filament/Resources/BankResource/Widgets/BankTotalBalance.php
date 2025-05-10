<?php

namespace App\Filament\Resources\BankResource\Widgets;

use App\Models\Bank;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BankTotalBalance extends BaseWidget
{
   

    protected function getStats(): array
    {
        $totalSaldo = Bank::sum('saldo');

        return [
            Stat::make('Total Saldo', 'Rp '.number_format($totalSaldo,0,'','.')),
        ];
    }
    
}
