@extends('layouts.dashboardLayouts')

@section('content')
    @include('partials.backendHeader')
    @include('partials.backendNav')
    <livewire:edit-factsheet-component :id="$idFactsheet" />
@endsection
