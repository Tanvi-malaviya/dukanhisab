<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetUserLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->input('locale')
            ?? $request->header('X-Locale') 
            ?? $request->header('Accept-Language') 
            ?? 'en';

        // Clean and validate locale
        $locale = strtolower(trim($locale));
        if (str_contains($locale, ',')) {
            $locale = explode(',', $locale)[0];
        }
        if (str_contains($locale, '-')) {
            $locale = explode('-', $locale)[0];
        }
        if (str_contains($locale, '_')) {
            $locale = explode('_', $locale)[0];
        }

        \Log::info('SetUserLocale middleware triggered', [
            'x_locale_header' => $request->header('X-Locale'),
            'resolved_locale' => $locale,
            'app_locale_before' => App::getLocale(),
        ]);

        $supportedLocales = ['en', 'gu', 'hi', 'bn', 'brx', 'doi', 'kn', 'ks', 'kok', 'mai', 'ml', 'mni', 'mr', 'ne', 'or', 'pa', 'sa', 'sat', 'sd', 'ta', 'te', 'ur'];
        if (in_array($locale, $supportedLocales, true)) {
            App::setLocale($locale);
        } else {
            App::setLocale('en');
        }

        return $next($request);
    }
}

