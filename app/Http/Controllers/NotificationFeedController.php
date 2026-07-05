<?php

namespace App\Http\Controllers;

use App\Models\NotificationFeed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationFeedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['data' => []]);
        }

        return response()->json([
            'data' => NotificationFeed::feedForUser($user, 10),
        ]);
    }
}