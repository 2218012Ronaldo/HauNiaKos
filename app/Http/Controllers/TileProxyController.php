<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class TileProxyController extends Controller
{
    /**
     * Serve map tile PNG, caching on disk to reduce requests to upstream.
     * URL: /tiles/{z}/{x}/{y}.png
     */
    public function tile(Request $request, $z, $x, $y)
    {
        // basic validation
        if (! ctype_digit((string) $z) || ! ctype_digit((string) $x) || ! ctype_digit((string) $y)) {
            return response('Invalid tile coordinates', 400);
        }

        $z = (int) $z;
        $x = (int) $x;
        $y = (int) $y;

        // path inside storage/app/tiles
        $relative = "tiles/{$z}/{$x}/{$y}.png";
        $disk = Storage::disk('local');

        if ($disk->exists($relative)) {
            $path = $disk->path($relative);

            return response()->file($path, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=2592000', // 30 days
            ]);
        }

        // fetch from OpenStreetMap tile server
        $upstream = "https://tile.openstreetmap.org/{$z}/{$x}/{$y}.png";

        try {
            $resp = Http::withHeaders([
                'User-Agent' => config('app.name').' ('.config('app.url', url('/')).')',
            ])
                ->timeout(10)
                ->get($upstream);

            if (! $resp->successful()) {
                return response('Upstream error', 502);
            }

            $contents = $resp->body();

            // ensure directory exists
            $dir = dirname($disk->path($relative));
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // write to storage
            file_put_contents($disk->path($relative), $contents);

            return response($contents, 200, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=2592000',
            ]);
        } catch (\Exception $e) {
            return response('Tile fetch error', 502);
        }
    }
}
