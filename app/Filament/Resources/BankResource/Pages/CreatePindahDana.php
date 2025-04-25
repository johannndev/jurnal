<?php

namespace App\Filament\Resources\BankResource\Pages;

use App\Filament\Resources\BankResource;
use App\Models\Bank;
use App\Models\Logtransaksi;
use Filament\Resources\Pages\Page;

use Filament\Forms;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class CreatePindahDana extends Page
{
    protected static string $resource = BankResource::class;

    protected static string $view = 'filament.resources.bank-resource.pages.create-pindah-dana';

    public Bank $bank;

    public ?float $amount = 0;
    public ?float $b_trf = 0;
    public ?string $from = null;
    public ?int $to = null;
    public ?int $operator = 0;
    public ?float $total = 0;


    public function mount(Bank $record)
    {
        $this->bank = $record;
        $this->operator =  Auth::id();

        // $bankFrom = Bank::find($this->bank);

        $this->from = $this->bank->label;
    }

    public function save()
    {
        $this->validate();

        $this->total = (float) ($this->amount - $this->b_trf);
      

        DB::transaction(function () {

            $bankFrom = Bank::where('id', $this->bank->id)->lockForUpdate()->first();
            $bankTo = Bank::where('id', $this->to)->lockForUpdate()->first();

            if (!$bankFrom) {
                Notification::make()
                    ->title('Rekening Dari tidak ditemukan!')
                    ->danger()
                    ->send();
                return;
            }

            if (!$bankTo) {
                Notification::make()
                    ->title('Rekening Ke tidak ditemukan!')
                    ->danger()
                    ->send();
                return;
            }
    
    
            $bankFrom->decrement('saldo',  $this->total);
            $bankTo->increment('saldo',  $this->total);

            $logFrom = Logtransaksi::create([
                'operator_id' =>   $this->operator,
                'bank_id' =>  $bankFrom->id,
                'type_transaksi' => 'PD',
                'type' =>  'withdraw',
                'rekenin_name' => $bankFrom->label,
                'deposit' =>  0,
                'withdraw' =>  $this->total,
                'saldo' =>   $bankFrom->saldo,
              
            ]);

            $logTo = Logtransaksi::create([
                'operator_id' =>   $this->operator,
                'bank_id' =>  $bankTo->id,
                'type_transaksi' => 'PD',
                'type' =>  'deposit',
                'rekenin_name' => $bankTo->label,
                'deposit' =>  $this->total,
                'withdraw' =>  0,
                'saldo' =>   $bankTo->saldo,
              
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
                    Forms\Components\TextInput::make('from')
                        ->label('Dari Rekening')
                        ->readOnly(),
                    Forms\Components\TextInput::make('amount')
                        ->numeric()
                        ->label('nominal')
                        ->required()
                        ->rules(['min:1'])
                        ->validationAttribute('Nominal'),
                    Forms\Components\TextInput::make('b_trf')
                        ->numeric()
                        ->label('Biaya Transfer'),
                    Forms\Components\Select::make('to')
                        ->label('Ke Rekaning')
                        ->options(Bank::pluck('label', 'id')) 
                        ->rules(['required'])
                        ->validationAttribute('Ke Rekaning'),
                  


                ]),
        ];
    }

  

}
