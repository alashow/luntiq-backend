@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">@lang('auth.verify.header')</div>

                <div class="card-body">
                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            @lang('auth.verify.sent')
                        </div>
                    @endif

                    @lang('auth.verify.body', ['resent' => route('verification.resend')])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
