<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, File, Validator};


/**
 * Class PostTagsController
 *
 * Controller for handling post tag-related backend requests.
 */
class PostTagsController extends Controller
{
    /**
     * PostTagsController constructor.
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
     * Display a listing of the post tags.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (!\auth()->user()->ability('admin', 'manage_post_tags,show_post_tags')){
            return redirect('admin/index');
        }

        $tags = Tag::withCount('posts')
                   ->when(request('keyword') != '', function ($query){
                       $query->search(request('keyword'));
                   })
                   ->orderBy(request('sort_by') ??  'id', request('order_by') ??  'desc')
                   ->paginate(request('limit_by')?? '10')
                   ->withQueryString();

        return view('backend.post_tags.index', compact('tags'));
    }

    /**
     * Show the form for creating a new post tag.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        if (!\auth()->user()->ability('admin', 'create_post_tags')){
            return redirect('admin/index');
        }
        return view('backend.post_tags.create');
    }

    /**
     * Store a newly created post tag in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        if (!\auth()->user()->ability('admin', 'create_post_tags')) {
            return redirect('admin/index');
        }

        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'name_en'       => 'required',
        ]);
        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data['name'] = $request->name;
        $data['name_en'] = $request->name_en;
        Tag::create($data);
        Cache::forget('global_tags');

        return redirect()->route('admin.post_tags.index')->with([
            'message' => 'Tag created successfully',
            'alert-type' => 'success',
        ]);
    }

    /**
     * Display the specified post tag.
     *
     * @param int $id
     */
    public function show($id)
    {
        /* if (!\auth()->user()->ability('admin', 'display_post')){
            return redirect('admin/index');
        } */
    }

    /**
     * Show the form for editing the specified post tag.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        if (!\auth()->user()->ability('admin', 'update_post_tags')){
            return redirect('admin/index');
        }
        $tag = Tag::whereId($id)->first();
        return view('backend.post_tags.edit', compact('tag'));
    }

    /**
     * Update the specified post tag in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        if (!\auth()->user()->ability('admin', 'update_post_tags')){
            return redirect('admin/index');
        }
        $validator = Validator::make($request->all(), [
            'name'          => 'required',
            'name_en'       => 'required',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $tag = Tag::whereId($id)->first();
        if($tag) {
            $data['name'] = $request->name;
            $data['name_en'] = $request->name_en;
            $data['slug'] = null;
            $data['slug_en'] = null;

            $tag->update($data);
            Cache::forget('global_tags');

            return redirect()->route('admin.post_tags.index')->with([
                'message'    => 'Tag Updated Successfully',
                'alert-type' => 'success',
            ]);
        }
        return redirect()->route('admin.post_tags.index')->with([
            'message' => 'Something was wrong please try again later',
            'alert-type' => 'danger',
        ]);
    }

    /**
     * Remove the specified post tag from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        if (!\auth()->user()->ability('admin', 'delete_post_tags')){
            return redirect('admin/index');
        }
        $tag = Tag::whereId($id)->first();

        $tag->delete();
        return redirect()->route('admin.post_tags.index')->with([
            'message' => 'Tag Deleted Successfully',
            'alert-type' => 'success',
        ]);
    }
}
