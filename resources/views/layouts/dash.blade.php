@extends('layouts.dashboard')

@section('page_title')
    @hasSection('page_title')
        @yield('page_title')
    @elseif (View::hasSection('page-title'))
        @yield('page-title')
    @endif
@endsection

@section('content')
    @yield('content')
@endsection

