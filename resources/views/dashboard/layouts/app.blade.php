@extends('layouts.admin')

@section('page_title')
    @if (View::hasSection('page_title'))
        @yield('page_title')
    @elseif (View::hasSection('page-title'))
        @yield('page-title')
    @elseif (View::hasSection('title'))
        @yield('title')
    @endif
@endsection

@section('page_subtitle')
    @if (View::hasSection('page_subtitle'))
        @yield('page_subtitle')
    @elseif (View::hasSection('page-subtitle'))
        @yield('page-subtitle')
    @endif
@endsection

@section('page_description')
    @if (View::hasSection('page_description'))
        @yield('page_description')
    @elseif (View::hasSection('page-description'))
        @yield('page-description')
    @endif
@endsection

@section('page_actions')
    @if (View::hasSection('page_actions'))
        @yield('page_actions')
    @elseif (View::hasSection('page-actions'))
        @yield('page-actions')
    @endif
@endsection

@section('page_toolbar')
    @if (View::hasSection('page_toolbar'))
        @yield('page_toolbar')
    @elseif (View::hasSection('page-toolbar'))
        @yield('page-toolbar')
    @endif
@endsection

@section('stat_cards')
    @if (View::hasSection('stat_cards'))
        @yield('stat_cards')
    @elseif (View::hasSection('stat-cards'))
        @yield('stat-cards')
    @endif
@endsection

@section('quick_filters')
    @if (View::hasSection('quick_filters'))
        @yield('quick_filters')
    @elseif (View::hasSection('quick-filters'))
        @yield('quick-filters')
    @endif
@endsection

@section('content')
    @yield('content')
@endsection

