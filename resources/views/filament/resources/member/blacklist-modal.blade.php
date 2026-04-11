<x-filament::modal id="blacklist-modal" width="md" :close-by-clicking-away="false">
    @if($this->blacklistId)
        @php
            $blacklist = \App\Models\BankBlacklist::with('bankname')->find($this->blacklistId);
        @endphp

        @if($blacklist)
            <div class="text-center py-4">
                <div class="flex justify-center mb-3">
                    <div class="p-2 rounded-full bg-danger-50 dark:bg-danger-500/10">
                        <x-heroicon-o-shield-exclamation class="h-8 w-8 text-danger-600 dark:text-danger-500" />
                    </div>
                </div>
                
                <h2 class="text-xl font-black tracking-tight text-gray-950 dark:text-white uppercase">
                    Security Match Found
                </h2>
                
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-200 font-medium px-4">
                    Nomor rekening ini terdeteksi dalam database terlarang (Blacklist).
                </p>
            </div>

            <dl class="divide-y divide-gray-400 dark:divide-gray-900 border-y border-gray-400 dark:border-gray-900">
                <div class="flex justify-between py-4 px-2">
                    <dt class="text-sm font-bold text-gray-600 dark:text-gray-400 uppercase tracking-tight">Nama Pemilik</dt>
                    <dd class="text-sm font-black text-gray-950 dark:text-white uppercase">{{ $blacklist->nama_rekening }}</dd>
                </div>
                <div class="flex justify-between py-4 px-2">
                    <dt class="text-sm font-bold text-gray-600 dark:text-gray-400 uppercase tracking-tight">Nama Bank</dt>
                    <dd class="text-sm font-bold text-gray-900 dark:text-gray-100 uppercase">{{ $blacklist->bankname?->bank_nama ?? '-' }}</dd>
                </div>
                <div class="flex justify-between py-4 px-2">
                    <dt class="text-sm font-bold text-gray-600 dark:text-gray-400 uppercase tracking-tight">Nomor Rekening</dt>
                    <dd class="text-sm font-mono font-black text-danger-600 dark:text-danger-400 tracking-widest">{{ $blacklist->nomor_rekening }}</dd>
                </div>
                @if($blacklist->keterangan)
                <div class="py-4 px-2">
                    <dt class="text-[10px] font-black text-gray-500 dark:text-gray-400 uppercase tracking-[0.2em] mb-2 text-center">Security Notes</dt>
                    <dd class="text-sm italic font-medium text-gray-700 dark:text-gray-200 leading-relaxed text-center px-4">
                        "{{ $blacklist->keterangan }}"
                    </dd>
                </div>
                @endif
            </dl>
        @else
            <div class="p-8 text-center text-gray-500 italic">Data keamanan tidak ditemukan.</div>
        @endif
    @else
        <div class="p-12 text-center">
            <x-filament::loading-indicator class="mx-auto h-8 w-8 text-primary-500 mb-4" />
            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest animate-pulse">Running Verifications...</p>
        </div>
    @endif

    <x-slot name="footerActions">
        <x-filament::button 
            color="gray" 
            x-on:click="close"
            class="w-full"
            size="lg"
            outlined
        >
            Dismiss & Return
        </x-filament::button>
    </x-slot>
</x-filament::modal>