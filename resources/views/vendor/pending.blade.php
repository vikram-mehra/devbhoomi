@extends('layouts.vendor')

@section('title', 'Pending approval')

@section('content')
    <h1 class="h4 mb-2">{{ __('Pending approval') }}</h1>
    <p class="text-muted small mb-0">{{ __('You will be able to manage products and orders after an admin approves your shop.') }}</p>
@endsection
