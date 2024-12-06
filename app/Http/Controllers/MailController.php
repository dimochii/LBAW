<?php

namespace App\Http\Controllers;

use Mail;
use Illuminate\Http\Request;
use App\Mail\MailModel;
use TransportException;
use Exception;

class MailController extends Controller
{
    function send(Request $request) {

        $mailData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        Mail::to($request->email)->send(new MailModel($mailData));
        return redirect()->route('news');
    }

}