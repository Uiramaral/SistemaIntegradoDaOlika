@extends('layouts.admin')

@section('page_title')
    @hasSection('page-title')
        @yield('page-title')
    @endif
@endsection

@section('page_subtitle')
    @hasSection('page-subtitle')
        @yield('page-subtitle')
    @endif
@endsection

@section('page_description')
    @hasSection('page-description')
        @yield('page-description')
    @endif
@endsection

@section('page_actions')
    @hasSection('page-actions')
        @yield('page-actions')
    @endif
@endsection

@section('page_toolbar')
    @hasSection('page-toolbar')
        @yield('page-toolbar')
    @endif
@endsection

@section('stat_cards')
    @hasSection('stat-cards')
        @yield('stat-cards')
    @endif
@endsection

@section('quick_filters')
    @hasSection('quick-filters')
        @yield('quick-filters')
    @endif
@endsection

@section('content')
    @yield('content')
@endsection

