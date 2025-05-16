import './bootstrap';


// resources/js/app.js

document.addEventListener('DOMContentLoaded', function () {
    // Event listener saat memilih bank
    // document.getElementById('bank_id').addEventListener('change', function () {
    //     const bankId = this.value;

    //     // Mengambil saldo dari backend sesuai dengan bank_id yang dipilih
    //     fetch(`/api/get-saldo/${bankId}`)
    //         .then(response => response.json())
    //         .then(data => {
    //             const saldoAwal = data.saldo ?? 0;
    //             // Set saldo_awal dan saldo_akhir ke field yang sesuai
    //             document.getElementById('saldo_awal_display').value = saldoAwal;
    //             document.getElementById('saldo_akhir_display').value = saldoAwal;
                
    //             // Update hidden field saldo_awal untuk reactive form
    //             document.querySelector('input[name="saldo_awal"]').value = saldoAwal;
    //             document.querySelector('input[name="saldo_akhir"]').value = saldoAwal;
    //         });
    // });

    // Event listener saat nominal diubah
    document.getElementById('nominal').addEventListener('input', function () {
        const nominal = parseFloat(this.value) || 0;
        const saldoAwal = parseFloat(document.getElementById('saldo_awal_display').value) || 0;

        console.log('nominal');
        

        // Hitung saldo_akhir
        const saldoAkhir = saldoAwal + nominal;

        // Set saldo_akhir ke field yang sesuai
        document.getElementById('saldo_akhir_display').value = saldoAkhir;

        // Update hidden field saldo_akhir untuk reactive form
        document.querySelector('input[name="saldo_akhir"]').value = saldoAkhir;
    });
});
