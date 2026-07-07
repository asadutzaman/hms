<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Radiology Report {{ $order->rad_order_no }}</title>
<style>
  body { font-family: sans-serif; margin: 24px; color: #222; font-size: 13px; }
  h1 { margin: 0 0 4px 0; font-size: 20px; }
  .section { margin-top: 20px; }
  .header-box { display: flex; justify-content: space-between; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 16px; }
  .test-title { background: #eef; padding: 4px 8px; margin-top: 14px; font-weight: bold; }
  .label { font-weight: bold; }
  .signoff { margin-top: 30px; font-size: 12px; color: #444; }
</style>
</head>
<body>
  <div class="header-box">
    <div>
      <h1>{{ config('app.name', 'HMS') }}</h1>
      <div>Radiology Report</div>
    </div>
    <div style="text-align:right">
      <div><strong>Order No:</strong> {{ $order->rad_order_no }}</div>
      <div><strong>Date:</strong> {{ \Carbon\Carbon::parse($order->ordered_at)->format('Y-m-d H:i') }}</div>
    </div>
  </div>

  <p>
    Patient: <strong>{{ trim(($order->patient->first_name ?? '') . ' ' . ($order->patient->last_name ?? '')) ?: '-' }}</strong><br>
    MRN: <strong>{{ $order->patient->mrn ?? '-' }}</strong><br>
    Gender / Age: <strong>{{ ucfirst($order->patient->gender ?? '-') }} / {{ $order->patient->date_of_birth ? \Carbon\Carbon::parse($order->patient->date_of_birth)->age : '-' }}</strong><br>
    Priority: <strong>{{ ucfirst($order->priority) }}</strong>
  </p>

  @foreach ($order->items as $item)
    <div class="test-title">{{ $item->test_name_snapshot }} ({{ strtoupper($item->modality_snapshot ?? '-') }})</div>
    @if ($item->report)
      <div class="section">
        <div class="label">Findings:</div>
        <div>{{ $item->report->findings ?: '-' }}</div>
      </div>
      <div class="section">
        <div class="label">Impression:</div>
        <div>{{ $item->report->impression ?: '-' }}</div>
      </div>
    @else
      <div class="section">No report entered.</div>
    @endif
  @endforeach

  <div class="signoff">
    Status: <strong>{{ ucfirst(str_replace('_', ' ', $order->order_status)) }}</strong><br>
    Generated on {{ now()->format('Y-m-d H:i') }}
  </div>
</body>
</html>
