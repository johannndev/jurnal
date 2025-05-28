<?php

namespace App\Filament\Resources\PendingwdResource\Pages;

use App\Filament\Resources\PendingwdResource;
use App\Filament\Resources\PendingwdResource\Widgets\PendingWdOverview;
use App\Filament\Widgets\RealtimeClock;
use App\Models\Member;
use App\Models\Pendingwd;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\ListRecords;

class ListPendingwds extends ListRecords
{
    protected static string $resource = PendingwdResource::class;

  

    protected function getHeaderActions(): array
    {
        return [

            Action::make('new')
            ->label('New Pending WD')
            ->modalHeading('Pilih Tipe')
            ->form([
                Forms\Components\Select::make('username')
                    ->label('User ID')
                    ->relationship('member', 'username')
                    ->searchable()
                    ->preload()
                    ->required(),
            ])
            ->action(function (array $data) {

               
                return redirect()->route('filament.admin.resources.pendingwds.create', ['username' => $data['username']]);
            }),
            Action::make('cuci')
                ->label('Cuci')
                ->color('danger')
                ->form([
                    DatePicker::make('start_date')
                        ->label('Dari Tanggal')
                        ->required(),
                    DatePicker::make('end_date')
                        ->label('Sampai Tanggal')
                        ->required(),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $records = Pendingwd::where('type',1)
                        ->whereDate('created_at', '>=', $data['start_date'])
                        ->whereDate('created_at', '<=', $data['end_date'])
                        ->delete();

                })
                ->visible(fn () =>
                    auth()->user()->hasPermissionTo('create_pendingwd')
                ),
        ];
    }

    

    public function getHeaderWidgetsColumns(): int | array
    {
        return 3;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RealtimeClock::class,
            PendingWdOverview::class,
        ];
    }

    
}
