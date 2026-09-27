@extends('adminlte::page')

@section('title')
    @hasSection('meta_title')
        @yield('meta_title') |
    @endif

    {{ config('adminlte.title') }}
@stop

@section('meta_tags')
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('meta')
@stop

@section('content_header')
    @if (isset($title) || isset($breadcrumb))
        <div class="container-fluid">
            <div class="row mb-1 align-items-center">
                <div class="col-md-6 col-12 text-center text-md-left">
                    @isset($title)
                        <h1 class="m-0 text-dark font-weight-bold">
                            <i class="fas fa-layer-group text-primary mr-2"></i>

                            {{ $title }}

                            @isset($sub_title)
                                <small class="text-muted font-weight-light ml-md-2">
                                    {{ $sub_title }}
                                </small>
                            @endisset
                        </h1>
                    @endisset
                </div>

                <div class="col-md-6 col-12 mt-3 mt-md-0">
                    @if (!empty($breadcrumb))
                        <nav aria-label="breadcrumb">
                            <ol
                                class="breadcrumb float-md-right shadow-sm border-0 px-3 py-2 bg-white rounded-pill"
                            >
                                <li class="breadcrumb-item">
                                    <a
                                        href="{{ route('admin.dashboard') }}"
                                        class="text-primary"
                                        aria-label="Admin dashboard"
                                    >
                                        <i class="fas fa-home"></i>
                                    </a>
                                </li>

                                @foreach ($breadcrumb as $crumb)
                                    @if (!empty($crumb['url']))
                                        <li class="breadcrumb-item">
                                            <a
                                                href="{{ $crumb['url'] }}"
                                                class="text-muted font-weight-bold"
                                            >
                                                {{ $crumb['text'] }}
                                            </a>
                                        </li>
                                    @else
                                        <li
                                            class="breadcrumb-item active text-secondary"
                                            aria-current="page"
                                        >
                                            {{ $crumb['text'] }}
                                        </li>
                                    @endif
                                @endforeach
                            </ol>
                        </nav>
                    @endif
                </div>
            </div>
        </div>
    @endif
@stop

@section('content')
    @yield('page_content')
@stop

@section('footer')
    @include('backoffice.admin.includes.footer')
@stop

@push('css')
    @include('backoffice.admin.includes.custom_css')
    @stack('page_css')
@endpush

@push('js')
    @include('backoffice.admin.includes.custom_js')
    @stack('page_js')
@endpush