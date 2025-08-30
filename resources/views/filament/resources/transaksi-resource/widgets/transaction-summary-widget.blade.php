<x-filament::widget>
    <x-filament::card>
       <x-filament::grid
            :default="2"
            class="text-sm gap-6"
        >
            <div>
                <x-filament::grid
                    :default="2"
                    class="text-sm gap-4 font-bold"
                >
                    <div class="text-right">
                        Jumlah Transaksi Deposit:
                    </div>
                    <div>
                        {{ $jumlahDeposit }}
                    </div>
                    <div class="text-right">
                        Total Transaksi Deposit:
                    </div>
                    <div>
                        {{ number_format($totalDeposit) }}
                    </div>
                    <div class="text-right">
                       Jumlah Transaksi Bonus:
                    </div>
                    <div>
                       {{ $jumlahBonusDepo }}
                    </div>
                    <div class="text-right">
                       Total Transaksi Bonus:
                    </div>
                    <div>
                       {{ number_format($totalBonusDepo) }}
                    </div>
                    <div class="text-right">
                       Player Deposit:
                    </div>
                    <div>
                      {{$playerDepo}}
                    </div>


                    <div class="text-right">
                       Jumlah new Deposit:
                    </div>
                    <div>
                       {{$jumlahFirstDepo}}
                    </div>

                </x-filament::grid>
                
            </div>
            <div>
                <x-filament::grid
                    :default="2"
                    class="text-sm gap-4 font-bold"
                >
                    <div class="text-right">
                        Jumlah Transaksi Withdraw:
                    </div>
                    <div>
                       {{ $jumlahWithdraw }}
                    </div>
                    <div class="text-right">
                        Total Transaksi Withdraw:
                    </div>
                    <div>
                       {{ number_format($totalWithdraw) }}
                    </div>
                    <div class="text-right">
                       Jumlah Transaksi Tarik Bonus:
                    </div>
                    <div>
                       {{$jumlahBonusWd}}
                    </div>
                    <div class="text-right">
                       Total Transaks Tarik Bonus:
                    </div>
                    <div>
                        {{$totalBonusWd}}
                    </div>
                    <div class="text-right">
                       Player Withdraw:
                    </div>
                    <div>
                       {{$playerWd}}
                    </div>

                 
                    <div class="text-right">
                       Total new Deposit:
                    </div>
                    <div>
                       {{ number_format($totalFirstDepo) }}
                    </div>

                </x-filament::grid>
               
            </div>
        </x-filament::grid>
    </x-filament::card>
</x-filament::widget>
