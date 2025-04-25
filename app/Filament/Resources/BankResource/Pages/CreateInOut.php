<?php

namespace App\Filament\Resources\BankResource\Pages;

use App\Filament\Resources\BankResource;
use App\Filament\Resources\BankResource\Widgets\BankSummaryWidget;
use App\Models\Bank;
use App\Models\Logtransaksi;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class CreateInOut extends Page
{
    protected static string $resource = BankResource::class;

    protected static string $view = 'filament.resources.bank-resource.pages.create-in-out';

    protected static ?string $title = 'Expanses/Income';

    public Bank $bank;

    public ?int $amount = 0;
    public ?int $operator = 0;
    public ?string $typeCash = null;
    public ?string $note = null;

    public function mount(Bank $record)
    {
        $this->bank = $record;
        $this->operator =  Auth::id();
    }

    public function save()
    {
        $this->validate();

        DB::transaction(function () {

            $bankUpdate = Bank::where('id', $this->bank->id)->lockForUpdate()->first();
   
            if (!$bankUpdate) {
                Notification::make()
                    ->title('bank tidak ditemukan!')
                    ->danger()
                    ->send();
                return;
            }

            if($this->typeCash == 'in'){
                $bankUpdate->increment('saldo',  $this->amount);
            }else{
                $bankUpdate->decrement('saldo',  $this->amount);
            }
    
            $log = Logtransaksi::create([
                'operator_id' =>   $this->operator,
                'bank_id' =>  $this->bank->id,
                'type_transaksi' => $this->typeCash === 'in' ? 'IN' : 'EP',
                'type' =>  $this->typeCash === 'in' ? 'deposit' : 'withdraw',
                'rekenin_name' => $bankUpdate->label,
                'deposit' =>  $this->typeCash === 'in' ? $this->amount : 0,
                'withdraw' =>  $this->typeCash === 'out' ? $this->amount : 0,
                'saldo' => $bankUpdate->saldo,
                'note' => $this->note,
              
            ]);

       
        });

       

        Notification::make()
            ->title('Transaksi berhasil ditambahkan!')
            ->success()
            ->send();

        return redirect()->route('filament.admin.resources.banks.index');
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Grid::make(1) // Secara otomatis membuat layout 2 kolom
                ->schema([
                    Forms\Components\Radio::make('typeCash')
                        ->label('Type Cash')
                        ->options([
                            'in' => 'Cash In',
                            'out' => 'Cash Out',
                        ])
                        ->inline()
                        
                        ->required()
                        ->validationAttribute('Type Cash'),
                  
                    Forms\Components\TextInput::make('amount')
                        ->numeric()
                        ->label('Nominal')
                        ->required()
                        ->rules(['min:1'])
                        ->validationAttribute('Nominal'),
                        
                    Forms\Components\Textarea::make('note')
                        ->label('Notes')
                        ->rows(5)
                        
                                    
                  


                ]),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BankSummaryWidget::make([
                'label' => $this->bank->label,
                'saldo' => $this->bank->saldo
            ])
        ];
    }
}
