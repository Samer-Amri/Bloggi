<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;

/**
 * Class ServiceController
 *
 * Controller for handling language change and Vue.js translation requests.
 */
class ServiceController extends Controller
{
    /**
     * Change the application language.
     *
     * @param string $locale The locale to set.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function change_language($locale): \Illuminate\Http\RedirectResponse
    {
        try {
            if(array_key_exists($locale, config('locale.languages'))){
                App::setLocale($locale);
                Lang::setLocale($locale);
                Session::put('locale', $locale);
                Carbon::setLocale($locale);
            }
            return redirect()->back();
        } catch (\Exception $exception) {
            return redirect()->back();
        }
    }

    /**
     * Get Arabic translations for Vue.js.
     *
     * @return void
     */
    public function vue_translate_ar() {
        $strings_ar = Cache::rememberForever('lang_ar.js', function () {
            $files_ar = glob(resource_path('lang/ar/Api/*.php'));
            $strings_ar = [];
            foreach($files_ar as $file_ar) {
                $name_ar = basename($file_ar, '.php');
                $strings_ar[$name_ar] = require $file_ar;
            }
            return $strings_ar;
        });
        header('Content-Type:text/javascript');
        echo('window.i18n =' . json_encode($strings_ar) . ';' );
        exit();
    }

    /**
     * Get English translations for Vue.js.
     *
     * @return void
     */
    public function vue_translate_en() {
        $strings_en = Cache::rememberForever('lang_en.js', function () {
            $files_en = glob(resource_path('lang/en/Api/*.php'));
            $strings_en = [];
            foreach($files_en as $file_en) {
                $name_en = basename($file_en, '.php');
                $strings_en[$name_en] = require $file_en;
            }
            return $strings_en;
        });
        header('Content-Type:text/javascript');
        echo('window.i18n =' . json_encode($strings_en) . ';' );
        exit();
    }
}
