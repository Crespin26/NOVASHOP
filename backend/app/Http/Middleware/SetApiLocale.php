<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetApiLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale($request->getPreferredLanguage(['fr', 'en']) ?? 'fr');
        $response = $next($request);
        $response->headers->set('Content-Language', app()->getLocale());
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
