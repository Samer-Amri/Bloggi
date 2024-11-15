@extends('layouts.app')
@section('content')
    <!-- Start Activation Email -->
    <div class="col-lg-9 col-12">
        <div class="email-page">
            <!-- Start Email Content -->
            <article class="email__content d-flex flex-wrap">
                <div class="content">
                    <h1>Hello, {{ $user->name }}</h1>
                    <p>Thank you for registering. Please click the link below to activate your account:</p>
                    <div class="email__btn">
                        <a href="{{ $activationLink }}">Activate Account</a>
                    </div>
                </div>
            </article>
            <!-- End Email Content -->
        </div>
    </div>
    <!-- End Activation Email -->
@endsection
