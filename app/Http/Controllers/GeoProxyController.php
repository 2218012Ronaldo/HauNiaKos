<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GeoProxyController extends Controller
{
    /**
     * Simple cached proxy for Nominatim search so clients don't hit the public API directly.
     * Caches results per query for 24 hours by default.
     */
    public function search(Request $request)
    {
        // Only accept known params and sanitize values
        $allowed = [
            'q',
            'format',
            'addressdetails',
            'limit',
            'viewbox',
            'bounded',
            'lat',
            'lon',
            'polygon_geojson',
            'osm_type',
            'osm_id',
        ];
        $raw = $request->only($allowed);

        // Trim and sanitize query
        $q = isset($raw['q']) ? trim((string) $raw['q']) : null;

        // If this is a search without query, return empty array
        if (! $q && ! $request->query('lat')) {
            return response()->json([], 200);
        }

        $params = [];
        if ($q) {
            $params['q'] = Str::limit($q, 255);
        }

        $params['format'] = 'jsonv2';

        $addressdetails = in_array($raw['addressdetails'] ?? null, ['0', '1', 0, 1], true) ? 1 : 1;
        $params['addressdetails'] = $addressdetails;

        // Clamp limit to reasonable maximum
        $limit = (int) ($raw['limit'] ?? 10);
        $params['limit'] = max(1, min(10, $limit));

        if (! empty($raw['viewbox'])) {
            $params['viewbox'] = substr((string) $raw['viewbox'], 0, 200);
        }

        // allow optional polygon/geojson shapes if requested (client can pass polygon_geojson=1)
        if (
            ! empty($raw['polygon_geojson']) &&
            in_array($raw['polygon_geojson'], ['1', 'true', 1, true], true)
        ) {
            $params['polygon_geojson'] = 1;
        }

        if (! empty($raw['bounded'])) {
            $params['bounded'] = in_array($raw['bounded'], ['1', 'true', 'True', 1, true], true)
                ? 1
                : 0;
        }

        // If reverse geocoding by lat/lon is provided, forward as reverse
        $isReverse = $request->query('lat') && $request->query('lon');

        // If lookup by osm_type/osm_id is requested, call the lookup endpoint
        $isLookup = $request->query('osm_id') && $request->query('osm_type');

        // Include an email if configured to comply with Nominatim policy
        $email = env('NOMINATIM_EMAIL');
        if ($email) {
            $params['email'] = $email;
        }

        $cacheKey =
            'nominatim:'.md5(http_build_query($params).($isReverse ? ':reverse' : ':search'));

        $cacheTtl =
            ! empty($params['polygon_geojson']) && $params['polygon_geojson']
                ? now()->addDays(7)
                : now()->addDay();

        $result = Cache::remember($cacheKey, $cacheTtl, function () use (
            $params,
            $isReverse,
            $request,
            $isLookup,
        ) {
            try {
                // Recompute lookup flag inside the closure to avoid undefined variable
                // errors if the outer scope wasn't captured correctly by the running PHP process.
                $isLookup = $request->query('osm_id') && $request->query('osm_type');

                $userAgent = config('app.name').' ('.config('app.url', url('/')).')';
                $client = Http::withHeaders([
                    'User-Agent' => $userAgent,
                ])->timeout(10);

                if ($isReverse) {
                    $response = $client->get(
                        'https://nominatim.openstreetmap.org/reverse',
                        array_merge($params, [
                            'lat' => $request->query('lat'),
                            'lon' => $request->query('lon'),
                            'format' => 'jsonv2',
                        ]),
                    );
                } elseif ($isLookup) {
                    // Build osm_ids parameter for lookup: format is [N|W|R] + id (e.g. R4631022)
                    $otype = $request->query('osm_type');
                    $oid = $request->query('osm_id');
                    $map = [
                        'node' => 'N',
                        'way' => 'W',
                        'relation' => 'R',
                        'N' => 'N',
                        'W' => 'W',
                        'R' => 'R',
                    ];
                    $prefix = $map[$otype] ?? null;
                    if ($prefix) {
                        $lookupParams = [
                            'osm_ids' => $prefix.$oid,
                            'format' => 'jsonv2',
                            'polygon_geojson' => $params['polygon_geojson'] ?? 0,
                        ];
                        $response = $client->get(
                            'https://nominatim.openstreetmap.org/lookup',
                            $lookupParams,
                        );
                    } else {
                        $response = $client->get(
                            'https://nominatim.openstreetmap.org/search',
                            $params,
                        );
                    }
                } else {
                    $response = $client->get('https://nominatim.openstreetmap.org/search', $params);
                }

                // If Nominatim returns non-200, cache a short error stub to avoid hammering
                if (! $response->successful()) {
                    return [
                        'error' => true,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ];
                }

                return $response->json();
            } catch (\Throwable $e) {
                return [
                    'error' => true,
                    'exception' => $e->getMessage(),
                    'trace' => method_exists($e, 'getTraceAsString')
                        ? $e->getTraceAsString()
                        : null,
                ];
            }
        });

        return response()->json($result);
    }
}
