<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')
            ->when($request->module, fn($q, $m) => $q->where('module', $m))
            ->when($request->action, fn($q, $a) => $q->where('action', $a))
            ->when($request->search, fn($q, $s) => $q->where('description', 'like', "%{$s}%"))
            ->latest();

        $logs = $query->paginate(25);
        return view('admin.activity-logs.index', compact('logs'));
    }
}
