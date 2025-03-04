<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Spatie\Valuestore\Valuestore;

/**
 * Class SettingsController
 *
 * Controller for handling settings-related backend requests.
 */
class SettingsController extends Controller
{
    /**
     * SettingsController constructor.
     * Redirects to login form if the user is not authenticated.
     */
    public function __construct()
    {
        if (\auth()->check()){
            $this->middleware('auth');
        } else {
            return view('backend.auth.login');
        }
    }

    /**
     * Display a listing of the settings.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (!\auth()->user()->ability('admin', 'manage_settings,show_settings')) {
            return redirect('admin/index');
        }

        $section = (isset(\request()->section) && \request()->section != '') ? \request()->section : 'general';
        $settings_sections = Setting::select('section')->distinct()->pluck('section');
        $settings = Setting::whereSection($section)->get();

        return view('backend.settings.index', compact('section', 'settings_sections', 'settings'));
    }

    /**
     * Update the specified settings in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        for ($i = 0; $i < count($request->id); $i++) {
            $input['value'] = isset($request->value[$i]) ? $request->value[$i] : null;
            Setting::whereId($request->id[$i])->first()->update($input);
        }
        $this->generateCache();

        return redirect()->route('admin.settings.index')->with([
            'message' => 'Settings updated successfully',
            'alert-type' => 'success'
        ]);
    }

    /**
     * Generate cache for the settings.
     */
    private function generateCache()
    {
        $settings = Valuestore::make(config_path('settings.json'));
        Setting::all()->each(function ($item) use ($settings) {
            $settings->put($item->key, $item->value);
        });
    }
}
