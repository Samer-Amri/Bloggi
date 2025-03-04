<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\Users\{UserResource, UsersCategoriesResource, UsersPostCommentsResource, UsersPostResource, UsersPostsResource, UsersTagsResource};
use App\Models\{Category, Comment, Post, PostMedia, Tag};
use App\Traits\HandlesPostImages;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\{Auth, Cache, File, Hash, Validator};
use Intervention\Image\Facades\Image;
use Stevebauman\Purify\Facades\Purify;

/**
 * Class UsersController
 *
 * Controller for handling user-related API requests.
 */
class UsersController extends Controller
{
    use HandlesPostImages;

    /**
     * UsersController constructor.
     * Apply authentication middleware.
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get authenticated user information.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function user_information() {
        $user = Auth::user();
        return response()->json(['errors' => false, 'message' => new UserResource($user)], 200);
    }

    /**
     * Get notifications for the authenticated user.
     *
     * @return array
     */
    public function getNotifications()
    {
        return [
            'read'      => auth()->user()->readNotifications,
            'unread'    => auth()->user()->unreadNotifications,
        ];
    }

    /**
     * Mark a notification as read.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function markAsRead(Request $request)
    {
        return auth()->user()->notifications->where('id', $request->id)->markAsRead();
    }

    /**
     * Update authenticated user information.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_user_information(Request $request)
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
            return response()->json(['errors' => true, 'message' => $validation->errors()], 201);
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
            return response()->json(['errors' => false, 'message' => 'User Information Updated Successfully'], 200);
        } else {
            return response()->json(['errors' => true, 'message' => 'Something was wrong'], 201);
        }
    }

    /**
     * Update authenticated user password.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_user_password(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => 'required|confirmed',
        ]);

        if($validation->fails())
        {
            return response()->json(['errors' => true, 'message' => $validation->errors()], 201);
        }

        $user = auth()->user();
        if(Hash::check($request->current_password, $user->password)){
            $update = $user->update([
                'password' => bcrypt($request->password),
            ]);
            if($update)
            {
                return response()->json(['errors' => false, 'message' => 'Password Updated Successfully'], 200);
            } else {
                return response()->json(['errors' => true, 'message' => 'Something was wrong'], 201);
            }
        } else
        {
            return response()->json(['errors' => true, 'message' => 'Current Password is wrong'], 201);
        }
    }

    /**
     * Get posts of the authenticated user.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function my_posts()
    {
        $user = Auth::user();
        $posts = $user->posts;
        return UsersPostsResource::collection($posts);
    }

    /**
     * Get tags and categories for creating a post.
     *
     * @return array
     */
    public function create_post()
    {
        $tags = Tag::all();
        $categories = Category::active()->get();
        return ['tags'=> UsersTagsResource::collection($tags), 'categories'=> UsersCategoriesResource::collection($categories)];
    }

    /**
     * Store a new post.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store_post(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'          => 'required',
            'description'    => 'required|min:50',
            'status'         => 'required',
            'comment_able'   => 'required',
            'category_id'    => 'required',
            'tags.*'    => 'required',
        ]);

        if($validator->fails()) {
            return response()->json(['errors' => true, 'messages' => $validator->errors()], 201);
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
                    'id' => $tag,
                ], [
                    'name' => $tag,
                ]);
                $new_tags[] = $tag->id;
            }
            $post->tags()->sync($new_tags);
        }

        if($request->status == 1) {
            Cache::forget('recent_posts');
            Cache::forget('global_tags');
        }

        return response()->json(['errors' => false, 'message' => 'Post created successfully'], 200);
    }

    /**
     * Get post details for editing.
     *
     * @param string|int $post
     * @return array
     */
    public function edit_post($post)
    {
        $post = Post::whereSlug($post)->orWhere('id', $post)->whereUserId(auth()->id())->first();
        $tags = Tag::all();
        $categories = Category::active()->get();
        return ['post' => new UsersPostResource($post), 'tags'=> UsersTagsResource::collection($tags), 'categories'=> UsersCategoriesResource::collection($categories)];
    }

    /**
     * Update an existing post.
     *
     * @param \Illuminate\Http\Request $request
     * @param string|int $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_post(Request $request, $post)
    {
        $validator = Validator::make($request->all(), [
            'title'          => 'required',
            'description'    => 'required|min:50',
            'status'         => 'required',
            'comment_able'   => 'required',
            'category_id'    => 'required',
            'tags.*' => 'required',
        ]);

        if($validator->fails()) {
            return response()->json(['errors' => true, 'messages' => $validator->errors()], 201);
        }

        $post = Post::whereSlug($post)->orWhere('id', $post)->whereUserId(auth()->id())->first();

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
                        'id' => $tag,
                    ], [
                        'name' => $tag,
                    ]);
                    $new_tags[] = $tag->id;
                }
                $post->tags()->sync($new_tags);
            }

            return response()->json(['errors' => false, 'message' => 'Post Updated Successfully'], 200);
        }

        return response()->json(['errors' => true, 'message' => 'Unauthorized'], 201);
    }

    /**
     * Delete a post.
     *
     * @param string|int $post_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete_post($post_id)
    {
        $post = Post::whereSlug($post_id)->orWhere('id', $post_id)->whereUserId(auth()->id())->first();

        if($post)
        {
            if($post->media->count() >0) {
                foreach($post->media as $media) {
                    if(File::exists('assets/posts/'. $media->file_name)){
                        unlink('assets/posts/'. $media->file_name);
                    }
                }
            }

            $post->delete();

            return  response()->json(['errors' => false, 'message' => 'Post Deleted Successfully'], 200);
        }

        return response()->json(['errors' => true, 'message' => 'Something was wrong'], 201);
    }

    /**
     * Delete a specific post media.
     *
     * @param int $media_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy_post_media($media_id)
    {
        $media = PostMedia::whereId($media_id)->first();

        if($media){
            if(File::exists('assets/posts/'.$media->file_name)) {
                unlink('assets/posts/'.$media->file_name);
            }
            $media->delete();
            return response()->json(['errors' => false, 'message' => 'Media Deleted Successfully'], 200);
        }

        return response()->json(['errors' => true, 'message' => 'Something was wrong'], 201);
    }

    /**
     * Get all comments for the authenticated user's posts.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function all_comments(Request $request)
    {
        $commentsQuery = Comment::query();

        if (isset($request->post) && $request->post != '') {
            $commentsQuery = $commentsQuery->where('post_id', $request->post);
        } else {
            $posts_id = auth()->user()->posts->pluck('id')->toArray();
            $commentsQuery = $commentsQuery->whereIn('post_id', $posts_id);
        }

        $comments = $commentsQuery->orderBy('id', 'desc')->get();

        return response()->json(UsersPostCommentsResource::collection($comments),  200);
    }

    /**
     * Get a specific comment for editing.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit_comment($id)
    {
        $comment = Comment::whereId($id)->WhereHas('post', function ($query){
            $query->where('posts.user_id', auth()->id() );
        })->first();

        if($comment) {
            return response()->json(['errors' => false, 'message' => new UsersPostCommentsResource($comment)], 200);
        } else {
            return response()->json(['errors' => true, 'message' => 'Something was wrong'], 201);
        }
    }

    /**
     * Update a specific comment.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_comment(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'url' => 'nullable|url',
            'status' => 'required',
            'comment' => 'required',
        ]);

        if($validation->fails()) {
            return response()->json(['errors' => true, 'message' => $validation->errors()], 201);
        }

        $comment = Comment::whereId($id)->WhereHas('post', function ($query){
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

            return response()->json(['errors' => false, 'message' => 'Comment Updated Successfully'], 200);
        }

        return response()->json(['errors' => true, 'message' => 'Something was wrong'], 201);
    }

    /**
     * Delete a specific comment.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete_comment($id)
    {
        $comment = Comment::whereId($id)->WhereHas('post', function ($query){
            $query->where('posts.user_id', auth()->id() );
        })->first();

        if($comment) {
            $comment->delete();
            Cache::forget('recent_comments');
            return response()->json(['errors' => false, 'message' => 'Comment Deleted Successfully'], 200);
        }

        return response()->json(['errors' => true, 'message' => 'Something was wrong. Comment Not Found'], 201);
    }

    /**
     * Logout the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['errors' => false, 'message' => 'Successfully logged out']);
    }
}
