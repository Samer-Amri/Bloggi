<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\{Category, Post, PostMedia, Tag};
use App\Repositories\PostRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Stevebauman\Purify\Facades\Purify;

/**
 * Class PostsController
 *
 * Controller for handling post-related backend requests.
 */
class PostsController extends Controller
{
    /**
     * @var PostRepositoryInterface
     */
    protected $postRepository;

    /**
     * PostsController constructor.
     *
     * @param PostRepositoryInterface $postRepository
     */
    public function __construct(PostRepositoryInterface $postRepository) {
        $this->postRepository = $postRepository;
        if(\auth()->check()){
            $this->middleware('auth');
        }
        else {
            return view('backend.auth.login');
        }
    }

    /**
     * Display a listing of the posts.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (!\auth()->user()->ability('admin', 'manage_posts,show_posts')){
            return redirect('admin/index');
        }

        $posts = $this->postRepository->all();

        $posts = $this->postRepository->with(['media', 'user', 'comments'])->post()
                                      ->when(request('keyword') != '', function ($query){
                                          $query->search(request('keyword'));
                                      })
                                      ->when(request('category_id') != '', function ($query){
                                          $query->whereCategoryId(request('category_id'));
                                      })
                                      ->when(request('tag_id') != '', function ($query){
                                          $query->whereHas('tags', function ($q) {
                                              $q->where('id', request('tag_id'));
                                          });
                                      })
                                      ->when(request('status') != '', function ($query){
                                          $query->whereStatus(request('status'));
                                      })
                                      ->orderBy(request('sort_by') ??  'id', request('order_by') ??  'desc')
                                      ->paginate(request('limit_by')?? '10')
                                      ->withQueryString();

        $tags = Tag::orderBy('id', 'desc')->select('id', 'name', 'name_en')->get();
        $categories= Category::orderBy('id', 'desc')->select('id', 'name',  'name_en')->get();
        return view('backend.posts.index', compact('posts', 'categories', 'tags'));
    }

    /**
     * Show the form for creating a new post.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        if (!\auth()->user()->ability('admin', 'create_posts')){
            return redirect('admin/index');
        }
        $tags = Tag::select('name', 'id');
        $categories= Category::orderBy('id', 'desc')->select('name', 'id');
        return view('backend.posts.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created post in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        if (!\auth()->user()->ability('admin', 'create_posts')){
            return redirect('admin/index');
        }
        $validator = Validator::make($request->all(), [
            'title'          => 'required',
            'title_en' => 'required',
            'description'    => 'required|min:50',
            'description_en' => 'required|min:50',
            'status'         => 'required',
            'comment_able'   => 'required',
            'category_id'    => 'required',
            'images.*'       => 'nullable|mimes:jpg,jpeg,png,gif|max:20000',
            'tags.*'         => 'required',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $data = [
            'title' => $request->title,
            'title_en' => $request->title_en,
            'description' => Purify::clean($request->description),
            'description_en' => Purify::clean($request->description_en),
            'status' => $request->status,
            'post_type' => 'post',
            'comment_able' => $request->comment_able,
            'category_id' => $request->category_id,
        ];

        $post = $this->postRepository->create($data);

        if($request->images && count($request->images) > 0) {
            $i = 1;
            foreach ($request->images as $file) {
                $filename = $post->slug . '-' . time() . '-' . $i . '.'
                            . $file->getClientOriginalExtension();
                $file_size = $file->getSize();
                $file_type = $file->getMimeType();
                $path = public_path('assets/posts/' . $filename);
                Image::make($file->getRealPath())->resize(
                    800,
                    null,
                    function ($constraint) {
                        $constraint->aspectRatio();
                    }
                )->save($path, 100);

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

        if($request->status == 1) {
            Cache::forget('recent_posts');
            Cache::forget('global_tags');
        }

        return redirect()->route('admin.posts.index')->with([
            'message' => 'Post Created Successfully',
            'alert-type' => 'success',
        ]);
    }

    /**
     * Display the specified post.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        if (!\auth()->user()->ability('admin', 'display_posts')){
            return redirect('admin/index');
        }
        $post = $this->postRepository
            ->with(['media', 'user', 'category', 'comments'])
            ->whereId($id)->post()->first();
        return view('backend.posts.show', compact( 'post') );
    }

    /**
     * Show the form for editing the specified post.
     *
     * @param int $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        if (!\auth()->user()->ability('admin', 'update_posts')){
            return redirect('admin/index');
        }
        $tags = Tag::select('id', 'name', 'name_en')->get();
        $categories = Category::orderBy('id', 'desc')->select('id', 'name', 'name_en')->get();
        $post = $this->postRepository->with(['media'])->whereId($id)->post()->first();
        return view('backend.posts.edit', compact('categories', 'post', 'tags'));
    }

    /**
     * Update the specified post in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        if (!\auth()->user()->ability('admin', 'update_posts')){
            return redirect('admin/index');
        }
        $validator = Validator::make($request->all(), [
            'title'          => 'required',
            'title_en' => 'required',
            'description'    => 'required|min:50',
            'description_en' => 'required|min:50',
            'status'         => 'required',
            'comment_able'   => 'required',
            'category_id'    => 'required',
            'images.*'       => 'nullable|mimes:jpg,jpeg,png,gif|max:20000',
            'tags.*'         => 'required',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $post = $this->postRepository->find($id);
        if($post) {
            $data = [
                'title' => $request->title,
                'title_en' => $request->title_en,
                'slug' => null,
                'slug_en' => null,
                'description' => Purify::clean($request->description),
                'description_en' => Purify::clean($request->description_en),
                'status' => $request->status,
                'comment_able' => $request->comment_able,
                'category_id' => $request->category_id,
            ];
            $this->postRepository->update($data, $id);

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

            return redirect()->route('admin.posts.index')->with([
                'message' => 'Post Updated Successfully',
                'alert-type' => 'success',
            ]);
        }
        return redirect()->route('admin.posts.index')->with([
            'message' => 'Something was wrong please try again later',
            'alert-type' => 'danger',
        ]);
    }

    /**
     * Remove the specified post from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        if (!\auth()->user()->ability('admin', 'delete_posts')){
            return redirect('admin/index');
        }
        $post = $this->postRepository->find($id);
        if($post)
        {
            if($post->media->count() >0) {
                foreach($post->media as $media) {
                    if(File::exists('assets/posts/'. $media->file_name)){
                        unlink('assets/posts/'. $media->file_name);
                    }
                }
            }

            $this->postRepository->delete($id);

            return  redirect()->route('admin.posts.index')->with([
                'message' => 'Post Deleted Successfully',
                'alert-type' => 'success',
            ]);
        }
        return redirect()->route('admin.posts.index')->with([
            'message' => 'Something was wrong. Post Not Found',
            'alert-type' => 'danger',
        ]);
    }

    /**
     * Remove a specific image from a post.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function removeImage(Request $request){
        if (!\auth()->user()->ability('admin', 'delete_posts')){
            return redirect('admin/index');
        }
        $media = PostMedia::whereId($request->media_id)->first();
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
     * Display a listing of the trashed posts for restoration.
     *
     * @return \Illuminate\View\View
     */
    public function restoreIndex()
    {
        if (!\auth()->user()->ability('admin', 'restore_posts')){
            return redirect('admin/index');
        }

        $posts = Post::onlyTrashed()->with(['media', 'user', 'comments', 'category'])->paginate(10);

        return view('backend.posts.restore', compact('posts'));
    }

    /**
     * Restore the specified trashed post.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        if (!\auth()->user()->ability('admin', 'restore_posts')){
            return redirect('admin/index');
        }

        $post = Post::onlyTrashed()->find($id);
        if ($post) {
            $post->restore();
            $post->update(['deleted_at' => null]);
            $post->refresh();
            if(is_null($post->deleted_at)) {
                return redirect()->route('admin.posts.index')->with([
                    'message'    => 'Post Restored Successfully',
                    'alert-type' => 'success',
                ]);
            }
            return redirect()->route('admin.posts.index')->with([
                'message' => 'Something went wrong. Post Not Found',
                'alert-type' => 'danger',
            ]);
        }
    }

    /**
     * Restore all trashed posts.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restoreAll()
    {
        if (!\auth()->user()->ability('admin', 'restore_posts')){
            return redirect('admin/index');
        }

        Post::onlyTrashed()->restore();

        return redirect()->route('admin.posts.restoreIndex')->with([
            'message' => 'All Posts Restored Successfully',
            'alert-type' => 'success',
        ]);
    }
}
