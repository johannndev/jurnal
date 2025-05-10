<div x-data="{ time: '', date: '' }"
    x-init="setInterval(() => {
         const now = new Date();
         time = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0') + ':' + now.getSeconds().toString().padStart(2, '0');
         
         // Menampilkan nama hari
         const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
         dayName = dayNames[now.getDay()];

         // Format tanggal dengan menambahkan nol di depan bulan dan tanggal yang kurang dari 10
         const day = now.getDate().toString().padStart(2, '0');
         const month = (now.getMonth() + 1).toString().padStart(2, '0'); // Bulan dimulai dari 0
         const year = now.getFullYear();

         date = `${dayName}, ${day}-${month}-${year}`;
     }, 1000)"
     class="font-bold text-primary-500  ">

     <div class="">
        <span class="mr-4" x-text="date"></span>
        <span  x-text="time"></span>
      
        
     </div>
   
</div>