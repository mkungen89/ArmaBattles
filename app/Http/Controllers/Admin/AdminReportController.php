<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlayerReport;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    use \App\Traits\LogsAdminActions;

    public function index(Request $request)
    {
        $query = PlayerReport::with('handler')->orderByDesc('reported_at');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('target_name', 'ilike', "%{$search}%")
                  ->orWhere('reporter_name', 'ilike', "%{$search}%")
                  ->orWhere('reason', 'ilike', "%{$search}%");
            });
        }

        $reports = $query->paginate(25)->withQueryString();
        $pendingCount = PlayerReport::pending()->count();

        return view('admin.reports.index', compact('reports', 'pendingCount'));
    }

    public function show(PlayerReport $report)
    {
        $report->load('handler');
        $history = PlayerReport::forTarget($report->target_name)
            ->where('id', '!=', $report->id)
            ->orderByDesc('reported_at')
            ->limit(20)
            ->get();

        return view('admin.reports.show', compact('report', 'history'));
    }

    public function update(Request $request, PlayerReport $report)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,resolved,dismissed',
            'admin_notes' => 'nullable|string|max:5000',
        ]);

        $oldStatus = $report->status;
        $report->status = $validated['status'];
        $report->admin_notes = $validated['admin_notes'];

        if ($oldStatus === 'pending' && $validated['status'] !== 'pending') {
            $report->handled_by = auth()->id();
            $report->handled_at = now();
        }

        $report->save();

        $this->logAction('report.update', null, null, [
            'report_id' => $report->id,
            'target' => $report->target_name,
            'old_status' => $oldStatus,
            'new_status' => $validated['status'],
        ]);

        return back()->with('success', 'Report updated successfully.');
    }
}
