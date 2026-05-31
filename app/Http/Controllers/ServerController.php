<?php

namespace App\Http\Controllers;

use App\Models\Enums\Server;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server' => ['required', 'string'],
        ]);

        Server::from($validated['server']); // validates it's a known server

        session(['server' => $validated['server']]);

        return response()->json(['server' => $validated['server']]);
    }
}
