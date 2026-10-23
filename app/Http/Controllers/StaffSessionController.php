<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffSessionController extends Controller
{
    public function create() { return view('login'); }

    public function store(Request $request)
    {
        $this->validate($request, ['email' => 'required|email', 'password' => 'required|string']);
        if (!Auth::attempt($request->only('email', 'password'))) {
            return redirect('/login')->withErrors(['email' => 'The supplied credentials were not accepted.']);
        }
        $request->session()->regenerate();
        return redirect('/items');
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->flush();
        $request->session()->regenerate();
        return redirect('/login');
    }
}
