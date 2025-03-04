<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, Validator};
use Stevebauman\Purify\Facades\Purify;

/**
 * Class AnnouncementsController
 *
 * Controller for handling announcement-related backend requests.
 */
class AnnouncementsController extends Controller
{
    /**
     * AnnouncementsController constructor.
     * Redirects to login form if the user is not authenticated.
     */
    public function __construct() {
        if(\auth()->check()){
            $this->middleware('auth');
        }
        else {
            return view('backend.auth.login');
        }
    }

    /**
     * Display a listing of the announcements.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (!\auth()->user()->ability('admin', 'manage_announcements,show_announcements')){
            return redirect('admin/index');
        }

        $announcements = Announcement::with(['user'])
                                     ->when(request('keyword') != '', function ($query){
                                         $query->search(request('keyword'));
                                     })
                                     ->when(request('status') != '', function ($query){
                                         $query->whereStatus(request('status'));
                                     })
                                     ->orderBy(request('sort_by') ??  'id', request('order_by') ??  'desc')
                                     ->paginate(request('limit_by')?? '10')
                                     ->withQueryString();

        return view('backend.announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new announcement.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        if (!\auth()->user()->ability('admin', 'create_announcements')){
            return redirect('admin/index');
        }
        return view('backend.announcements.create');
    }

    /**
     * Store a newly created announcement in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        if (!\auth()->user()->ability('admin', 'create_announcements')){
            return redirect('admin/index');
        }
        $validator = Validator::make($request->all(), [
            'title'          => 'required',
            'title_en'       => 'required',
            'description'    => 'required|min:50',
            'description_en' => 'required|min:50',
            'status'         => 'required',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data['title'] = $request->title;
        $data['title_en'] = $request->title_en;
        $data['description'] = Purify::clean($request->description);
        $data['description_en'] = Purify::clean($request->description_en);
        $data['status'] = $request->status;

        $announcement = auth()->user()->announcements()->create($data);

        if($request->status == 1) {
            Cache::forget('recent_announcements');
        }

        return redirect()->route('admin.announcements.index')->with([
            'message' => 'Announcement Created Successfully',
            'alert-type' => 'success',
        ]);
    }

    /**
     * Display the specified announcement.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        if (!\auth()->user()->ability('admin', 'display_announcements')){
            return redirect('admin/index');
        }
        $announcement = Announcement::with(['user'])->whereId($id)->first();
        return view('backend.announcements.show', compact('announcement'));
    }

    /**
     * Show the form for editing the specified announcement.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        if (!\auth()->user()->ability('admin', 'update_announcements')){
            return redirect('admin/index');
        }
        $announcement = Announcement::whereId($id)->first();
        return view('backend.announcements.edit', compact('announcement'));
    }

    /**
     * Update the specified announcement in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        if (!\auth()->user()->ability('admin', 'update_announcements')){
            return redirect('admin/index');
        }
        $validator = Validator::make($request->all(), [
            'title'          => 'required',
            'title_en'       => 'required',
            'description'    => 'required|min:50',
            'description_en' => 'required|min:50',
            'status'         => 'required',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $announcement = Announcement::whereId($id)->first();
        if($announcement) {
            $data['title'] = $request->title;
            $data['title_en'] = $request->title_en;
            $data['slug'] = null;
            $data['slug_en'] = null;
            $data['description'] = Purify::clean($request->description);
            $data['description_en'] = Purify::clean($request->description_en);
            $data['status'] = $request->status;

            $announcement->update($data);

            return redirect()->route('admin.announcements.index')->with([
                'message' => 'Announcement Updated Successfully',
                'alert-type' => 'success',
            ]);
        }
        return redirect()->route('admin.announcements.index')->with([
            'message' => 'Something was wrong please try again later',
            'alert-type' => 'danger',
        ]);
    }

    /**
     * Remove the specified announcement from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        if (!\auth()->user()->ability('admin', 'delete_announcements')){
            return redirect('admin/index');
        }
        $announcement = Announcement::whereId($id)->first();
        if($announcement) {
            $announcement->delete();
            return redirect()->route('admin.announcements.index')->with([
                'message' => 'Announcement Deleted Successfully',
                'alert-type' => 'success',
            ]);
        }
        return redirect()->route('admin.announcements.index')->with([
            'message' => 'Something was wrong. Announcement Not Found',
            'alert-type' => 'danger',
        ]);
    }
}
