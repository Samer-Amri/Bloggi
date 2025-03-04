@extends('layouts.admin')
@section('content')

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex">
            <h6 class="m-0 font-weight-bold text-primary">{{__('Backend/posts.restore_posts')}}</h6>
            <div class="ml-auto d-flex">
                <form action="{{ route('admin.posts.restoreAll') }}" method="POST" class="mr-2">
                    @csrf
                    <button type="submit" class="btn btn-primary">
            <span class="icon text-white-50">
                <i class="fa fa-undo"></i>
            </span>
                        <span class="text">{{__('Backend/posts.restore_all')}}</span>
                    </button>
                </form>
                <a href="{{route('admin.posts.index')}}" class="btn btn-primary">
        <span class="icon text-white-50">
            <i class="fa fa-home"></i>
        </span>
                    <span class="text">{{__('Backend/posts.posts')}}</span>
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>{{__('Backend/posts.title')}}</th>
                    <th>{{__('Backend/posts.comments')}}</th>
                    <th>{{__('Backend/posts.status')}}</th>
                    <th>{{__('Backend/posts.category')}}</th>
                    <th>{{__('Backend/posts.user')}}</th>
                    <th>{{__('Backend/posts.deleted_at')}}</th>
                    <th class="text-center" style="width:30px;">{{__('Backend/posts.actions')}}</th>
                </tr>
                </thead>
                <tbody>
                @forelse($posts as $post)
                    <tr>
                        <td>{{ $post->title() }}</td>
                        <td>{{ $post->comments->count() }}</td>
                        <td>{{ $post->status() }}</td>
                        <td>{{ $post->category->name() }}</td>
                        <td>{{ $post->user->name }}</td>
                        <td>{{ $post->deleted_at->format('d-m-Y h:i a') }}</td>
                        <td>
                            <div class="btn-group">
                                <form action="{{ route('admin.posts.restore', $post->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-success"><i class="fa fa-undo"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">{{ __('Backend/posts.no_posts') }}</td>
                    </tr>
                @endforelse
                </tbody>
                <tfoot>
                <tr>
                    <th colspan="7">
                        <div class="float-right">
                            {!! $posts->links() !!}
                        </div>
                    </th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>

@endsection
