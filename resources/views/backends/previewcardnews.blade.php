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

        {{-- Kartu direplikasi dari section kabar di landing (latar #fdf0ee)
             supaya thumbnail, judul, dan deskripsi terlihat persis seperti
             nantinya — termasuk ruang minimum judul/deskripsi. --}}
        <div class="bg-[#fdf0ee] px-[4%] py-[5%]">
            <div class="grid gap-[4.5%] gap-y-8 sm:grid-cols-2">
                @foreach ($cards as $card)
                    <div class="block">
                        <div class="overflow-hidden">
                            <img src="{{ asset('storage/files/photos/' . $data->img) }}" alt="{{ $card['title'] }}"
                                 class="aspect-[476/268] w-full object-cover">
                        </div>

                        <p class="mt-4 font-display text-[14px] font-medium text-ember">
                            {{ $card['date'] }}
                        </p>

                        <h3 class="mt-1 min-h-[26px] font-display text-[18px] font-semibold leading-[26px] text-neutral-900">
                            {{ $card['title'] }}
                        </h3>

                        <div class="mt-2 min-h-[88px] max-w-[60ch] font-display text-[14px] font-normal leading-[22px] text-neutral-500">
                            {{ $card['description'] }}
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
