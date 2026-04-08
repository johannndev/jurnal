<?php

namespace App\Filament\Resources\BankResource\Widgets;

use App\Models\Bank;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use App\Models\Group;

class BankTotalBalance extends BaseWidget
{
    protected function getStats(): array
    {
        $groupId = Group::getActiveGroupId();
        $totalSaldo = Bank::when($groupId, fn ($query) => $query->where('group_id', $groupId))->sum('saldo');

        return [
            Stat::make('Total Saldo', 'Rp '.number_format($totalSaldo,0,'','.')),
        ];
    }
    
}
