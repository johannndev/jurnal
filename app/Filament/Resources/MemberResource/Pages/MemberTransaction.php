<?php

namespace App\Filament\Resources\MemberResource\Pages;

use App\Filament\Resources\MemberResource;
use App\Filament\Widgets\RealtimeClock;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use App\Models\Transaction;
use Filament\Tables\Columns\TextColumn;

class MemberTransaction extends Page implements Tables\Contracts\HasTable
{
    use InteractsWithTable;

    protected static string $resource = MemberResource::class;
    protected static string $view = 'filament.resources.member-resource.pages.member-transaction';

    public ?int $userId = null;
    public float $totalDeposit = 0;
    public float $totalWithdraw = 0;
    public float $balance = 0;

    public function mount(int $record): void
    {
        $this->userId = $record;
        $this->calculateBalance();
    }

 

    protected function getTableQuery()
    {
        return Transaction::query()->where('member_id', $this->userId)->orderBy('created_at', 'desc');
    }

    protected function calculateBalance(): void
    {
        $result = Transaction::where('member_id', $this->userId)
            ->selectRaw("
                SUM(CASE WHEN type = 'deposit' THEN total ELSE 0 END) as total_deposit,
                SUM(CASE WHEN type = 'withdraw' THEN total ELSE 0 END) as total_withdraw
            ")
            ->first();

            

        $this->totalDeposit = $result->total_deposit ?? 0;
        $this->totalWithdraw = $result->total_withdraw ?? 0;
        $this->balance = $this->totalDeposit - $this->totalWithdraw;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_deposit')
                ->label('Deposit')
                ->url(fn () => route('filament.admin.resources.members.create-transaction', ['record' => $this->userId,'type'=>'deposit']))
                ->icon('heroicon-o-plus'),

            Action::make('add_withdraw')
                ->label('Withdraw')
                ->url(fn () => route('filament.admin.resources.members.create-transaction', ['record' => $this->userId,'type'=>'withdraw']))
                ->icon('heroicon-o-plus'),
        ];
    }

    protected function getTableColumns(): array
    {
        return [
            
            TextColumn::make('created_at')->label('Tanggal')->searchable(),
            TextColumn::make('operator.name')->label('Operator'),
            TextColumn::make('member.username')->label('username'),
            TextColumn::make('bank.label')->label('Rek. Depo'),
            TextColumn::make('deposit')->label('Deposit')->numeric(locale: 'id'),
            TextColumn::make('withdraw')->label('Withdraw')->numeric(locale: 'id'),
            TextColumn::make('note')->label('Note'),
            TextColumn::make('fee')->label('B.trf')->numeric(locale: 'id'),
            TextColumn::make('bonus')->label('Bonus')->numeric(locale: 'id'),
            TextColumn::make('total')->label('Total')->numeric(locale: 'id'),
            
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\EditAction::make(),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            Tables\Actions\DeleteBulkAction::make(),
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
           
        ];
    }


    
}
