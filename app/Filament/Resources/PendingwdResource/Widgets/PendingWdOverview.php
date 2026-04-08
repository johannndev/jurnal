<?php

namespace App\Filament\Resources\PendingwdResource\Widgets;

use App\Models\Pendingwd;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use App\Models\Group;

class PendingWdOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $groupId = Group::getActiveGroupId();
        $totalSaldo = Pendingwd::when($groupId, function ($query) use ($groupId) {
                return $query->whereHas('operator', function ($q) use ($groupId) {
                    $q->where('group_id', $groupId);
                });
            })
            ->sum('nominal');

        return [
            Stat::make('Total Wd Gantung', 'Rp '.number_format($totalSaldo,0,'','.')),
        ];
     
    }
}
