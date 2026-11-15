<?php

namespace App\Http\Controllers;

use App\Models\ArcadeMachine;
use App\Models\Card;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $customers = Customer::with('cards')->get();
        $machines = ArcadeMachine::all();

        return view('welcome', compact('customers', 'machines'));
    }
}
