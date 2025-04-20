<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SiteViewr;
use Illuminate\Http\JsonResponse;

class SiteViewrController extends Controller
{
    public function incrementSiteViews(): JsonResponse
    {
        $siteViewr = SiteViewr::first();
        if (!$siteViewr) {
            $siteViewr = SiteViewr::create(['SiteViews' => 0]);
        }
        $siteViewr->increment('SiteViews');
        return response()->json(['siteViews' => $siteViewr->SiteViews]);
    }

}
