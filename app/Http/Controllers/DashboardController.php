<?php

namespace App\Http\Controllers;

use App\Models\Enums\Server;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function welcome(): Response
    {
        return Inertia::render('Welcome');
    }

    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'servers' => Server::all(),
            'currentServer' => session('server', Server::GOBLIN->value),
        ]);
    }
}
