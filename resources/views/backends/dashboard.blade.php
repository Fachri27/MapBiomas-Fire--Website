@extends('layouts.dashboardLayouts')

@section('content')
    @include('partials.backendHeader')
    @include('partials.backendNav')

    @php
        // Bar dipakai di beberapa tempat; pembagi dijaga agar tabel kosong
        // tidak memicu division by zero.
        $pct = fn ($value, $total) => $total > 0 ? round($value / $total * 100) : 0;
        $trendMax = $trend->max('total') ?: 1;
    @endphp

    <div class="max-w-6xl mx-auto px-6 py-8">
        <div class="flex justify-between mb-6">
            <h1 class="sm:text-3xl text-xl text-newgray-900 dark:text-newgray-300 font-semibold">Dashboard</h1>
            <span class="text-sm text-gray-500 self-end">{{ \Carbon\Carbon::now('Asia/Jakarta')->format('d M Y') }}</span>
        </div>

        {{-- Ringkasan jumlah konten --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($stats as $stat)
                <a href="{{ $stat['url'] }}" class="block bg-white dark:bg-newgray-800 border border-gray-200 dark:border-gray-800 rounded-lg shadow px-5 py-4 hover:border-newgray-900 dark:hover:border-gray-300">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $stat['label'] }}</p>
                    <p class="text-4xl font-semibold text-newgray-900 dark:text-gray-200 py-1">{{ $stat['value'] }}</p>
                    <p class="text-xs text-gray-500">{{ $stat['note'] }}</p>
                </a>
            @endforeach
        </div>

        <div class="grid lg:grid-cols-2 gap-4 py-4">
            {{-- Tren publikasi 6 bulan terakhir --}}
            <div class="bg-white dark:bg-newgray-800 border border-gray-200 dark:border-gray-800 rounded-lg shadow px-5 py-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">News published · recent months</p>
                {{-- Grid enam kolom, bukan flex: satu bulan saja tidak melar
                     memenuhi kartu. --}}
                <div class="grid grid-cols-6 gap-2 py-4">
                    @forelse ($trend as $month)
                        <div>
                            <p class="text-xs text-gray-500 text-center">{{ $month['total'] }}</p>
                            {{-- Tinggi bar persen, jadi tracknya harus tinggi tetap. --}}
                            <div class="h-28 flex items-end">
                                <div class="w-full bg-ember rounded" style="height: {{ max($pct($month['total'], $trendMax), 2) }}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 text-center pt-1">{{ $month['label'] }}</p>
                        </div>
                    @empty
                        <p class="text-sm italic text-gray-400">No news yet</p>
                    @endforelse
                </div>
            </div>

            {{-- Komposisi kategori --}}
            <div class="bg-white dark:bg-newgray-800 border border-gray-200 dark:border-gray-800 rounded-lg shadow px-5 py-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Content breakdown</p>
                @php
                    // Dua tabel berbeda, jadi persentasenya dihitung per grup
                    // supaya kategori 'monthly' tidak diadu dengan 'news'.
                    $groups = collect(['News & events' => $newsByCategory, 'Factsheets' => $factsheetByCategory])
                        ->filter(fn ($rows) => count($rows));
                @endphp
                <div class="py-3">
                    @forelse ($groups as $groupLabel => $rows)
                        @php $groupTotal = $rows->sum('total'); @endphp
                        <p class="text-xs text-gray-500 pt-2">{{ $groupLabel }} · {{ $groupTotal }}</p>
                        @foreach ($rows as $row)
                            <div class="py-1">
                                <div class="flex justify-between text-sm text-newgray-700 dark:text-gray-300">
                                    <span>{{ $row->category ?: 'uncategorized' }}</span>
                                    <span>{{ $row->total }}</span>
                                </div>
                                <div class="h-2 bg-gray-200 dark:bg-newgray-700 rounded-full">
                                    <div class="h-2 bg-ember-soft rounded-full" style="width: {{ $pct($row->total, $groupTotal) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    @empty
                        <p class="text-sm italic text-gray-400">No content yet</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Aktivitas terakhir --}}
        <div class="grid lg:grid-cols-2 gap-4">
            <div class="bg-white dark:bg-newgray-800 border border-gray-200 dark:border-gray-800 rounded-lg shadow px-5 py-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Latest news</p>
                @forelse ($recentNews as $item)
                    <a href="{{ url('/cms/editnews/'.$item->id) }}" class="flex justify-between gap-4 py-2 border-b border-gray-200 dark:border-gray-800 text-sm text-newgray-700 dark:text-gray-300 hover:text-newgray-900 dark:hover:text-white">
                        <span class="truncate min-w-0">{{ $item->titleID }}</span>
                        <span class="text-xs text-gray-500 whitespace-nowrap">
                            {{ $item->publishdate }} · {{ $item->status == 1 ? 'published' : 'draft' }}
                        </span>
                    </a>
                @empty
                    <p class="text-sm italic text-gray-400 py-2">No news yet</p>
                @endforelse
            </div>

            <div class="bg-white dark:bg-newgray-800 border border-gray-200 dark:border-gray-800 rounded-lg shadow px-5 py-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Latest infographics</p>
                @forelse ($recentInfographic as $item)
                    <a href="{{ url('/cms/editinfographic/'.$item->id) }}" class="flex justify-between gap-4 py-2 border-b border-gray-200 dark:border-gray-800 text-sm text-newgray-700 dark:text-gray-300 hover:text-newgray-900 dark:hover:text-white">
                        <span class="truncate min-w-0">{{ $item->titleEN }}</span>
                        <span class="text-xs text-gray-500 whitespace-nowrap">
                            {{ $item->publishdate }} · {{ $item->status == 1 ? 'published' : 'draft' }}
                        </span>
                    </a>
                @empty
                    <p class="text-sm italic text-gray-400 py-2">No infographics yet</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
