{{-- Bilah kemajuan unggahan Livewire.

     Butuh induk ber-x-data yang menyimpan `isUploading` dan `progress`, diisi
     dari event livewire-upload-*. Event itu menggelembung dari <input type=file>,
     jadi induknya cukup elemen mana pun yang membungkus input tersebut. --}}
<div x-show="isUploading" x-cloak class="mt-2">
    <div class="h-1.5 w-full overflow-hidden rounded bg-gray-200 dark:bg-newgray-700">
        <div class="h-full rounded bg-red-600 transition-all duration-150 ease-out"
             :style="`width: ${progress}%`"
             role="progressbar" aria-valuemin="0" aria-valuemax="100"
             :aria-valuenow="progress"></div>
    </div>
    <p class="mt-1 text-center text-xs text-newgray-900 dark:text-gray-300"
       x-text="`Uploading ${progress}%`"></p>
</div>
