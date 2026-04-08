<?php

namespace App\Filament\Resources\PendingDepoResource\Widgets;

use App\Models\Pendingdepo;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use App\Models\Group;

class PendingDepoOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $groupId = Group::getActiveGroupId();
        $totalSaldo = Pendingdepo::when($groupId, function ($query) use ($groupId) {
                return $query->whereHas('operator', function ($q) use ($groupId) {
                    $q->where('group_id', $groupId);
                });
            })
            ->sum('nominal');

        return [
            Stat::make('Total Depo Gantung', 'Rp '.number_format($totalSaldo,0,'','.')),
        ];
    }
}
