<?php

namespace App\Http\Controllers;

use App\Models\BugReport;
use App\Models\ReportingCategory;
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
}