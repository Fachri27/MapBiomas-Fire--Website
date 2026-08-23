{{-- lang switch --}}
@php
    /* Rute tanpa nama (mis. pratinjau berita dari CMS) tidak dapat dibangun
       ulang via route(); arahkan saja ke beranda bahasa tujuan. */
    $langUrl = fn (string $lang): string => Route::currentRouteName()
        ? route(Route::currentRouteName(), [$lang, $data->id, $data->slug])
        : url($lang);
@endphp
<div class="max-w-6xl mx-auto  sm:block hidden">
    <div class="flex justify-between px-3">
        <a></a>
        <div class="text-red-400 px-12 py-1 bg-red-600 text-sm rounded-b flex space-x-4">
            <a href="{{ $langUrl('en') }}" class=" @if(App::getLocale() == 'en') text-white @endif  ">English</a>
            <a href="{{ $langUrl('id') }}" class="@if(App::getLocale() == 'id') text-white @endif">Indonesia</a>
        </div>
    </div>
</div>
