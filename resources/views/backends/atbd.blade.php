@extends('layouts.dashboardLayouts')

@section('content')
    @include('partials.backendHeader')
    @include('partials.backendNav')
    <div class="max-w-6xl mx-auto px-6 md:px-8 sm:py-16 py-8 min-h-screen">
        <livewire:page-atbd />
    </div>

    {{-- Editor TinyMCE dibungkus wire:ignore, jadi Livewire tidak boleh
         menyentuh isinya. Saat kategori berganti, kontennya didorong ke editor
         yang sudah berdiri — jauh lebih ringan daripada membangunnya ulang. --}}
    <script>
        document.addEventListener('livewire:init', function () {
            Livewire.on('atbd-konten-diganti', function (payload) {
                var data = Array.isArray(payload) ? payload[0] : payload;
                if (! data || typeof tinymce === 'undefined') {
                    return;
                }

                ['contentEN', 'contentID'].forEach(function (nama) {
                    var editor = tinymce.get(nama);
                    if (editor) {
                        editor.setContent(data[nama] || '');
                    }
                });
            });
        });
    </script>
@endsection
