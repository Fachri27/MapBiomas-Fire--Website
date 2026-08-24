<div>
    <div class="flex items-center justify-between">
        <a class="text-3xl font-semibold capitalize">{{__('news & event') }}</a>
        <label class=" w-48 "  >
            <div class="relative flex w-full flex-col  text-neutral-600 dark:text-neutral-300">
                <label for="os" class="w-fit pl-0.5 text-gray-700 mb-1"></label>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute pointer-events-none right-4 top-3 size-5">
                    <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
                <select wire:ignore wire:model.live='category' class=" w-full appearance-none text-black  border border-landy  px-4 py-2 text-sm focus:outline-none">
                    <option selected value="all">all</option>
                    <option value="news">news</option>
                    <option value="event">event</option>
                </select>
            </div>
        </label>
    </div>
    <div class="mt-4 flex gap-2 flex-wrap justify-between ">
        @foreach ($data as $item)
            <div class="sm:w-[49%] w-full mt-6">
                {{-- Bentuk kartu mengikuti bagian kabar di landing: gambar, tanggal
                     merah, judul, lalu deskripsi. Satu <a> saja karena anchor
                     bersarang bukan markup yang sah. --}}
                <a href="{{ route('detailnews', [app()->getLocale(), $item->id, $item->slug]) }}"
                   class="group block">
                    <div class="overflow-hidden">
                        <img src="{{ asset('storage/files/photos/'.$item->img) }}" alt="{{ $item->title }}"
                             loading="lazy"
                             class="aspect-[476/268] w-full object-cover transition-transform duration-700 ease-[cubic-bezier(0.16,0.84,0.28,1)] group-hover:scale-[1.04]">
                    </div>

                    <p class="mt-4 font-display text-[14px] font-medium text-ember">
                        {{ \Illuminate\Support\Carbon::parse($item->publishdate)->locale(app()->getLocale())->translatedFormat('j F Y') }}
                    </p>

                    <h3 class="mt-1 min-h-[26px] font-display text-[18px] font-semibold leading-[26px] text-neutral-900 transition-colors group-hover:text-ember">
                        {{ $item->title }}
                    </h3>

                    <div class="mt-2 min-h-[88px] max-w-[60ch] font-display text-[14px] font-normal leading-[22px] text-neutral-500">
                        {{ strip_tags($item->description) }}
                    </div>
                </a>
            </div>
        @endforeach
        @if ($data)
        {{ $data->links('livewire.pagination') }}
        @endif
    </div>
</div>
