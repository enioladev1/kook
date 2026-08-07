<?php

namespace App\Http\Controllers;

use App\Services\AuditLogQueryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function __construct(private readonly AuditLogQueryService $auditLogs) {}

    public function index(Request $request): Response
    {
        return Inertia::render('audit-logs/index', [
            'logs' => $this->auditLogs->listForUser($request->user()),
        ]);
    }
}
