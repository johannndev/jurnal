// console.log('halo')

    document.addEventListener('DOMContentLoaded', function () {
    const bankSelect = document.querySelector('#bank_id_select');
    const nominalInput = document.querySelector('#nominal');
    const saldoAkhirInput = document.querySelector('#data\\.saldo_akhir');
    const saldoAwalInput = document.querySelector('#data\\.saldo_awal'); // escaping dot (.)
    var type = document.querySelector('#data\\.type');

    bankSelect.addEventListener('change', async function(event) {
        const bankId = event.target.value;
       // event.target adalah elemen yang memicu event

        if (bankId) {
            const response = await fetch(`/get-bank-saldo/${bankId}`);
            const data = await response.json();
            saldoAwalInput.value = data.saldo;
            saldoAkhirInput.value = data.saldo;
            // updateSaldoAkhir();
            

            // console.log(data.saldo);
        }
    });

     nominalInput.addEventListener('keyup', function (event) {
        const nominalVal = event.target.value;

        console.log(type.value);

        var updateSaldo = 0
        
        if(type.value === "dp"){
            updateSaldo = parseInt(saldoAwalInput.value)+parseInt(nominalVal);
        }else{
            updateSaldo = parseInt(saldoAwalInput.value)-parseInt(nominalVal);
        }
      

         saldoAkhirInput.value = updateSaldo

        
     });

    function showMe(e) {
        
        console.log(e.value);
    }

    function updateSaldoAkhir() {
        const saldoAwal = parseFloat(saldoAwalInput.value) || 0;
        // const nominal = parseFloat(nominalInput.value) || 0;
        saldoAkhirInput.value = saldoAwal;
    }
});

