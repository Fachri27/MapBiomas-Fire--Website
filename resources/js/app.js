import '../../vendor/masmerise/livewire-toaster/resources/js'; // 👈

import intersect from '@alpinejs/intersect';

// Livewire v3 membawa Alpine sendiri; plugin cukup didaftarkan lewat
// event alpine:init agar tidak ada dua instans Alpine di halaman CMS.
document.addEventListener('alpine:init', () => {
    window.Alpine.plugin(intersect);
});
