{{-- Navbar layar besar. Satu berkas untuk semua halaman publik; $nav dan
     $langUrl datang dari View::composer di AppServiceProvider.

     $overlay = true dipakai landing: header mengambang di atas foto hero dan
     baru memakai latar putih setelah halaman digulir. Halaman dalam memakai
     varian padat, dibungkus `sticky` oleh halamannya masing-masing. --}}
@php
    $overlay = $overlay ?? false;
    // Landing mewariskan dua kelas ini lewat @include; halaman lain memakai
    // lebar bakunya.
    $shell = $shell ?? 'mx-auto w-[90%] max-w-[1520px]';
    $frame = $frame ?? 'mx-auto w-full max-w-6xl';
@endphp

<header
    @if ($overlay)
        x-data="{ scrolled: false }"
        x-on:scroll.window="scrolled = window.scrollY > 40"
        {{-- Kelas ditulis utuh di kedua cabang supaya terbaca pemindai Tailwind. --}}
        class="fixed inset-x-0 top-0 z-50 hidden border-b border-ember transition-colors duration-300 lg:block"
        :class="scrolled ? 'bg-white/[0.92] backdrop-blur' : 'bg-transparent'"
    @else
        class="hidden border-b border-ember bg-white lg:block"
    @endif
>
    <div class="{{ $frame }}">

        {{-- Pengalih bahasa: tab yang menggantung dari tepi atas, sejajar tepi
             kanan konten. --}}
        <div class="{{ $shell }} flex justify-end">
            <div class="flex items-center gap-5 rounded-b bg-ember px-9 py-1 font-display text-sm leading-6"
                 role="group" aria-label="{{ __('Pilihan bahasa') }}">
                <a href="{{ $langUrl('en') }}" hreflang="en"
                   @if (app()->getLocale() === 'en') aria-current="true" @endif
                   class="@if (app()->getLocale() === 'en') font-semibold text-white @else text-white/55 transition-colors hover:text-white @endif">English</a>
                <a href="{{ $langUrl('id') }}" hreflang="id"
                   @if (app()->getLocale() === 'id') aria-current="true" @endif
                   class="@if (app()->getLocale() === 'id') font-semibold text-white @else text-white/55 transition-colors hover:text-white @endif">Indonesia</a>
            </div>
        </div>

        <div class="{{ $shell }} flex items-center justify-between gap-6 py-2.5 lg:py-3.5">
            <a href="{{ route('index', app()->getLocale()) }}" class="relative block shrink-0">
                @if ($overlay)
                    {{-- Dua varian logo ditumpuk lalu disilangkan mengikuti header:
                         saat transparan dipakai versi teks putih agar "MAP" dan
                         "INDONESIA | FIRE" tetap terbaca di atas foto. --}}
                    <img src="{{ asset('images/mapbiomas-fire.png') }}"
                         alt="MapBiomas Indonesia Fire, ke beranda"
                         class="h-8 w-auto transition-opacity duration-300 lg:h-11"
                         :class="scrolled ? 'opacity-100' : 'opacity-0'">
                    <img src="{{ asset('images/mapbiomas-fire-on-dark.png') }}" alt="" aria-hidden="true"
                         class="absolute inset-0 h-8 w-auto transition-opacity duration-300 lg:h-11"
                         :class="scrolled ? 'opacity-0' : 'opacity-100'">
                @else
                    <img src="{{ asset('images/mapbiomas-fire.png') }}"
                         alt="MapBiomas Indonesia Fire, ke beranda"
                         class="h-8 w-auto lg:h-11">
                @endif
            </a>

            <nav class="flex items-center gap-4 xl:gap-7" aria-label="{{ __('Navigasi utama') }}">
                @foreach ($nav as $item)
                    @if (isset($item['children']))
                        <div class="relative" x-data="{ sub: false }"
                             x-on:click.outside="sub = false"
                             x-on:keydown.escape.window="sub = false">
                            <button type="button" aria-haspopup="true"
                                    x-on:click="sub = !sub"
                                    :aria-expanded="sub.toString()"
                                    class="flex cursor-pointer items-center gap-1.5 font-display text-[15px] font-semibold text-ember transition-colors hover:text-ember-soft">
                                {{ $item['label'] }}
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 12 8" fill="none" aria-hidden="true"
                                     class="h-2 w-3 transition-transform duration-200"
                                     :class="sub ? 'rotate-180' : ''">
                                    <path d="M1 1.5 6 6.5 11 1.5" stroke="currentColor" stroke-width="1.6"/>
                                </svg>
                            </button>
                            <div x-show="sub" x-cloak
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 {{-- tanpa padding: isian hover item menempel ke garis tepi panel --}}
                                 class="absolute left-0 top-full z-50 mt-3 w-56 border border-ember bg-white">
                                @foreach ($item['children'] as $child)
                                    <a href="{{ $child['href'] }}" x-on:click="sub = false"
                                       class="block px-5 py-2.5 font-display text-sm font-semibold text-ember transition-colors hover:bg-ember hover:text-white">
                                        {{ $child['label'] }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ $item['href'] }}"
                           class="font-display text-[15px] font-semibold text-ember transition-colors hover:text-ember-soft">
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </nav>
        </div>
    </div>
</header>
