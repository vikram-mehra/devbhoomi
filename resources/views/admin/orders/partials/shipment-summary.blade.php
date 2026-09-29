@php
    $summary = $summary ?? [];
    $results = $summary['results'] ?? [];
@endphp
<div class="alert {{ !empty($summary['queued']) ? 'alert-info' : (($summary['failed'] ?? 0) > 0 ? 'alert-warning' : 'alert-success') }} border-0 shadow-sm mb-3">
    <div class="fw-semibold mb-1">
        @if(!empty($summary['queued']))
            Shipment creation has been queued.
        @else
            Shipment Creation Completed
        @endif
    </div>
    <div class="small mb-2">
        Total Orders: {{ $summary['total'] ?? 0 }}
        @if(empty($summary['queued']))
            &nbsp;· Successful: {{ $summary['successful'] ?? 0 }}
            &nbsp;· Failed: {{ $summary['failed'] ?? 0 }}
        @endif
        @if(!empty($summary['courier']))
            &nbsp;· Courier: {{ $summary['courier'] }}
        @endif
    </div>
    @if($results !== [])
        <div class="table-responsive">
            <table class="table table-sm mb-0 bg-white">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Status</th>
                        <th>AWB</th>
                        <th>Reason / cURL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($results as $row)
                        <tr>
                            <td>{{ $row['order_number'] ?? $row['order_id'] }}</td>
                            <td>{{ !empty($row['success']) ? 'Success' : ($row['status'] ?? 'Failed') }}</td>
                            <td>{{ $row['awb'] ?: '—' }}</td>
                            <td>
                                <div>{{ $row['message'] ?: (!empty($row['success']) ? 'Success' : '—') }}</div>
                                @if(!empty($row['debug_curl']))
                                    <pre class="small mb-0 mt-2 p-2 bg-dark text-white rounded" style="white-space:pre-wrap;word-break:break-word;max-height:28rem;overflow:auto;">{{ $row['debug_curl'] }}</pre>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
