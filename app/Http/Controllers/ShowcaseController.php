<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShowcaseController extends Controller
{
    public function show(string $slug): View
    {
        $showcase = config('showcase.'.$slug);

        if (!is_array($showcase)) {
            throw new NotFoundHttpException;
        }

        return view('market.showcase', compact('showcase'));
    }
}
