@extends('layouts.admin')

@section('title', __('Courier Partners'))
@section('page_subtitle', __('Enable partners in .env. Credentials stay in environment variables, not in the database.'))

@section('content')
    <div class="card border-0 shadow-sm admin-data-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0 admin-table">
                <thead>
                    <tr>
                        <th>Courier</th>
                        <th>Key</th>
                        <th>Enabled</th>
                        <th>Default</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($partners as $key => $partner)
                        <tr>
                            <td class="fw-semibold">{{ $partner['name'] }}</td>
                            <td><code>{{ $key }}</code></td>
                            <td>
                                @if($partner['enabled'])
                                    <span class="badge bg-success">ON</span>
                                @else
                                    <span class="badge bg-secondary">OFF</span>
                                @endif
                            </td>
                            <td>
                                @if($default === $key)
                                    <span class="badge bg-primary">Default</span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white small text-muted">
            Toggle partners with <code>COURIER_DELHIVERY_ENABLED</code> and <code>COURIER_BLUEDART_ENABLED</code>.
            Set API credentials in <code>.env</code> only. See <code>docs/courier-integration.md</code>.
        </div>
    </div>
@endsection
