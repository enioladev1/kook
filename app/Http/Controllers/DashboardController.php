<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('dashboard', [
            'stats' => $this->dashboard->statsForUser($user),
            'recentEvents' => $this->dashboard->recentEventsForUser($user),
        ]);
    }
}
