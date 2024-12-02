<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        //descomentar isto depois
        /* 
        if (! $user->is_admin) {
            return redirect('/news')->with('error', 'Access denied.');
        }*/

        $reports = Report::orderBy('report_date', 'desc')->paginate(10);

        return view('pages.reports', compact('reports'));
    }


    public function report(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect('/news')->with('error', 'You must be logged in to submit a report.');
        }


        $validatedData = $request->validate([
            'reason' => 'required|string|max:1000',
            'report_type' => 'required|in:user_report,post_report,comment_report',
        ]);

        
        $report = Report::create([
            'reason' => $validatedData['reason'],
            'report_date' => now(),
            'is_open' => true,
            'report_type' => $validatedData['report_type'],
            'authenticated_user_id' => $id,
        ]);

        
        return response()->json([
            'message' => 'Report submitted successfully',
            'report' => $report,
        ], 201);
    }


    public function resolve($id)
    {
        $user = Auth::user();

        if (!$user->is_admin) {
            return redirect('/news')->with('error', 'Access denied.');
        }


        $report = Report::find($id);

        if (!$report) {
            return response()->json(['message' => 'Report not found'], 404);
        }

        $report->update(['is_open' => false]);

        return response()->json([
            'message' => 'Report resolved successfully',
            'report' => $report,
        ], 200);
    }

}
