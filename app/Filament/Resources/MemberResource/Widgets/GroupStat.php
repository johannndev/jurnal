<?php

namespace App\Filament\Resources\MemberResource\Widgets;

use App\Models\Group;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\HtmlString;
use Filament\Facades\Filament;


class GroupStat extends BaseWidget
{

    public ?int $groupId = null;

    
    protected function getStats(): array
    {
        $gid = Group::getActiveGroupId();
        $qs = '?group_id=' . $gid;
        
        $stat = Group::find($gid);

        if (!$stat) {
            return [];
        }

        $stats = [];

        if ($stat->is_coin_system) {
            $stats[] = Stat::make('Coins',  $stat->koin < 0 ? '-Rp ' . number_format(abs($stat->koin), 0, ',', '.') : 'Rp ' . number_format($stat->koin, 0, ',', '.'))
                ->description(new HtmlString('Coins history <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4 inline ml-1"> <path fill-rule="evenodd" d="M16.72 7.72a.75.75 0 0 1 1.06 0l3.75 3.75a.75.75 0 0 1 0 1.06l-3.75 3.75a.75.75 0 1 1-1.06-1.06l2.47-2.47H3a.75.75 0 0 1 0-1.5h16.19l-2.47-2.47a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /> </svg>'))
                ->color( $stat->koin < 0 ? 'danger' : 'success')
                ->url(route('filament.admin.resources.members.koin-history').$qs);
        }

        if ($stat->is_non_coin_system) {
            $stats[] = Stat::make('Saldo', 'Rp ' . number_format($stat->saldo, 0, ',', '.'))
                ->description('Total Balance')
                ->color('primary');
        }

        return $stats;
    }

    public static function canCache(): bool
    {
        return false;
    }
}
