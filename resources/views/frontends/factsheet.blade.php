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
            <a class="text-xl font-semibold ">Fact Sheet</a>

            @include('partials.categoryTabs', [
                'route' => 'factsheet',
                'active' => $category ?? 'monthly',
                'label' => __('Fact sheet category'),
            ])

            {{-- Hanya kalimat unduhnya. Judul tetap disimpan dan tampil di CMS
                 sebagai penanda antar entri, tapi tidak dimunculkan di sini. --}}
            <div class="divide-y divide-gray-200">
                @forelse ($sheets as $sheet)
                    <div class="py-6 first:pt-0 last:pb-0">
                        <p class="leading-relaxed sm:text-base text-sm">
                            {{ $sheet->description }}
                            [<a href="{{ $sheet->link }}" target="_blank" rel="noopener"
                                class="text-red-600 underline">{{ __('Link') }}</a>].
                        </p>
                    </div>
                @empty
                    <p class="py-6 text-sm text-gray-500">{{ __('Belum ada fact sheet terbit.') }}</p>
                @endforelse
            </div>
        </div>
    </div>


    @include('partials.footer')
@endsection
