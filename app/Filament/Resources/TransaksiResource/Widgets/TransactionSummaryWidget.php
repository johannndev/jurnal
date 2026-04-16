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
        $filters = request('tableFilters') ?? [];

        $startDate = $filters['Tanggal']['start_date'] ?? null;
        $endDate = $filters['Tanggal']['end_date'] ?? null;
        $groupId = $filters['group_id']['group_id'] ?? Group::getActiveGroupId();
        $operatorId = $filters['operator_id']['operator_id'] ?? null;
        $typeFilter = $filters['type']['type'] ?? 'all';
        $username = $filters['member_search']['username'] ?? null;

        $query = DB::table('transactions')
            ->when($groupId, fn ($q) => $q->where('group_id', $groupId))
            ->when($startDate, fn ($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn ($q) => $q->whereDate('created_at', '<=', $endDate));

        if ($operatorId) {
            $query->where('operator_id', $operatorId);
        }

        // Align filters with strict transaction types
        if ($typeFilter === 'deposit') {
            $query->where(function($q) {
                $q->where('deposit', '>', 0)
                  ->orWhere('type', 'input-bonus');
            });
        } elseif ($typeFilter === 'withdraw') {
            $query->where(function($q) {
                $q->where('withdraw', '>', 0)
                  ->orWhere('type', 'tarik-bonus');
            });
        }

        if ($username) {
            $query->whereExists(function ($q) use ($username) {
                $q->select(DB::raw(1))
                    ->from('members')
                    ->whereColumn('members.id', 'transactions.member_id')
                    ->where('members.username', $username);
            });
        }

        // Audit raw transactions with bonus > 0 for analysis
        $rawTransactionsWithBonus = (clone $query)->where('bonus', '>', 0)->get();

        $summaryData = $query->selectRaw('
            COUNT(DISTINCT CASE WHEN type = "deposit" THEN member_id END) AS player_depo,
            COUNT(DISTINCT CASE WHEN type = "withdraw" THEN member_id END) AS player_wd,

            COUNT(CASE WHEN type = "deposit" THEN 1 END) AS jumlah_deposit,
            SUM(CASE WHEN type = "deposit" THEN deposit ELSE 0 END) AS total_deposit,

            COUNT(CASE WHEN type = "input-bonus" THEN 1 END) AS jumlah_bonus_deposit,
            SUM(CASE WHEN type = "input-bonus" THEN bonus ELSE 0 END) AS total_bonus_deposit,

            COUNT(CASE WHEN type = "tarik-bonus" THEN 1 END) AS jumlah_bonus_withdraw,
            SUM(CASE WHEN type = "tarik-bonus" THEN bonus ELSE 0 END) AS total_bonus_withdraw,

            COUNT(CASE WHEN type = "withdraw" THEN 1 END) AS jumlah_withdraw,
            SUM(CASE WHEN type = "withdraw" THEN withdraw ELSE 0 END) AS total_withdraw,

            COUNT(CASE WHEN type = "deposit" AND first_depo = "Y" THEN 1 END) AS jumlah_first_deposit,
            SUM(CASE WHEN type = "deposit" AND first_depo = "Y" THEN deposit ELSE 0 END) AS total_first_deposit
        ')->first();

        // Audit dd
        // dd([
        //     'STRICT_TYPE_ONLY' => 'Bonus calculations strictly use type check only',
        //     'RAW_DATABASE_RECORDS_WITH_BONUS' => $rawTransactionsWithBonus->toArray(),
        //     'SUMMARY_CALCULATION' => $summaryData
        // ]);

        return [
            'jumlahDeposit' => $summaryData->jumlah_deposit,
            'totalDeposit' => $summaryData->total_deposit,
            'jumlahBonusDepo' => $summaryData->jumlah_bonus_deposit,
            'totalBonusDepo' => $summaryData->total_bonus_deposit,
            'jumlahBonusWd' => $summaryData->jumlah_bonus_withdraw,
            'totalBonusWd' => $summaryData->total_bonus_withdraw,
            'jumlahWithdraw' => $summaryData->jumlah_withdraw,
            'totalWithdraw' => $summaryData->total_withdraw,
            'playerDepo' => $summaryData->player_depo,
            'playerWd' => $summaryData->player_wd,
            'jumlahFirstDepo' => $summaryData->jumlah_first_deposit,
            'totalFirstDepo' => $summaryData->total_first_deposit,
        ];  
    }
}
