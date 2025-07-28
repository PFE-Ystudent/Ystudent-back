<?php

namespace App\Http\Controllers;

use App\Http\Resources\Admin\BugReportResource;
use App\Models\BugReport;
use App\Models\ReportingCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportingController extends Controller
{
    public function bugReport(Request $request)
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'min:15'],
            'reporting_category_id' => ['required', 'exists:' . (new ReportingCategory())->getTable() . ',id']
        ]);

        $bugReport = new BugReport();
        $bugReport->description = $validated['description'];
        $bugReport->user()->associate(Auth::id());
        $bugReport->reportingCategory()->associate($validated['reporting_category_id']);
        $bugReport->save();

        return response()->noContent();
    }

    public function fetchAll(string $status)
    {
        $bugReports = BugReport::query()
            ->with(['user', 'reportingCategory'])
            ->where('is_processed', $status === 'processed')
            ->when($status === 'done', function ($q) {
                $q->where('is_done', true)
                    ->orWhere('is_archived', true);
            })
            ->when($status !== 'done', function ($q) {
                $q->where('is_done', false)
                    ->where('is_archived', false);
            })
            ->get();

        return response()->json([
            'bugReports' => BugReportResource::collection($bugReports)
        ]);
    }

    public function updateStatus(BugReport $bugReport, Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'string', 'in:is_processed,is_done,is_archived']
        ]);

        $bugReport->is_processed = false;
        $bugReport->is_done = false;
        $bugReport->is_archived = false;
        if (isset($validated['status'])) {
            $bugReport->{$validated['status']} = true;
        }
        $bugReport->save();

        return response()->noContent();
    }

    public function update(BugReport $bugReport, Request $request)
    {
        $validated = $request->validate([
            'note' => ['nullable', 'string'],
            'important' => ['nullable', 'boolean'],
        ]);

        $bugReport->update($validated);
        return response()->noContent();
    }

    public function getStats()
    {
        $oneMonthAgo = Carbon::now()->subMonth();
        $baseQuery = BugReport::query()
            ->selectRaw('count(*) as opened, sum(is_archived) as closed, sum(is_done) as resolved');

        $totals = $baseQuery->first();
        $lastMonth = $baseQuery->where('created_at', '>', $oneMonthAgo)->first();
        
        return response()->json([
            'stats' => [
                'totals' => $totals,
                'lastMonth' => $lastMonth
            ]
        ]);
    }
}