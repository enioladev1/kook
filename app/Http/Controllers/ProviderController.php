<?php

namespace App\Http\Controllers;

use App\Services\ProviderCatalogService;
use Inertia\Inertia;
use Inertia\Response;

class ProviderController extends Controller
{
    public function __construct(private readonly ProviderCatalogService $providers) {}

    public function index(): Response
    {
        return Inertia::render('providers/index', [
            'providers' => $this->providers->active(),
        ]);
    }
}
