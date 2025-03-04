@extends('layouts.app')
@section('content')
    <section class="my_account_area pt--80 pb--55 bg--white">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">{{ __('Frontend/auth.verify_email') }}</div>
                            <div class="card-body">
                                {{ __('Frontend/auth.please_check_email') }}
                                {{ __('Frontend/auth.if_not_received') }},
                                <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-link p-0 m-0 align-baseline">{{ __('Frontend/auth.request_another') }}</button>.
                                </form>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
