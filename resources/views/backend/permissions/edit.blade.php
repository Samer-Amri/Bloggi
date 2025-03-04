@extends('layouts.admin')
@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex">
            <h6 class="m-0 font-weight-bold text-primary">{{__('Backend/permissions.edit_permission')}}
                ( {{ $permission->display_name() }} )</h6>
            <div class="ml-auto">
                <a href="{{route('admin.permissions.index')}}" class="btn btn-primary">
                    <span class="icon text-white-50">
                        <i class="fa fa-home"></i>
                    </span>
                    <span class="text">{{__('Backend/permissions.permissions')}}</span>
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.permissions.update', $permission->id) }}">
                @csrf
                @method('PATCH')
                <div class="row">
                    <div class="col-2">
                        <div class="form-group">
                            <label for="name">{{__('Backend/permissions.name')}}</label>
                            <input type="text" id="name" name="name" class="form-control" value="{{old('name', $permission->name)}}">
                            @error('name')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group">
                            <label for="display_name">{{__('Backend/permissions.display_name')}}</label>
                            <input type="text" id="display_name" name="display_name" class="form-control" value="{{old('display_name', $permission->display_name)}}">
                            @error('display_name')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group">
                            <label for="display_name_en">{{__('Backend/permissions.display_name_en')}}</label>
                            <input type="text" id="display_name_en" name="display_name_en" class="form-control"
                                   value="{{old('display_name_en', $permission->display_name_en)}}">
                            @error('display_name_en')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group">
                            <label for="description">{{__('Backend/permissions.description')}}</label>
                            <input type="text" id="description" name="description" class="form-control"
                                   value="{{old('description', $permission->description)}}">
                            @error('description')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group">
                            <label for="description_en">{{__('Backend/permissions.description_en')}}</label>
                            <input type="text" id="description_en" name="description_en" class="form-control"
                                   value="{{old('description_en', $permission->description_en)}}">
                            @error('description_en')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-2">
                        <div class="form-group">
                            <label for="route">{{__('Backend/permissions.route')}}</label>
                            <input type="text" id="route" name="route" class="form-control"
                                   value="{{old('route', $permission->route)}}">
                            @error('route')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="module">{{__('Backend/permissions.module')}}</label>
                            <input type="text" id="module" name="module" class="form-control" value="{{old('module', $permission->module)}}">
                            @error('module')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="as">{{__('Backend/permissions.as')}}</label>
                            <input type="text" id="as" name="as" class="form-control"
                                   value="{{old('as', $permission->as)}}">
                            @error('as')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="icon">{{__('Backend/permissions.icon')}}</label>
                            <input type="text" id="icon" name="icon" class="form-control"
                                   value="{{old('icon', $permission->icon)}}">
                            @error('icon')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="sidebar_link">{{__('Backend/permissions.sidebar_link')}}</label>
                            <select id="sidebar_link" name="sidebar_link" class="form-control">
                                <option value=""> --- </option>
                                <option
                                    value="0" {{old('sidebar_link', $permission->sidebar_link) == '0' ? 'selected' : ''}}>{{__('Backend/permissions.no')}}</option>
                                <option
                                    value="1" {{old('sidebar_link', $permission->sidebar_link) == '1' ? 'selected' : ''}}>{{__('Backend/permissions.yes')}}</option>
                            </select>
                            @error('sidebar_link')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="parent">{{__('Backend/permissions.parent')}}</label>
                            <select id="parent" name="parent" class="form-control">
                                <option value="0"> --- </option>
                                @foreach($main_permissions as $main_perm)
                                    <option
                                        value="{{$main_perm->id}}" {{old('parent', $permission->parent) == $main_perm->id ? 'selected' : ''}}>{{$main_perm->display_name()}}</option>
                                @endforeach
                            </select>
                            @error('parent')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="parent_show">{{__('Backend/permissions.parent_show')}}</label>
                            <select id="parent_show" name="parent_show" class="form-control">
                                <option value="0"> --- </option>
                                @foreach($main_permissions as $main_perm)
                                    <option
                                        value="{{$main_perm->id}}" {{old('parent_show', $permission->parent_show) == $main_perm->id ? 'selected' : ''}}>{{$main_perm->display_name()}}</option>
                                @endforeach
                            </select>
                            @error('parent_show')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="parent_original">{{__('Backend/permissions.parent_original')}}</label>
                            <select id="parent_original" name="parent_original" class="form-control">
                                <option value="0"> --- </option>
                                @foreach($main_permissions as $main_perm)
                                    <option
                                        value="{{$main_perm->id}}" {{old('parent_original', $permission->parent_original) == $main_perm->id ? 'selected' : ''}}>{{$main_perm->display_name()}}</option>
                                @endforeach
                            </select>
                            @error('parent_original')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label for="appear">{{__('Backend/permissions.appear')}}</label>
                            <select id="appear" name="appear" class="form-control">
                                <option value=""> --- </option>
                                <option
                                    value="0" {{old('appear', $permission->appear) == '0' ? 'selected' : ''}}>{{__('Backend/permissions.no')}}</option>
                                <option
                                    value="1" {{old('appear', $permission->appear) == '1' ? 'selected' : ''}}>{{__('Backend/permissions.yes')}}</option>
                            </select>
                            @error('appear')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label for="ordering">{{__('Backend/permissions.ordering')}}</label>
                            <input type="text" id="ordering" name="ordering" class="form-control"
                                   value="{{old('ordering', $permission->ordering)}}">
                            @error('ordering')<span class="text-danger">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    <div class="col-9">
                    </div>
                </div>

                <div class="form-group pt-4">
                    <button type="submit" class="btn btn-primary">{{__('Backend/permissions.submit')}}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
