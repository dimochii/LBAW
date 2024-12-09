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

        // Recupera todos os relatórios, ordenados por data
        $reports = Report::orderBy('report_date', 'desc')->get();

        return view('pages.reports', compact('reports'));
    }


    public function report(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/news')->with('error', 'You must be logged in to submit a report.');
        }

        
        $validatedData = $request->validate([
            'reported_id' => 'required|int',
            'reason' => 'required|string|max:1000',
            'report_type' => 'required|in:user_report,item_report,comment_report,topic_report',
        ]);
        if ($request->report_type =='user_report' && (Auth::user()->id == $request->reported_id)){return redirect()->route('user.profile', ['user' => $id])
            ->with('failure', 'You can\'t report yourself.');}

        
        $report = Report::create([
            'reported_id' => $validatedData['reported_id'],
            'reason' => $validatedData['reason'],
            'report_date' => now(),
            'is_open' => true,
            'report_type' => $validatedData['report_type'],
            'authenticated_user_id' => Auth::user()->id,
        ]);

        return redirect()->back()->with('success', 'Reported successfully' );
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
