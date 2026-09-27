@extends('backoffice.admin.layouts.app')

@section('meta_title', 'Dashboard')

@section('page_content')
    <div class="card card-outline card-primary">
        <div class="card-body">
            <p class="mb-0">Welcome, {{ $admin->name ?? 'Admin' }}.</p>
        </div>
    </div>
@endsection
