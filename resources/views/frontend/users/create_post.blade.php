@extends('layouts.app')
@section('style')
    <link rel="stylesheet" href="{{asset('frontend/js/summernote/summernote-bs4.min.css')}}">
    <link rel="stylesheet" href="{{ asset('frontend/js/select2/css/select2.min.css') }}"/>
@endsection
@section('content')
    <div class="col-lg-9 col-12">
        <h3>{{__('frontend/general.create_post')}}</h3>
        <form method="post" action="{{route('users.post.store')}}" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="title">{{__('frontend/general.title')}}</label>
                <input type="text" id="title"  name="title" value="{{old('title')}}" class="form-control">
                @error('title')<span class="text-danger">{{ $message }}</span>@enderror
            </div>
            <div class="form-group">
                <label for="title_en">{{__('frontend/general.title_en')}}</label>
                <input type="text" name="title_en" value="{{old('title_en')}}" class="form-control">
                @error('title_en')<span class="text-danger">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="description">{{__('frontend/general.description')}}</label>
                <textarea name="description" class="form-control summernote">{{old('description')}}</textarea>
                @error('description')<span class="text-danger">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="description_en">{{__('frontend/general.description_en')}}</label>
                <textarea name="description_en" class="form-control summernote">{{old('description_en')}}</textarea>
                @error('description_en')<span class="text-danger">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label for="tags">{{__('frontend/general.tags')}}</label>
                <button type="button" class="btn btn-primary btn-xs"
                        id="select_btn_tag">{{__('frontend/general.select_all')}}</button>
                <button type="button" class="btn btn-primary btn-xs"
                        id="deselect_btn_tag">{{__('frontend/general.deselect_all')}}</button>
                <select name="tags[]" class="form-control selects" multiple="multiple" id="select_all_tags">
                    @foreach($tags->toArray() as $tag)
                        <option
                            value="{{$tag->id}}">{{$tag->name()}}</option>
                    @endforeach
                </select>
                @error('tags')<span class="text-danger">{{ $message }}</span>@enderror
            </div>
            <div class="row">
                <div class="col-4">
                    <label for="category_id">{{__('frontend/general.category')}}</label>
                    <select name="category_id" id="category_id" class="form-control">
                        <option value="">---</option>
                        @foreach($categories->toArray() as $category)
                            <option value="{{ $category->id }}">{{ $category->name() }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="col-4">
                    <label for="comment_able">{{__('frontend/general.comment_able')}}</label>
                    <select name="comment_able" id="comment_able" class="form-control">
                        <option value="0" {{ old('comment_able') == 0 ? 'selected' : '' }}>{{__('frontend/general.no')}}</option>
                        <option value="1" {{ old('comment_able') == 1 ? 'selected' : '' }}>{{__('frontend/general.yes')}}</option>
                    </select>
                    @error('comment_able')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="col-4">
                    <label for="status">{{__('frontend/general.status')}}</label>
                    <select name="status" id="status" class="form-control">
                        <option value="1" {{ old('status') == 1 ? 'selected' : '' }}>{{__('frontend/general.active')}}</option>
                        <option value="0" {{ old('status') == 0 ? 'selected' : '' }}>{{__('frontend/general.inactive')}}</option>
                    </select>
                    @error('status')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
            </div>
            <div class="row pt-4">
                <div class="col-12">
                    <label for="post-images">{{__('frontend/general.images')}}</label>
                    <br>
                    <div class="file-loading">
                        <input id="post-images" type="file" name="images[]" multiple="multiple">
                        <span class="form-text text-muted">{{__('frontend/general.images')}}</span>
                        @error('images')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                </div>
            </div>
            <div class="form-group pt-4">
                <button type="submit" class="btn btn-primary">{{__('frontend/general.submit')}}</button>
            </div>
        </form>>
    </div>
    <div class="col-lg-3 col-12 md-mt-40 sm-mt-40">
        @include('partial.frontend.users.sidebar')
    </div>
@endsection
@section('script')
    <script src="{{ asset('frontend/js/summernote/summernote-bs4.min.js')}}"></script>
    <script src="{{ asset('frontend/js/select2/js/select2.full.min.js')}}"></script>
    <script>
        $(function () {
            $('.summernote').summernote({
                tabsize: 2,
                height: 200,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']],
                ],
            });
            $('.selects').select2({
                tags: true,
                minimumResultsForSearch: Infinity,
            });
            $('#select_btn_tag').click(function () {
                $('#select_all_tags > option').prop('selected', 'selected');
                $('#select_all_tags').trigger('change');
            });
            $('#deselect_btn_tag').click(function () {
                $('#select_all_tags > option').prop('selected', '');
                $('#select_all_tags').trigger('change');
            });
            $('#post-images').fileinput({
                theme: 'fas',
                maxFileCount: 5,
                allowedFileTypes: ['image'],
                showCancel: true,
                showRemove: false,
                showUpload: false,
                overwriteInitial: false,
            });
        });
    </script>
@endsection
