@extends('layouts.app')
@section('content')
    {{--    I am Index Page--}}

                <div class="col-lg-9 col-12">
                        <h3>{{__('frontend/users.edit_comment_on', ['title' => $comment->post->title])}} </h3>
                    <form action="{{route('users.comment.update', $comment->id)}}" method="post">
                        @csrf
                        @method('put')
                    <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="name">{{__('frontend/general.name')}}</label>
                                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $comment->name) }}">
                                    @error('name')<span class="text-danger">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="email">{{__('frontend/general.email')}}</label>
                                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $comment->email) }}">
                                    @error('email')<span class="text-danger">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="url">{{__('frontend/general.website')}}</label>
                                    <input type="text" name="url" id="url" class="form-control" value="{{ old('url', $comment->url) }}">
                                    @error('url')<span class="text-danger">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-3">
                                    <label for="status">{{__('frontend/general.status')}}</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="1" {{ old('status') == 1 ? 'selected' : '' }}>{{__('frontend/general.active')}}</option>
                                        <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>{{__('frontend/general.inactive')}}</option>
                                    </select>
                                    @error('status')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <label for="comment">{{__('frontend/general.comment')}}</label>
                                <textarea name="comment" id="comment" class="form-control">{{ old('comment', $comment->comment) }}</textarea>
                                @error('comment')<span class="text-danger">{{ $message }}</span>@enderror
                            </div>
                        </div>
                            <div class="form-group pt-4">
                                <button type="submit" class="btn btn-primary">{{__('frontend/general.submit')}}</button>
                            </div>
                    </form>
                </div>
                <div class="col-lg-3 col-12 md-mt-40 sm-mt-40">
                    @include('partial.frontend.users.sidebar')
                </div>

@endsection

