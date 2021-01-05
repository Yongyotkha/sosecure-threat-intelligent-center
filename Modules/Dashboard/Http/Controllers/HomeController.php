<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Current Page Name
     *
     * @var string
     */
    protected $page;
    
    public function __construct()
    {
        $this->middleware(['installed', 'auth', 'verified','2fa']);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index($dashboard = null)
    {
        $dashboard = $this->getDashboard($dashboard);
        $allowedTabs = ['expenses', 'invoicing', 'payments', 'projects', 'sales', 'ticketing'];
        $data['dashboard'] = in_array($dashboard, $allowedTabs) ? $dashboard : 'invoicing';
        $data['page'] = langapp('home');
        if (session('show_tour')) {
            $data['help'] = true;
            session()->pull('show_tour');
        }

        return view('dashboard::index')->with($data);
    }
    // Get dashboard to display
    protected function getDashboard($dashboard)
    {
        if (!is_null($dashboard)) {
            session(['dashboard_view' => $dashboard]);
        }

        return session('dashboard_view', $dashboard);
    }
}
