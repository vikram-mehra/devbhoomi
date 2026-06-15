<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Label - {{ $order->order_number }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Libre+Barcode+39&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-page: #0f172a;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #475569;
            --border-color: #cbd5e1;
            --accent-cod: #dc2626;
            --accent-prepaid: #16a34a;
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3), 0 8px 10px -6px rgba(0, 0, 0, 0.3);
        }

        /* Basic Setup */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Outfit', 'Inter', sans-serif;
            background-color: var(--bg-page);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow-x: hidden;
        }

        /* Screen Controls UI */
        .controls {
            margin-bottom: 24px;
            display: flex;
            gap: 12px;
            z-index: 10;
            animation: fadeIn 0.4s ease-out;
        }
        .btn {
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
        }
        .btn-secondary {
            background-color: #334155;
            color: #f1f5f9;
            border: 1px solid #475569;
        }
        .btn-secondary:hover {
            background-color: #475569;
            transform: translateY(-2px);
        }

        /* Label Container (Digital Preview Style) */
        .label-outer-container {
            width: 100%;
            max-width: 450px;
            perspective: 1000px;
            animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .label-card {
            background-color: var(--bg-card);
            color: var(--text-main);
            width: 100%;
            aspect-ratio: 4/6; /* Mimics 4x6 inch label */
            border-radius: 12px;
            box-shadow: var(--shadow);
            padding: 24px;
            border: 2px dashed var(--border-color);
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .label-card:hover {
            transform: translateY(-4px) scale(1.01);
        }

        /* Decorative Background elements for Preview screen */
        .glow-bg {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(59,130,246,0.1) 0%, rgba(0,0,0,0) 70%);
            top: 20%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1;
            pointer-events: none;
        }

        /* Label Sections */
        .label-section {
            border-bottom: 2px solid #000;
            padding-bottom: 12px;
            margin-bottom: 12px;
        }
        .label-section:last-child {
            border-bottom: none;
            padding-bottom: 0;
            margin-bottom: 0;
        }

        /* Header block */
        .label-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo-placeholder {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }
        .label-title-badge {
            background-color: #000;
            color: #fff;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }

        /* From / To block */
        .address-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }
        .address-box {
            font-size: 12px;
            line-height: 1.4;
        }
        .address-box.to {
            font-size: 14px;
        }
        .address-box.to strong.name {
            font-size: 16px;
            font-weight: 700;
        }
        .address-label {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 800;
            color: #64748b;
            border: 1px solid #64748b;
            padding: 1px 4px;
            display: inline-block;
            border-radius: 3px;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }
        
        /* Barcode container */
        .barcode-container {
            text-align: center;
            padding: 10px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .barcode-visual {
            font-family: 'Libre Barcode 39', cursive;
            font-size: 56px;
            line-height: 1;
            margin-bottom: 4px;
            letter-spacing: 2px;
        }
        .barcode-text {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 3px;
        }

        /* Bottom Row Metadata */
        .meta-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .meta-item {
            font-size: 11px;
            line-height: 1.3;
        }
        .meta-item strong {
            font-size: 12px;
            display: block;
            margin-top: 2px;
        }
        .payment-status-badge {
            font-size: 14px;
            font-weight: 800;
            padding: 6px 12px;
            text-align: center;
            border-radius: 6px;
            text-transform: uppercase;
            display: inline-block;
            margin-top: 4px;
            border: 2px solid #000;
        }
        .payment-status-badge.cod {
            background-color: #000;
            color: #fff;
        }
        .payment-status-badge.prepaid {
            background-color: #fff;
            color: #000;
        }

        /* Packing Slip List (Mini checklist) */
        .packing-slip {
            background-color: #f8fafc;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 8px;
            font-size: 10px;
        }
        .packing-slip-title {
            font-size: 9px;
            text-transform: uppercase;
            font-weight: 800;
            color: #64748b;
            margin-bottom: 4px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 2px;
        }
        .packing-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }
        .packing-item:last-child {
            margin-bottom: 0;
        }

        /* Print Button / Footer in layout */
        .footer-note {
            margin-top: 24px;
            font-size: 12px;
            color: #64748b;
            text-align: center;
            animation: fadeIn 0.6s ease-out;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Print Media Overrides */
        @media print {
            body {
                background-color: #ffffff !important;
                color: #000000 !important;
                padding: 0 !important;
                min-height: 0 !important;
                display: block !important;
            }
            .controls, .footer-note, .glow-bg {
                display: none !important;
            }
            .label-outer-container {
                max-width: 100% !important;
                width: 4in !important;
                height: 6in !important;
                margin: 0 auto !important;
                page-break-inside: avoid;
            }
            .label-card {
                box-shadow: none !important;
                border: 2px solid #000 !important;
                border-radius: 0 !important;
                width: 4in !important;
                height: 6in !important;
                padding: 0.25in !important;
                page-break-inside: avoid;
                transform: none !important;
            }
            .packing-slip {
                background-color: #ffffff !important;
                border: 1px solid #000 !important;
            }
            @page {
                size: 4in 6in;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="glow-bg"></div>

    <div class="controls">
        <button class="btn btn-primary" onclick="window.print()">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2m-2-9v11H8V9z"/></svg>
            Print Label
        </button>
        <button class="btn btn-secondary" onclick="window.close()">
            Close Window
        </button>
    </div>

    <div class="label-outer-container">
        <div class="label-card">
            <!-- Header section -->
            <div class="label-section">
                <div class="label-header">
                    <span class="logo-placeholder">{{ $company['name'] }}</span>
                    <span class="label-title-badge">Shipping Label</span>
                </div>
            </div>

            <!-- Address Grid: From and To -->
            <div class="label-section" style="display: grid; grid-template-columns: 1fr; gap: 10px;">
                <div class="address-box">
                    <span class="address-label">FROM</span><br>
                    <strong>{{ $company['name'] }}</strong><br>
                    {{ $company['address'] }}<br>
                    Phone: {{ $company['phone'] }}
                </div>
                <div class="address-box to" style="border-top: 1px dashed #000; padding-top: 8px;">
                    <span class="address-label">SHIP TO</span><br>
                    <strong class="name">{{ $order->customer_name ?: ($order->shippingAddress->name ?? $order->user->name ?? 'N/A') }}</strong><br>
                    {{ $order->shippingAddress?->fullAddress() ?: 'N/A' }}<br>
                    <strong>Phone: {{ $order->customer_phone ?: ($order->shippingAddress->phone ?? 'N/A') }}</strong>
                </div>
            </div>

            <!-- Barcode representation -->
            <div class="label-section barcode-container">
                <div class="barcode-visual">*{{ $order->order_number }}*</div>
                <div class="barcode-text">ORDER #{{ $order->order_number }}</div>
            </div>

            <!-- Package & Shipping Meta Info -->
            <div class="label-section meta-row">
                <div class="meta-item">
                    <span>Carrier / Method</span>
                    <strong>{{ $order->courier_name ?: 'Standard Delivery' }}</strong>
                    @if($order->tracking_id)
                        <span style="display: block; margin-top: 4px; font-size: 10px; color: var(--text-muted);">
                            Tracking: <strong>{{ $order->tracking_id }}</strong>
                        </span>
                    @endif
                </div>
                <div class="meta-item" style="text-align: right;">
                    <span>Weight / Pieces</span>
                    <strong>{{ $totalWeight > 0 ? number_format($totalWeight, 2) . ' kg' : 'N/A' }} / {{ $order->items->sum('qty') }} pcs</strong>
                </div>
            </div>

            <!-- Payment Badge & Cash collect Info -->
            <div class="label-section" style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 8px;">
                <div>
                    <span style="font-size: 10px; text-transform: uppercase; color: var(--text-muted);">Payment Method</span>
                    <br>
                    <strong style="font-size: 13px;">{{ strtoupper((string) $order->payment_method) }}</strong>
                </div>
                <div>
                    @if(strtolower((string)$order->payment_method) === 'cod')
                        <div class="payment-status-badge cod">COD</div>
                        <div style="font-size: 11px; font-weight: 700; text-align: right; margin-top: 2px;">
                            Collect: ₹{{ number_format((float)$order->payableAmount(), 2) }}
                        </div>
                    @else
                        <div class="payment-status-badge prepaid">PREPAID</div>
                        <div style="font-size: 11px; font-weight: 700; text-align: right; margin-top: 2px; color: var(--accent-prepaid);">
                            Amount Paid
                        </div>
                    @endif
                </div>
            </div>

            <!-- Packing Checklist -->
            <div class="label-section" style="border-bottom: none; margin-bottom: 0; padding-bottom: 0;">
                <div class="packing-slip">
                    <div class="packing-slip-title">Package Contents (Items Checklist)</div>
                    @foreach($order->items as $item)
                        <div class="packing-item">
                            <span>{{ \Illuminate\Support\Str::limit($item->product_name, 35) }}</span>
                            <strong>Qty: {{ $item->qty }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="footer-note">
        Press <strong>Ctrl + P</strong> or <strong>Cmd + P</strong> if the print dialog doesn't load automatically.
    </div>
</body>
</html>
