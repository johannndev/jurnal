<?php

namespace App\Filament\Resources\TransaksiResource\Pages;

use App\Filament\Resources\TransaksiResource;
use App\Filament\Resources\TransaksiResource\Widgets\TransactionSummaryWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;


class ListTransaksis extends ListRecords
{
    protected static string $resource = TransaksiResource::class;

    public function getBreadcrumbs(): array
    {
        return [];
    }
    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            TransactionSummaryWidget::class,
        ];
    }

    
}
