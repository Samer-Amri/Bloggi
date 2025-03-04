@extends('layouts.admin')
@section('content')

    <x-profile :profile="$profile" :role="$role" />

@endsection

@section('style')
    <style>
        .breadcrumbs{
            list-style: none;
        }
        .breadcrumbs li{
            float:left;
            margin-right:10px;
        }
        .breadcrumbs li a:hover{
            text-decoration: none;
        }
        .breadcrumbs li .active{
            color:red;
        }
        .breadcrumbs li+li:before{
            content:"/\00a0";
        }
        .image{
            background:url('{{asset('backend/img/background.jpg')}}');
            height:150px;
            background-position:center;
            background-attachment:cover;
            position: relative;
        }
        .image img{
            position: absolute;
            top:55%;
            left:35%;
            margin-top:30%;
        }
        i{
            font-size: 14px;
            padding-right:8px;
        }
    </style>
@endsection

