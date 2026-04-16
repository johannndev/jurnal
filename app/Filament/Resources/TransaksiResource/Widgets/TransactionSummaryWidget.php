<?php

namespace App\Filament\Resources\TransaksiResource\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

use App\Models\Group;

class TransactionSummaryWidget extends Widget
{
    protected static string $view = 'filament.resources.transaksi-resource.widgets.transaction-summary-widget';
    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $startDate = request('tableFilters')['Tanggal']['start_date'] ?? null;
        $endDate = request('tableFilters')['Tanggal']['end_date'] ?? null;
        $groupId = Group::getActiveGroupId();

        $query = DB::table('transactions');

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Audit dd with context
        $auditQuery = clone $query;
        $auditData = $auditQuery->select('type', DB::raw('count(*) as count'), DB::raw('sum(bonus) as total_bonus'))
            ->groupBy('type')
            ->get();
        
        // This will halt execution and show the data in your browser with context
        dd([
            'filtered_group_id' => $groupId,
            'filtered_start_date' => $startDate,
            'filtered_end_date' => $endDate,
            'audit_results' => $auditData->toArray()
        ]);

        $data = $query->selectRaw('
            COUNT(DISTINCT CASE WHEN type = "deposit" THEN member_id END) AS player_depo,
            COUNT(DISTINCT CASE WHEN type = "withdraw" THEN member_id END) AS player_wd,

            COUNT(CASE WHEN type = "deposit" THEN 1 END) AS jumlah_deposit,
            SUM(CASE WHEN type = "deposit" THEN total ELSE 0 END) AS total_deposit,

            COUNT(CASE WHEN type = "deposit" AND bonus > 0 THEN 1 END) AS jumlah_bonus_deposit,
            SUM(CASE WHEN type = "deposit" AND bonus > 0 THEN bonus ELSE 0 END) AS total_bonus_deposit,

            COUNT(CASE WHEN type = "withdraw" AND bonus > 0 THEN 1 END) AS jumlah_bonus_withdraw,
            SUM(CASE WHEN type = "withdraw" AND bonus > 0 THEN bonus ELSE 0 END) AS total_bonus_withdraw,

            COUNT(CASE WHEN type = "withdraw" THEN 1 END) AS jumlah_withdraw,
            SUM(CASE WHEN type = "withdraw" THEN total ELSE 0 END) AS total_withdraw,

            COUNT(CASE WHEN type = "deposit" AND first_depo = "Y" THEN 1 END) AS jumlah_first_deposit,
            SUM(CASE WHEN type = "deposit" AND first_depo = "Y" THEN total ELSE 0 END) AS total_first_deposit
        ')->first();

        return [
            'jumlahDeposit' => $data->jumlah_deposit,
            'totalDeposit' => $data->total_deposit,
            'jumlahBonusDepo' => $data->jumlah_bonus_deposit,
            'totalBonusDepo' => $data->total_bonus_deposit,
            'jumlahBonusWd' => $data->jumlah_bonus_withdraw,
            'totalBonusWd' => $data->total_bonus_withdraw,
            'jumlahWithdraw' => $data->jumlah_withdraw,
            'totalWithdraw' => $data->total_withdraw,
            'playerDepo' => $data->player_depo,
            'playerWd' => $data->player_wd,
            'jumlahFirstDepo' => $data->jumlah_first_deposit,
            'totalFirstDepo' => $data->total_first_deposit,
        ];  
    }
}
