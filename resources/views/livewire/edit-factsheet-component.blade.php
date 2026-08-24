<form wire:submit="storeAksi" x-data="{lang:'english'}" class="max-w-4xl mx-auto px-6 md:px-8 min-h-screen">

    <div class=" border-b border-gray-300 dark:border-opacity-20 py-16 mb-6">
        <div class="max-w-4xl mx-auto px-6  flex justify-between ">
            <h1 class="sm:text-4xl text-xl text-newgray-900 dark:text-newgray-300 font-semibold ">edit factsheet</h1>
            <div class="z-30">
                <button type="submit" wire:loading.remove wire:target='storeAksi' id="btnStore" class="inline-flex sm:px-16 px-8 sm:py-2 py-1 rounded dark:hover:bg-newgray-900 dark:hover:border-gray-200 dark:hover:text-gray-200 hover:bg-white hover:text-newgray-900 border hover:border-newgray-900 bg-newgray-900 dark:bg-gray-100 text-newgray-100 dark:text-newgray-900">
                    Save
                </button>
                <button type="button" disabled wire:loading wire:target='storeAksi' id="btnStore" class="inline-flex sm:px-16 px-8 sm:py-2 py-1 rounded dark:hover:bg-newgray-900 dark:hover:border-gray-200 dark:hover:text-gray-200 hover:bg-white hover:text-newgray-900 border hover:border-newgray-900 bg-newgray-900 dark:bg-gray-100 text-newgray-100 dark:text-newgray-900">
                    <svg class="animate-spin mx-auto h-6 w-6 " xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <h1 class="text-xl font-semibold  text-newbg-newgray-900 dark:text-gray-300 mb-4">Category</h1>
    <label class="w-full mb-6 block" >
        <select wire:model='category' class=" bg-gray-100 dark:bg-newgray-700 text-newgray-700 dark:text-gray-300 rounded w-full border  py-2 px-4 focus:outline-none border-gray-300 dark:border-opacity-20">
            <option value="monthly">Monthly</option>
            <option value="annual">Annual</option>
        </select>
    </label>

    <div  class="overflow-x-auto scrollbar-hide whitespace-nowrap   subpixel-antialiased flex mb-6 justify-end">
        {{-- tabs english --}}
        <div @click="lang='english'" class="hover:bg-gray-200 dark:hover:bg-newgray-700 py-2 px-2 rounded  cursor-pointer"
        :class="{ 'border-b-2 border-newgray-900 dark:border-gray-300' : lang === 'english' }"
        >
            <a  class=" px-0.5  text-newgray-900 dark:text-gray-400 text-sm   hover:text-newgray-900 dark:hover:text-gray-300 "
            :class="{ 'font-black' : lang === 'english' }"
            >English</a>
        </div>
        {{-- lang indonesia --}}
        <div @click="lang='indonesia'" class="hover:bg-gray-200 dark:hover:bg-newgray-700 py-2 px-2 rounded  cursor-pointer"
        :class="{ 'border-b-2 border-newgray-900 dark:border-gray-300' : lang === 'indonesia' }"
        >
            <a  class=" px-0.5  text-newgray-900 dark:text-gray-400 text-sm   hover:text-newgray-900 dark:hover:text-gray-300 "
            :class="{ 'font-bold' : lang === 'indonesia' }"
            >Indonesia</a>
        </div>

    </div>

    <div x-show="lang==='english'" x-cloak style="display: none !important">
        <div class="w-full border border-gray-300 dark:border-opacity-20 rounded px-6 py-6 mb-6">
            <label class="block mb-4">
                <span class="block text-sm font-semibold text-newgray-900 dark:text-gray-300 mb-2">Title</span>
                <input type="text" wire:model='titleEN' placeholder="Factsheet title (english)" class="bg-gray-100 dark:bg-newgray-700 text-newgray-700 dark:text-gray-300 rounded w-full border border-gray-300 dark:border-opacity-20 py-2 px-4 focus:outline-none">
            </label>
            <label class="block">
                <span class="block text-sm font-semibold text-newgray-900 dark:text-gray-300 mb-2">Description</span>
                <textarea rows="4" wire:model='descriptionEN' placeholder="Short description (english)" class="bg-gray-100 dark:bg-newgray-700 text-newgray-700 dark:text-gray-300 rounded w-full border border-gray-300 dark:border-opacity-20 py-2 px-4 focus:outline-none"></textarea>
            </label>
        </div>
    </div>

    <div x-show="lang==='indonesia'" x-cloak style="display: none !important">
        <div class="w-full border border-gray-300 dark:border-opacity-20 rounded px-6 py-6 mb-6">
            <label class="block mb-4">
                <span class="block text-sm font-semibold text-newgray-900 dark:text-gray-300 mb-2">Judul</span>
                <input type="text" wire:model='titleID' placeholder="Judul factsheet (indonesia)" class="bg-gray-100 dark:bg-newgray-700 text-newgray-700 dark:text-gray-300 rounded w-full border border-gray-300 dark:border-opacity-20 py-2 px-4 focus:outline-none">
            </label>
            <label class="block">
                <span class="block text-sm font-semibold text-newgray-900 dark:text-gray-300 mb-2">Deskripsi</span>
                <textarea rows="4" wire:model='descriptionID' placeholder="Deskripsi singkat (indonesia)" class="bg-gray-100 dark:bg-newgray-700 text-newgray-700 dark:text-gray-300 rounded w-full border border-gray-300 dark:border-opacity-20 py-2 px-4 focus:outline-none"></textarea>
            </label>
        </div>
    </div>

    <label class="w-full mb-6 block">
        <span class="block text-sm font-semibold text-newgray-900 dark:text-gray-300 mb-2">Link <span class="font-normal text-gray-400">(optional if a PDF is uploaded)</span></span>
        <input type="url" wire:model='link' placeholder="https://... (pdf / halaman unduh)" class="bg-gray-100 dark:bg-newgray-700 text-newgray-700 dark:text-gray-300 rounded w-full border border-gray-300 dark:border-opacity-20 py-2 px-4 focus:outline-none">
    </label>

    <div class="w-full border border-gray-300 dark:border-opacity-20 rounded px-6 py-6 mb-6">
        <span class="block text-sm font-semibold text-newgray-900 dark:text-gray-300 mb-2">PDF file</span>
        <label class="cursor-pointer block">
            <input type="file" class="hidden" wire:model.live='pdf' accept="application/pdf" />
            <div class="border border-dashed border-gray-300 dark:border-opacity-20 rounded py-6 px-4 text-center">
                @if ($pdf)
                    <p class="text-sm text-newgray-900 dark:text-gray-300 break-all">{{ $pdf->getClientOriginalName() }}</p>
                @elseif (!empty($updf))
                    <p class="text-sm text-newgray-900 dark:text-gray-300 break-all">{{ $updf }}</p>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                @endif
                <p wire:loading.remove wire:target="pdf" class="text-xs text-center text-gray-400 mt-2">Click to upload PDF (max 50MB)</p>
                <p wire:loading wire:target="pdf" class="text-xs text-center text-gray-400 mt-2">Uploading...</p>
            </div>
        </label>
    </div>
</form>
