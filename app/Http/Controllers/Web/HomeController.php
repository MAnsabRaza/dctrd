<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Landing;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $activeTheme = getActiveTheme();
        $homeLanding = null;

        if (!empty($activeTheme)) {
            $homeLanding = $activeTheme->homeLanding;
        }

        if (empty($homeLanding)) {
            $homeLanding = Landing::query()
                ->where('enable', true)
                ->with([
                    'components' => function ($query) {
                        $query->with(['landingBuilderComponent']);
                        $query->orderBy('order', 'asc');
                    }
                ])
                ->first();
        }

        $seoSettings = getSeoMetas('home');
        $pageTitle = !empty($seoSettings['title']) ? $seoSettings['title'] : trans('home.home_title');
        $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('home.home_title');
        $pageRobot = getPageRobot('home');

        $data = [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'activeTheme' => $activeTheme,
            'homeLanding' => $homeLanding,
        ];

        return view('design_1.web.home.index', $data);
    }
}
