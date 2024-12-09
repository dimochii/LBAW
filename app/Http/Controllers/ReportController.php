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



    public function report(Request $request, $id)
    {
        if (!Auth::check()) {
            return redirect('/news')->with('error', 'You must be logged in to submit a report.');
        }

        if (Auth::user()->id == $id) {
            return redirect()->route('user.profile', ['user' => $id])
                ->with('failure', 'You can\'t report yourself.');
        }
        
        $validatedData = $request->validate([
            'reason' => 'required|string|max:1000',
            'report_type' => 'required|in:user_report,item_report,comment_report,topic_report',
        ]);

        
        $report = Report::create([
            'reason' => $validatedData['reason'],
            'report_date' => now(),
            'is_open' => true,
            'report_type' => $validatedData['report_type'],
            'authenticated_user_id' => $id,
        ]);

        
        return redirect()->route('user.profile', ['user' => $id])
                ->with('Sucess', 'You\'ve succesfully reported this user.');
    }

    public function multipleReports(Request $request) {

    if (!Auth::check()) {
        return redirect('/news')->with('error', 'You must be logged in to submit a report.');
    }

    $userIds = $request->input('reported_user_id'); 
    if (!is_array($userIds)) {
        $userIds = [$userIds]; 
    }
    
    if (in_array(Auth::user()->id, $userIds)) {
        return redirect()->route('user.profile', ['user' => Auth::user()->id])
            ->with('failure', 'You can\'t report yourself.');
    }

    $validatedData = $request->validate([
        'reason' => 'required|string|max:1000',
        'report_type' => 'required|in:user_report,item_report,comment_report,topic_report',        
    ]);

    
    foreach ($userIds as $userId) {
        Report::create([
            'reason' => $validatedData['reason'],
            'report_date' => now(),
            'is_open' => true,
            'report_type' => $validatedData['report_type'],
            'authenticated_user_id' => $userId,
        ]);
    }

    return redirect('/news')->with('sucess', 'authors reported.');
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
