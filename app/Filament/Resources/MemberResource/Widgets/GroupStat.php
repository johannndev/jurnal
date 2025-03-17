<?php

namespace App\Filament\Resources\MemberResource\Widgets;

use App\Models\Group;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GroupStat extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();

        $stat = Group::find($user->group_id);

        return [
            Stat::make('Coins',  $stat->koin < 0 ? '-Rp ' . number_format(abs($stat->koin), 0, ',', '.') : 'Rp ' . number_format($stat->koin, 0, ',', '.'))
                ->color( $stat->koin < 0 ? 'danger' : 'success')
                ->url(Route('filament.admin.resources.members.koin-history')),
            Stat::make('Saldo', $stat->saldo < 0 ? '-Rp ' . number_format(abs($stat->saldo), 0, ',', '.') : 'Rp ' . number_format($stat->saldo, 0, ',', '.'))
                ->color( $stat->saldo < 0 ? 'danger' : 'success'),
        ];
    }
}
