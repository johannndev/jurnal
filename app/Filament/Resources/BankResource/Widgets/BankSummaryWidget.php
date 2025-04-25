<?php

namespace App\Filament\Resources\BankResource\Widgets;

use App\Models\Bank;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BankSummaryWidget extends BaseWidget
{
   
    public ?float $saldo;
    public ?string $label;

   

    
    protected function getStats(): array
    {
        return [
            Stat::make($this->label, 'Rp '.number_format($this->saldo,0,'','.')),
        ];
    }
}
