@extends('layouts.indexLayout')

@section('meta')
    @include('partials.indexMeta')
@endsection

@section('content')

    {{-- @include('partials.langswitcher') --}}
    @include('partials.navMobile')
    <div class="bg-white sticky top-0 z-50">
        @include('partials.navPC')
    </div>
    <div class="border-b-[0.4px] border-red-500"></div>

    {{-- heroPage --}}

    <div class="">
        <img src="{{ asset('images/hero-fire.jpg') }}" alt="Mapbiomas Indonesia - Fire" class=" z-10 sm:h-[45vh] h-[30vh] w-full object-[center_75%] object-cover">
    </div>

    <div class="sm:px-0 px-4">
        <div class="max-w-3xl mx-auto bg-white relative mt-3 z-20 rounded sm:px-6 px-4 py-10 border-b border-red-600 min-h-[40vh]">
            <a class="text-xl font-semibold ">{{ __('Factsheet') }}</a>

            <div class="divide-y divide-gray-200 mt-4">
                @forelse ($sheets as $sheet)
                    {{-- PDF hasil unggahan CMS menang atas kolom link.
                         Placeholder '#' berarti belum ada tautan (jangan render tombol mati).
                         URL sampul dibuat relatif agar tetap satu origin walau
                         situs dibuka lewat host/IP yang berbeda dari APP_URL. --}}
                    @php
                        $href = $sheet->file ? asset('storage/files/factsheet/'.$sheet->file) : $sheet->link;
                        if ($href === '#') $href = null;
                        // Sampul selalu lewat proxy satu-origin: berkas lokal
                        // dilayani langsung, tautan luar di-proxy agar lolos CORS.
                        $hasSource = $sheet->file
                            || (is_string($sheet->link) && str_starts_with($sheet->link, 'http'));
                        $thumb = $hasSource
                            ? route('factsheet.file', ['id' => $sheet->id, 'lang' => app()->getLocale()], false)
                            : null;
                    @endphp
                    <div class="py-6 first:pt-0 last:pb-0 flex gap-4 sm:gap-6">
                        {{-- Sampul: halaman pertama PDF yang diunggah (dirender
                             di peramban). Entri tautan luar dapat placeholder. --}}
                        @if ($thumb)
                            {{-- self-stretch + object-cover: tinggi sampul selalu
                                 mengikuti kolom teks, sisi ter-crop proporsional. --}}
                            <div class="w-36 sm:w-60 shrink-0 self-stretch overflow-hidden rounded border border-gray-200 bg-gray-100 min-h-[140px]">
                                <canvas data-pdf-thumb="{{ $thumb }}"
                                        class="block h-full w-full object-cover"></canvas>
                            </div>
                        @else
                            <div class="flex w-36 sm:w-60 shrink-0 self-stretch min-h-[140px] items-center justify-center rounded border border-gray-200 bg-gray-100">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-8 w-8 text-gray-400">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                </svg>
                            </div>
                        @endif
                        <div class="flex min-w-0 flex-1 flex-col gap-2">
                            <span class="inline-flex w-fit items-center rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-medium text-red-600">
                                {{ ($sheet->category ?? '') === 'monthly' ? __('Monthly') : __('Annual') }}
                            </span>
                            @if ($sheet->title)
                                <p class="text-lg font-semibold">{{ $sheet->title }}</p>
                            @endif
                            @if ($sheet->description)
                                <p class="leading-relaxed sm:text-base text-sm">{{ $sheet->description }}</p>
                            @endif
                            {{-- Entri warisan bisa tak punya berkas maupun tautan; tanpa
                                 penjagaan ini tombolnya jadi <a href=""> yang memuat ulang halaman. --}}
                            @if ($href)
                                <a href="{{ $href }}" target="_blank" rel="noopener"
                                   class="mt-1 inline-flex w-fit items-center gap-2 rounded border border-red-600 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-600 hover:text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    {{ __('Download') }}
                                </a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-sm text-gray-500">{{ __('Belum ada factsheet terbit.') }}</p>
                @endforelse
            </div>
        </div>
    </div>


    @include('partials.footer')
@endsection
