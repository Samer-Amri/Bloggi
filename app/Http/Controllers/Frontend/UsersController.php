<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Comment;
use App\Models\PostMedia;
use App\Models\Tag;
use App\Traits\HandlesPostImages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Stevebauman\Purify\Facades\Purify;
use App\Models\Post;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Class UsersController
 *
 * Controller for handling user-related frontend requests.
 */
class UsersController extends Controller
{
    use HandlesPostImages;

    /**
     * UsersController constructor.
     * Apply authentication and email verification middleware.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'verified']);
    }

    /**
     * Display a listing of the authenticated user's posts.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $posts = auth()->user()->posts()->with(['media', 'user'])
                       ->withCount('comments')->orderBy('id', 'desc')->paginate(10);
        return view('frontend.users.dashboard', compact('posts'));
    }

    /**
     * Show the form for editing the authenticated user's information.
     *
     * @return \Illuminate\View\View
     */
    public function edit_info()
    {
        return view('frontend.users.edit_info');
    }

    /**
     * Update the authenticated user's information.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update_info(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'name' => 'required',
            'email' => 'required|email',
            'mobile' => 'required|numeric',
            'bio' => 'nullable|min:10',
            'receive_email' => 'required',
            'user_image' => 'nullable|image|max:20000|mimes:jpeg,jpg,png',
        ]);

        if($validation->fails())
        {
            return redirect()->back()->withErrors($validation)->withInput();
        }

        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['mobile'] = $request->mobile;
        $data['bio'] = $request->bio;
        $data['receive_email'] = $request->receive_email;

        if($image = $request->file('user_image'))
        {
            if(auth()->user()->user_image != '')
            {
                if(File::exists('assets/users/'.auth()->user()->user_image))
                {
                    unlink('assets/users/'.auth()->user()->user_image);
                }
            }
            $filename = Str::slug(auth()->user()->username) . '.'. $image->getClientOriginalName();
            $path = public_path('assets/users/'. $filename);
            Image::make($image->getRealPath())->resize(300, null, function ($constraint){
                $constraint->aspectRatio();
            })->save($path, 100);

            $data['user_image'] = $filename;
        }

        $update = auth()->user()->update($data);

        if($update)
        {
            return redirect()->back()->with([
                'message' => 'User Info Updated Successfully',
                'alert-type' => 'success',
            ]);
        } else {
            return redirect()->back()->with([
                'message' => 'Something was wrong please try again later',
                'alert-type' => 'danger',
            ]);
        }
    }

    /**
     * Update the authenticated user's password.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update_password(Request $request)
    {
        $validation = Validator::make($request->all(),[
            'current_password' => 'required',
            'password' => 'required|confirmed',
        ]);

        if($validation->fails())
        {
            return redirect()->back()->withErrors($validation)->withInput();
        }

        $user = auth()->user();
        if(Hash::check($request->current_password, $user->password)){
            $update = $user->update([
                'password' => bcrypt($request->password),
            ]);
            if($update)
            {
                return redirect()->back()->with([
                    'message' => 'Password Updated Successfully',
                    'alert-type' => 'success',
                ]);
            } else {
                return redirect()->back()->with([
                    'message' => 'Something was wrong please try again later',
                    'alert-type' => 'danger',
                ]);
            }
        }
    }

    /**
     * Show the form for creating a new post.
     *
     * @return \Illuminate\View\View
     */
    public function create_post()
    {
        $tags = Tag::pluck('name', 'id');
        $categories = Category::active()->pluck('name', 'id');
        return view('frontend.users.create_post', compact('categories', 'tags'));
    }

    /**
     * Store a newly created post in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store_post(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'          => 'required',
            'description'    => 'required|min:50',
            'status'         => 'required',
            'comment_able'   => 'required',
            'category_id'    => 'required',
            'tags.*'         => 'required',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data ['title']                   = $request->title;
        $data ['description']             = Purify::clean($request->description);
        $data ['status']                  = $request->status;
        $data ['comment_able']            = $request->comment_able;
        $data ['category_id']             = $request->category_id;

        $post = auth()->user()->posts()->create($data);

        $this->handlePostImages($post, $request->images);

        if(count($request->tags) > 0) {
            $new_tags = [];
            foreach ($request->tags as $tag) {
                $tag = Tag::firstOrCreate([
                    'id' => $tag
                ], [
                    'name' => $tag
                ]);
                $new_tags[] = $tag->id;
            }
            $post->tags()->sync($new_tags);
        }

        if($request->status == 1) {
            Cache::forget('recent_posts');
            Cache::forget('global_tags');
        }

        return redirect()->back()->with([
            'message' => 'Post Created Successfully',
            'alert-type' => 'success',
        ]);
    }

    /**
     * Show the form for editing the specified post.
     *
     * @param int $post_id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit_post($post_id)
    {
        $post = Post::whereSlug($post_id)->orWhere('id', $post_id)->whereUserId(auth()->id())->first();
        if($post) {
            $tags = Tag::pluck('name', 'id');
            $categories = Category::whereStatus(1)->pluck('name', 'id');
            return view('frontend.users.edit_post', compact('categories', 'post', 'tags'));
        }
        return redirect()->route('frontend.index')->with([
            'message' => 'Post Not Found',
            'alert-type' => 'warning',
        ]);
    }

    /**
     * Update the specified post in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $post_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update_post(Request $request, $post_id)
    {
        $validator = Validator::make($request->all(), [
            'title'          => 'required',
            'description'    => 'required|min:50',
            'status'         => 'required',
            'comment_able'   => 'required',
            'category_id'    => 'required',
            'tags.*'         => 'required',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $post = Post::whereSlug($post_id)->orWhere('id', $post_id)->whereUserId(auth()->id())->first();
        if($post) {
            $data ['title']                   = $request->title;
            $data ['description']             = Purify::clean($request->description);
            $data ['status']                  = $request->status;
            $data ['comment_able']            = $request->comment_able;
            $data ['category_id']             = $request->category_id;

            $post->update($data);

            if($request->images && count($request->images) > 0) {
                $i = 1;
                foreach($request->images as $file) {
                    $filename   = $post->slug.'-'.time().'-'.$i.'.'.$file->getClientOriginalExtension();
                    $file_size  = $file->getSize();
                    $file_type  = $file->getMimeType();
                    $path       = public_path('assets/posts/'. $filename);
                    Image::make($file->getRealPath())->resize(800, null, function($constraint) {
                        $constraint->aspectRatio();
                    })->save($path, 100);

                    $post->media()->create([
                        'file_name' => $filename,
                        'file_size' => $file_size,
                        'file_type' => $file_type,
                    ]);
                    $i++;
                }
            }

            if(count($request->tags) > 0) {
                $new_tags = [];
                foreach ($request->tags as $tag) {
                    $tag = Tag::firstOrCreate([
                        'id' => $tag
                    ], [
                        'name' => $tag
                    ]);
                    $new_tags[] = $tag->id;
                }
                $post->tags()->sync($new_tags);
            }

            return redirect()->back()->with([
                'message' => 'Post Updated Successfully',
                'alert-type' => 'success',
            ]);
        }
        return redirect()->back()->with([
            'message' => 'Something was wrong please try again later',
            'alert-type' => 'danger',
        ]);
    }

    /**
     * Remove the specified post from storage.
     *
     * @param int $post_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy_post($post_id)
    {
        $post = Post::whereSlug($post_id)->orWhere('id', $post_id)->whereUserId(auth()->id())->first();
        if($post)
        {
            if($post->media->count() > 0) {
                foreach($post->media as $media) {
                    if(File::exists('assets/posts/'. $media->file_name)){
                        unlink('assets/posts/'. $media->file_name);
                    }
                }
            }

            $post->delete();

            return redirect()->back()->with([
                'message' => 'Post Deleted Successfully',
                'alert-type' => 'success',
            ]);
        }
        return redirect()->back()->with([
            'message' => 'Something was wrong. Post Not Found',
            'alert-type' => 'danger',
        ]);
    }

    /**
     * Remove the specified post media from storage.
     *
     * @param int $media_id
     * @return bool
     */
    public function destroy_post_media($media_id)
    {
        $media = PostMedia::whereId($media_id)->first();
        if($media){
            if(File::exists('assets/posts/'.$media->file_name)) {
                unlink('assets/posts/'.$media->file_name);
            }
            $media->delete();
            return true;
        }
        return false;
    }

    /**
     * Display a listing of the comments for the authenticated user's posts.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function show_comments(Request $request)
    {
        $commentsQuery = Comment::query();
        if (isset($request->post) && $request->post != '') {
            $commentsQuery = $commentsQuery->where('post_id', $request->post);
        } else {
            $posts_id = auth()->user()->posts->pluck('id')->toArray();
            $commentsQuery = $commentsQuery->whereIn('post_id', $posts_id);
        }
        $comments = $commentsQuery->orderBy('id', 'desc')->paginate(10);

        $comments->appends(request()->except('page'));

        return view('frontend.users.comments', compact('comments'));
    }

    /**
     * Show the form for editing the specified comment.
     *
     * @param int $comment_id
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function edit_comment($comment_id)
    {
        $comment = Comment::whereId($comment_id)->WhereHas('post', function ($query){
            $query->where('posts.user_id', auth()->id() );
        })->first();
        if($comment) {
            return view('frontend.users.edit_comment', compact('comment'));
        } else {
            return redirect()->back()->with([
                'message' => 'Something was wrong. Comment Not found',
                'alert-type' => 'danger',
            ]);
        }
    }

    /**
     * Update the specified comment in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $comment_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update_comment(Request $request, $comment_id)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'url' => 'nullable|url',
            'status' => 'required',
            'comment' => 'required',
        ]);

        if($validation->fails()) {
            return redirect()->back()->withErrors($validation)->withInput();
        }

        $comment = Comment::whereId($comment_id)->WhereHas('post', function ($query){
            $query->where('posts.user_id', auth()->id() );
        })->first();

        if($comment) {
            $data['name']       = $request->name;
            $data['email']      = $request->email;
            $data['url']        = $request->url != '' ? $request->url : null;
            $data['status']     = $request->status;
            $data['comment']    = Purify::clean($request->comment);

            $comment->update($data);

            if($request->status == 1) {
                Cache::forget('recent_comments');
            }

            return redirect()->back()->with([
                'message' => 'Comment Updated Successfully',
                'alert-type' => 'success',
            ]);
        }
        return redirect()->back()->with([
            'message' => 'Something was wrong. Comment Not Found',
            'alert-type' => 'danger',
        ]);
    }

    /**
     * Remove the specified comment from storage.
     *
     * @param int $comment_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy_comment($comment_id)
    {
        $comment = Comment::whereId($comment_id)->WhereHas('post', function ($query){
            $query->where('posts.user_id', auth()->id() );
        })->first();
        if($comment) {
            $comment->delete();
            Cache::forget('recent_comments');

            return redirect()->back()->with([
                'message' => 'Comment Deleted Successfully',
                'alert-type' => 'success',
            ]);
        }
        return redirect()->back()->with([
            'message' => 'Something was wrong. Comment Not Found',
            'alert-type' => 'danger',
        ]);
    }
}
