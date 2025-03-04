<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;
use App\Models\Comment;

/**
 * Class PostCommentsController
 *
 * Controller for handling post comment-related backend requests.
 */
class PostCommentsController extends Controller
{
    /**
     * PostCommentsController constructor.
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
     * Display a listing of the post comments.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (!\auth()->user()->ability('admin', 'manage_post_comments,show_post_comments')){
            return redirect('admin/index');
        }

        $comments = Comment::query()
                           ->when(request('keyword') != '', function ($query){
                               $query->search(request('keyword'));
                           })
                           ->when(request('status') != '', function ($query){
                               $query->whereStatus(request('status'));
                           })
                           ->when(request('post_id') != '', function ($query){
                               $query->wherePostId(request('post_id'));
                           })
                           ->orderBy(request('sort_by') ??  'id', request('order_by') ??  'desc')
                           ->paginate(request('limit_by')?? '10')
                           ->withQueryString();

        $posts = Post::post()->select('id', 'title', 'title_en')->get();
        return view('backend.post_comments.index', compact('comments', 'posts' ));
    }

    /**
     * Show the form for creating a new post comment.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created post comment in storage.
     *
     * @param \Illuminate\Http\Request $request
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified post comment.
     *
     * @param int $id
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified post comment.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        if (!\auth()->user()->ability('admin', 'update_post_comments')){
            return redirect('admin/index');
        }

        $comment = Comment::whereId($id)->first();
        return view('backend.post_comments.edit', compact('comment') );
    }

    /**
     * Update the specified post comment in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        if (!\auth()->user()->ability('admin', 'update_post_comments')){
            return redirect('admin/index');
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'url' => 'nullable|url',
            'status' => 'required',
            'comment' => 'required',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $comment = Comment::whereId($id)->first();
        if($comment) {
            $data['name']    = $request->name;
            $data['email']   = $request->email;
            $data['url']     = $request->url;
            $data['status']  = $request->status;
            $data['comment'] = Purify::clean($request->comment);

            $comment->update($data);

            Cache::forget('recent_comments');

            return redirect()->route('admin.post_comments.index')->with([
                'message'    => 'Comment Updated Successfully',
                'alert-type' => 'success',
            ]);
        }

            return redirect()->route('admin.post_comments.index')->with([
                'message' => 'Something was wrong please try again later',
                'alert-type' => 'danger',
            ]);

    }

    /**
     * Remove the specified post comment from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        if (!\auth()->user()->ability('admin', 'delete_post_comments')){
            return redirect('admin/index');
        }
        $comment = Comment::whereId($id)->first();
        if($comment)
        {
            $comment->delete();
            return  redirect()->route('admin.post_comments.index')->with([
                'message' => 'Comment Deleted Successfully',
                'alert-type' => 'success',
            ]);
        }
        return redirect()->route('admin.post_comments.index')->with([
            'message' => 'Something was wrong. Comment Not Found',
            'alert-type' => 'danger',
        ]);
    }
}
