<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Class AdminController
 *
 * Controller for handling admin-related backend requests.
 */
class AdminController extends Controller
{
    /**
     * AdminController constructor.
     * Redirects to login form if the user is not authenticated.
     */
    public function __construct()
    {
        if (!Auth::check()) {
            return redirect()->route('admin.show_login_form');
        }
    }

    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        if (Auth::check()) {
            return view('backend.index');
        }
        return redirect()->route('admin.show_login_form');
    }

    /**
     * Display the authenticated user's profile.
     *
     * @return \Illuminate\View\View
     */
    public function profile()
    {
        $profile = Auth()->user();
        $role = $profile->roles->first()->name;
        return view('backend.users.profile', compact('profile', 'role'));
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function profileUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->all();
        $status = $user->fill($data)->save();

        if ($status) {
            request()->session()->flash('success', 'Successfully updated your profile');
        } else {
            request()->session()->flash('error', 'Please try again!');
        }
        return redirect()->back();
    }
}
