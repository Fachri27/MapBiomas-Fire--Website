<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         * Menu navigasi hidup di satu tempat. Sebelumnya daftar yang sama
         * ditulis ulang di navPC, detailNavPc, navMobile, dan inline di
         * landing — empat salinan yang pelan-pelan berbeda satu sama lain.
         */
        // Footer landing memakai daftar yang sama, jadi ikut diberi $nav.
        // Label sengaja tidak dibungkus __(): menu tetap berbahasa Inggris
        // di kedua lokal, sesuai permintaan.
        View::composer(['partials.navPC', 'partials.navMobile', 'frontends.landing'], function ($view) {
            $lang = app()->getLocale();

            $view->with('nav', [
                ['label' => 'about', 'href' => route('about', $lang)],
                ['label' => 'FAQ', 'href' => route('faq', $lang)],
                [
                    'label' => 'map & data',
                    'children' => [
                        ['label' => 'terms of use', 'href' => route('termsofuse', $lang)],
                        ['label' => 'platform/map', 'href' => 'https://plataforma.mapbiomas.org/fire/fire_annual?t[regionKey]=indonesia'],
                    ],
                ],
                [
                    // ATBD tetap terpisah monthly/annual karena halamannya
                    // membedakan keduanya lewat query 'cat'.
                    'label' => 'methodology',
                    'children' => [
                        ['label' => 'ATBD Annual', 'href' => route('atbd', ['lang' => $lang, 'cat' => 'annual'])],
                        ['label' => 'ATBD Monthly', 'href' => route('atbd', ['lang' => $lang, 'cat' => 'monthly'])],
                        ['label' => 'reference map', 'href' => route('refrencemap', $lang)],
                    ],
                ],
                ['label' => 'news & event', 'href' => route('newsnevent', $lang)],
                [
                    'label' => 'downloads',
                    'children' => [
                        ['label' => 'collection map', 'href' => route('downloads', $lang)],
                        ['label' => 'infographics', 'href' => route('infographics', $lang)],
                        ['label' => 'factsheet', 'href' => route('factsheet', $lang)],
                    ],
                ],
            ]);

            /*
             * Tautan bahasa untuk rute yang sedang dibuka. Parameter rute ikut
             * dibawa supaya halaman detail berita (yang perlu id & slug) tidak
             * error, dan query dipertahankan agar ?cat= pada ATBD tidak hilang.
             * Rute tanpa nama (mis. pratinjau CMS) jatuh ke beranda bahasa itu.
             */
            $view->with('langUrl', fn (string $ke): string => Route::currentRouteName()
                ? route(
                    Route::currentRouteName(),
                    array_merge(request()->route()->parameters(), ['lang' => $ke], request()->query())
                )
                : url($ke));
        });
    }
}
