<?php

namespace App\Filament\Resources\PendingDepoResource\Widgets;

use App\Models\Pendingdepo;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingDepoOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalSaldo = Pendingdepo::sum('nominal');

        return [
            Stat::make('Total Depo Gantung', 'Rp '.number_format($totalSaldo,0,'','.')),
        ];
    }
}
