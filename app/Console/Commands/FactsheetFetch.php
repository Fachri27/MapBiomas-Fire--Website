<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Mengunduh PDF factsheet dari tautan luar ke storage lokal sekali saja,
 * lalu mengisi kolom fileID/fileEN. Setelah itu halaman factsheet melayani
 * berkas lokal (cepat, dengan range request bawaan) alih-alih me-proxy
 * puluhan megabita dari server luar pada tiap kunjungan.
 */
class FactsheetFetch extends Command
{
    protected $signature = 'factsheet:fetch';

    protected $description = 'Unduh PDF factsheet dari tautan ke storage agar sampul dan unduhan dilayani lokal';

    public function handle(): int
    {
        foreach (DB::table('factsheet')->orderBy('id')->get() as $row) {
            foreach (['ID', 'EN'] as $suffix) {
                $fileCol = 'file'.$suffix;
                $linkCol = 'link'.$suffix;

                if (filled($row->$fileCol)) {
                    $this->line("factsheet #{$row->id} [{$suffix}]: sudah ada berkas, lewati");
                    continue;
                }

                $link = $row->$linkCol;
                if (! is_string($link) || ! str_starts_with($link, 'http')) {
                    $this->line("factsheet #{$row->id} [{$suffix}]: tanpa tautan http, lewati");
                    continue;
                }

                $resp = Http::timeout(300)->get($link);
                if (! $resp->successful()) {
                    $this->warn("factsheet #{$row->id} [{$suffix}]: gagal mengunduh (HTTP {$resp->status()})");

                    continue;
                }

                $body = $resp->body();
                if (! str_starts_with($body, '%PDF-')) {
                    $this->warn("factsheet #{$row->id} [{$suffix}]: isi tautan bukan PDF, lewati");

                    continue;
                }

                $name = sha1($row->id.$suffix.uniqid()).'.pdf';
                Storage::disk('local')->put('public/files/factsheet/'.$name, $body);
                DB::table('factsheet')->where('id', $row->id)->update([
                    $fileCol => $name,
                    'updated_at' => now(),
                ]);
                $this->info('factsheet #'.$row->id.' ['.$suffix.']: tersimpan '.number_format(strlen($body) / 1048576, 1).' MB');
            }
        }

        return self::SUCCESS;
    }
}
