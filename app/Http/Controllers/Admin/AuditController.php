<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;

class AuditController extends Controller
{
    public function index()
    {
        $audits = Audit::with('user')
            ->where('auditable_type', 'App\\Models\\Task')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        return view('admin.audits', compact('audits'));
    }
}
