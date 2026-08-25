<!-- {{-- nav mobile --}} -->
@php
    /* Titik laci berhenti tampil. Baku 'lg' karena navPC baru muncul di lg —
       kalau laci berhenti di sm, layar 640-1024px tidak punya navigasi sama
       sekali. Kelasnya ditulis utuh agar terbaca pemindai Tailwind. */
    $mobileOnly = ($hideFrom ?? 'lg') === 'sm' ? 'sm:hidden' : 'lg:hidden';

@endphp
<header class="bg-auriga-biru sticky top-0 z-30">
    <div x-data="{ open: false }" class="px-4 py-3 bg-white z-10 {{ $mobileOnly }} block">
        <div class="flex justify-between items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600 " viewBox="0 0 20 20" fill="currentColor" @click="open = true">
                <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" />
              </svg>

              <img src="{{ asset('images/mapbiomas-fire.png') }}" alt="MapBiomas Indonesia Fire" class="h-9 w-auto">
              <div class="flex gap-2 items-center z-50 text-sm" role="group" aria-label="Pilihan bahasa">
                <a href="{{ $langUrl('en') }}" hreflang="en"
                   @if(App::getLocale() == 'en') aria-current="true" @endif
                   class="cursor-pointer @if(App::getLocale() == 'en') text-red-600 font-bold @else text-gray-500 @endif">EN</a>
                <div class="border-l border-gray-300 h-4"></div>
                <a href="{{ $langUrl('id') }}" hreflang="id"
                   @if(App::getLocale() == 'id') aria-current="true" @endif
                   class="cursor-pointer @if(App::getLocale() == 'id') text-red-600 font-bold @else text-gray-500 @endif">ID</a>
            </div>
        </div>


        <div class="fixed w-3/4 h-screen z-50 bg-red-900 inset-0 overflow-y-auto " x-show="open"
        x-transition:enter="transition-all transform ease-out"
        x-transition:enter-start="-translate-x-1/2 opacity-0"
        x-transition:enter-end="translate-x-0 opacity-100"
        x-transition:leave="transition-all transform ease-in"
        x-transition:leave-start="translate-x-0 opacity-100"
        x-transition:leave-end="-translate-x-1/2 opacity-0"
        @click.outside="open = false"
        x-cloak style="display: none !important">
            <button class="absolute px-4 py-4 focus:outline-none text-white" @click="open = false">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 " fill="white" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
            </button>

            <div class="mt-16 space-y-3">
                <div class="px-4">
                    <a href="{{ route('index', app()->getLocale()) }}" class="mb-4 px-4 inline-block leading-5 text-white font-semibold">home</a>
                    <p class="border-b border-gray-300"></p>
                </div>
                @foreach ($nav as $item)
                    @if (isset($item['children']))
                        <div class="px-4" x-data="{ sub: false }">
                            <button type="button" class="flex items-center px-4 mb-2 focus:outline-none"
                                    x-on:click="sub = !sub" :aria-expanded="sub.toString()">
                                <span class="text-base leading-5 text-white font-semibold">{{ $item['label'] }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" :class="{'rotate-180': sub, 'rotate-0': !sub}" class="w-6 text-gray-300 transition-transform duration-200" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div class="bg-white px-4 py-3 mb-4 flex flex-col space-y-2 rounded" x-show="sub" x-cloak style="display: none !important;">
                                @foreach ($item['children'] as $child)
                                    <a href="{{ $child['href'] }}" class="text-sm mr-6">{{ $child['label'] }}</a>
                                @endforeach
                            </div>
                            <p class="border-b border-gray-300"></p>
                        </div>
                    @else
                        <div class="px-4">
                            <a href="{{ $item['href'] }}" class="mb-4 px-4 inline-block leading-5 text-white font-semibold">{{ $item['label'] }}</a>
                            <p class="border-b border-gray-300"></p>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 -py-2 sm:block hidden">
        <div class="flex justify-between px-3">
            <a></a>
        </div>
    </div>
</header>
