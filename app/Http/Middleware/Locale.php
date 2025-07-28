<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Lang;

class Locale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            if (config('locale.status')) {
                // Check if locale is set in session
                if (Session::has('locale') && array_key_exists(Session::get('locale'), config('locale.languages'))) {
                    $locale = Session::get('locale');
                    App::setLocale($locale);
                    Lang::setLocale($locale);
                    Carbon::setLocale($locale);
                    
                    // Set RTL for Arabic
                    if (config('locale.languages')[$locale][2]) {
                        session(['lang-rtl' => true]);
                    } else {
                        session()->forget('lang-rtl');
                    }
                    
                    Log::info('Locale set from session', ['locale' => $locale]);
                } else {
                    // Try to detect from browser language
                    $userLanguages = preg_split('/[,;]/', $request->server('HTTP_ACCEPT_LANGUAGE', ''));
                    foreach ($userLanguages as $language) {
                        $language = trim($language);
                        if (array_key_exists($language, config('locale.languages'))) {
                            App::setLocale($language);
                            Lang::setLocale($language);
                            Carbon::setLocale($language);
                            
                            // Set RTL for Arabic
                            if (config('locale.languages')[$language][2]) {
                                session(['lang-rtl' => true]);
                            } else {
                                session()->forget('lang-rtl');
                            }
                            
                            Log::info('Locale set from browser', ['locale' => $language]);
                            break;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in locale middleware', ['error' => $e->getMessage()]);
            // Fallback to default locale
            App::setLocale(config('app.locale'));
        }
        
        return $next($request);
    }
}
