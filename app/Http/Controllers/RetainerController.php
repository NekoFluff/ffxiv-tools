<?php

namespace App\Http\Controllers;

use App\Models\Enums\DataCenter;
use App\Models\Enums\Server;
use App\Models\Retainer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RetainerController extends Controller
{
    public function index(): Response
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        return Inertia::render('Retainers/Index', [
            'retainers' => $user->retainers()->with('listings', 'items')->get(),
            'servers' => Server::all(),
            'currentServer' => session('server', Server::GOBLIN->value),
        ]);
    }

    public function edit(Retainer $retainer): Response
    {
        $this->authorize('view', $retainer);

        return Inertia::render('Retainers/Edit', [
            'retainer' => $retainer,
            'servers' => Server::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'server' => ['required', 'string'],
        ]);

        if ($user->retainers()->count() >= 10) {
            return back()->withErrors(['name' => 'You have reached the maximum number of 10 retainers.']);
        }

        if ($user->retainers()->where('name', $validated['name'])->where('server', $validated['server'])->exists()) {
            return back()->withErrors(['name' => 'You already have a retainer with that name.']);
        }

        $server = Server::from($validated['server']);

        $user->retainers()->create([
            'name' => $validated['name'],
            'server' => $server,
            'data_center' => $server->dataCenter(),
        ]);

        return redirect()->route('retainers');
    }

    public function update(Request $request, Retainer $retainer): RedirectResponse
    {
        $this->authorize('update', $retainer);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'server' => ['required', 'string'],
        ]);

        $server = Server::from($validated['server']);

        $retainer->update([
            'name' => $validated['name'],
            'server' => $server,
            'data_center' => $server->dataCenter(),
        ]);

        return redirect()->route('retainers');
    }

    public function destroy(Retainer $retainer): RedirectResponse
    {
        $this->authorize('delete', $retainer);

        $retainer->delete();

        return redirect()->route('retainers');
    }
}
