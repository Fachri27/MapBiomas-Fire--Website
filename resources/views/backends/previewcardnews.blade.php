@extends('layouts.dashboardLayouts')

@section('content')
    @include('partials.backendHeader')
    @include('partials.backendNav')

    <div class="max-w-6xl mx-auto px-6 md:px-8 sm:py-16 py-8 min-h-screen">
        <div class="flex items-baseline justify-between mb-8">
            <h1 class="text-xl font-bold text-gray-800">Preview Card — {{ $data->titleID }}</h1>
            <span class="text-xs font-mono uppercase tracking-wider text-gray-500">
                {{ $data->status == 1 ? 'Published' : 'Unpublish' }}
            </span>
        </div>

        {{-- Kartu direplikasi dari section kabar di landing (bg #f26b61) supaya
             thumbnail, judul, dan deskripsi terlihat persis seperti nantinya. --}}
        <div class="bg-[#f26b61] px-[4%] py-[5%]">
            <div class="grid gap-[4.5%] gap-y-8 sm:grid-cols-2">
                @foreach ($cards as $card)
                    <div class="flex h-full flex-col">
                        <div class="overflow-hidden">
                            <img src="{{ asset('storage/files/photos/' . $data->img) }}" alt="{{ $card['title'] }}"
                                 class="aspect-[476/268] w-full object-cover">
                        </div>
                        <div class="flex flex-1 flex-col bg-ember-soft px-[7%] py-5">
                            <h3 class="font-display text-[clamp(0.95rem,1.15vw,1.3rem)] font-normal leading-snug text-white">
                                {{ $card['title'] }}
                            </h3>
                            <div class="mt-2 max-w-[60ch] font-display text-[clamp(0.75rem,0.85vw,0.95rem)] font-light leading-relaxed text-white/85">
                                {{ $card['description'] }}
                            </div>
                            <p class="mt-auto pt-3 font-mono text-[10px] uppercase tracking-[0.16em] text-white/75">{{ $card['date'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <p class="mt-4 text-xs text-gray-500">
            Kiri: Indonesia · Kanan: English — ukuran &amp; warna sama dengan kartu di landing.
        </p>

        <div class="mt-8 flex gap-3">
            <a href="{{ url('/cms/previewnews/' . $data->id) }}" target="_blank"
               class="inline-flex items-center rounded border border-newgray-300 px-4 py-2 text-sm font-semibold text-newgray-700 hover:bg-gray-100">
                Preview halaman detail &rarr;
            </a>
            <a href="{{ url('/cms/listnews') }}"
               class="inline-flex items-center rounded bg-newblue px-4 py-2 text-sm font-semibold text-white hover:bg-newblue/90">
                Kembali ke list news
            </a>
        </div>
    </div>
@endsection
