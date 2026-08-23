@extends('layouts.indexLayout')

@section('meta')
    @include('partials.detailMeta')
@endsection

@section('content')

    {{-- @include('partials.langswitcherDetail') --}}
    @include('partials.navMobile')
    <div class="bg-white sticky top-0 z-50">
        @include('partials.detailNavPc')
    </div>
    <div class="border-b-[0.4px] border-landy"></div>

    {{-- heroPage --}}

    @if (!empty($preview))
        <div class="bg-landy py-2 text-center font-display text-sm font-semibold text-white">
            Pratinjau — berita ini belum dipublikasi
        </div>
    @endif

    <div class="">
        <img src="{{ asset('images/hero-fire.jpg') }}" alt="Mapbiomas Indonesia - Fire" class=" z-10 sm:h-[45vh] h-[30vh] w-full object-[center_75%] object-cover">
    </div>

    <div class="sm:px-0 px-4">
        <div class="max-w-3xl mx-auto bg-white relative  -mt-20 z-20 rounded sm:px-6 px-4 sm:py-12 py-4 border-b border-landy min-h-[40vh]">
            <a class="text-landy font-light">{{$data->category}}</a>
            <h1 class="text-xl font-semibold text-landy mb-4">{{$data->title}}</h1>
            <p class="text-landy mb-6 font-light">{{ strip_tags($data->description) }}</p>
            <img src="{{ asset('storage/files/photos/'.$data->img) }}" alt="{{ $data->title }}" class="w-full h-full ">
            <div class="prose max-w-none mt-4 sm:text-base text-sm leading-relaxed font-light">
                {!! optional($data)->content !!}
            </div>



        </div>
    </div>


    @include('partials.footer')
@endsection
