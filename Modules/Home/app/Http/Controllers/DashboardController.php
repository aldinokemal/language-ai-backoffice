<?php

namespace Modules\Home\Http\Controllers;

use App\Classes\Breadcrumbs;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    private string $url = '/dashboard';

    private function defaultParser(): array
    {
        return [
            'url' => $this->url,
        ];
    }

    public function index()
    {
        $breadcrumbs = [
            new Breadcrumbs('Dashboard', route('dashboard')),
        ];

        $parser = array_merge($this->defaultParser(), [
            'breadcrumbs' => $breadcrumbs,
        ]);

        return view('home::dashboard.index')->with($parser);
    }
}
