@extends('layouts.market')

@section('title', 'Chat — '.($vendor->shop_name ?? 'Seller'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'items' => [
            ['label' => $isVendorView ? __('Messages') : ($vendor->shop_name ?? __('Seller'))],
        ],
    ])
@endpush

@section('content')
    <h1 class="h4 mb-3">{{ $isVendorView ? 'Chat with '.$customerName : 'Chat with '.$vendor->shop_name }}</h1>
    <div class="zm-card p-3 mb-3" style="max-height:420px;overflow-y:auto;">
        @forelse($messages as $m)
            <div class="mb-2 {{ $m->sender_role === 'vendor' ? 'text-end' : '' }}">
                <span class="badge {{ $m->sender_role === 'vendor' ? 'bg-success' : 'bg-primary' }}">{{ $m->sender_role }}</span>
                <div class="small mt-1">{{ $m->body }}</div>
                <div class="text-muted" style="font-size:.7rem;">{{ $m->created_at->diffForHumans() }}</div>
            </div>
        @empty
            <p class="text-muted mb-0">Say hello to start the conversation.</p>
        @endforelse
    </div>
    <form method="post" action="{{ route('chat.store', $vendor) }}" class="d-flex gap-2">
        @csrf
        @if($isVendorView)
            <input type="hidden" name="customer_user_id" value="{{ $customerUserId }}">
        @endif
        <input class="form-control" name="body" placeholder="Type a message" required>
        <button class="zm-btn zm-btn-primary" type="submit">Send</button>
    </form>
@endsection
