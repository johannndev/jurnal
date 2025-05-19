<?php

namespace App\Filament\Resources\PendingwdResource\Widgets;

use App\Models\Pendingwd;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingWdOverview extends BaseWidget
{
    protected function getStats(): array
    {
       
        $totalSaldo = Pendingwd::sum('nominal');

        return [
            Stat::make('Total Depo Gantung', 'Rp '.number_format($totalSaldo,0,'','.')),
        ];
     
    }
}
