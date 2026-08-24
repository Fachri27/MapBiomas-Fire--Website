@extends('layouts.indexLayout')

@section('meta')
    @include('partials.detailMeta')
@endsection

@section('content')

    {{-- @include('partials.langswitcherDetail') --}}
    @include('partials.navMobile')
    <div class="bg-white sticky top-0 z-50">
        @include('partials.navPC')
    </div>
    <div class="border-b-[0.4px] border-landy"></div>

    {{-- heroPage --}}

    @if (!empty($preview))
        <div class="bg-landy py-2 text-center font-display text-sm font-semibold text-white">
            Pratinjau — berita ini belum dipublikasi
            <span class="ml-4 inline-flex gap-3 text-white/80">
                <a href="?lang=id" class="@if(app()->getLocale() == 'id') underline font-bold @endif hover:text-white">ID</a>
                <span aria-hidden="true">|</span>
                <a href="?lang=en" class="@if(app()->getLocale() == 'en') underline font-bold @endif hover:text-white">EN</a>
            </span>
        </div>
    @endif

    <div class="">
        <img src="{{ asset('images/hero-fire.jpg') }}" alt="Mapbiomas Indonesia - Fire" class=" z-10 sm:h-[45vh] h-[30vh] w-full object-[center_75%] object-cover">
    </div>

    <div class="sm:px-0 px-4">
        {{-- Jarak hero→konten 12px (mt-3), sama dengan semua halaman hero
             lainnya; kartu tidak menimpa gambar hero. --}}
        <div class="max-w-[820px] mx-auto bg-white relative mt-3 z-20 rounded px-[5vw] py-10 border-b border-landy min-h-[40vh]">
            {{-- Kategori, judul, dan deskripsi memakai Poppins seperti isi artikel
                 di bawahnya; deskripsi disamakan persis 16px/1.85 agar menyambung
                 dengan badan teks. Judul tetap lebih besar sebagai judul. --}}
            <a class="font-display text-landy font-light">{{$data->category}}</a>
            <h1 class="font-display text-xl font-semibold text-landy mb-4">{{$data->title}}</h1>
            <p class="font-display text-[16px] leading-[1.85] text-[#3a3428] mb-6">{{ strip_tags($data->description) }}</p>
            <img src="{{ asset('storage/files/photos/'.$data->img) }}" alt="{{ $data->title }}" class="w-full h-full ">
            {{-- Warna isi artikel dikunci #3a3428 via variabel prose, karena
                 plugin typography mewarnai elemen (p/li/strong/dst.) lewat
                 --tw-prose-* dan mengabaikan text-* pada pembungkusnya. --}}
            <div class="prose max-w-none mt-4 font-display text-[16px] leading-[1.85] prose-p:text-[16px] prose-p:leading-[1.85] prose-li:text-[16px] prose-li:leading-[1.85] [--tw-prose-body:#3a3428] [--tw-prose-headings:#3a3428] [--tw-prose-bold:#3a3428] [--tw-prose-links:#3a3428] [--tw-prose-counters:#3a3428] [--tw-prose-bullets:#3a3428] [--tw-prose-hr:#3a3428] [--tw-prose-quotes:#3a3428] [--tw-prose-quote-borders:#3a3428] [--tw-prose-captions:#3a3428]">
                {!! optional($data)->content !!}
            </div>



        </div>
    </div>


    @include('partials.footer')
@endsection
