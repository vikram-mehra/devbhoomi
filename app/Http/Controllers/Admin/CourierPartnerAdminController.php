<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Courier\CourierManager;
use Illuminate\View\View;

class CourierPartnerAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    public function index(CourierManager $manager): View
    {
        $partners = $manager->partners();
        $default = (string) config('couriers.default');

        return view('admin.courier-partners', compact('partners', 'default'));
    }
}
