<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Receipt {{ $bill->bill_no }}</title>
<style>
  body { font-family: sans-serif; margin: 24px; color: #222; font-size: 13px; }
  h1 { margin: 0 0 4px 0; font-size: 20px; }
  table { width: 100%; border-collapse: collapse; margin-top: 8px; }
  th, td { border-bottom: 1px solid #ddd; padding: 6px 8px; text-align: left; }
  th { background: #f4f4f4; }
  .right { text-align: right; }
  .totals td { font-weight: bold; }
  .section { margin-top: 20px; }
  .header-box { display: flex; justify-content: space-between; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 16px; }
</style>
</head>
<body>
  <div class="header-box">
    <div>
      <h1>{{ config('app.name', 'HMS') }}</h1>
      <div>IPD Invoice / Receipt</div>
    </div>
    <div style="text-align:right">
      <div><strong>Bill No:</strong> {{ $bill->bill_no }}</div>
      <div><strong>Date:</strong> {{ $bill->billed_at ? \Carbon\Carbon::parse($bill->billed_at)->format('Y-m-d H:i') : '-' }}</div>
    </div>
  </div>

  <p>
    Patient: <strong>{{ trim(($bill->admission->patient->first_name ?? '') . ' ' . ($bill->admission->patient->last_name ?? '')) ?: '-' }}</strong><br>
    Admission No: <strong>{{ $bill->admission->admission_no ?? '-' }}</strong><br>
    Ward / Bed: <strong>{{ $bill->admission->ward->name ?? '-' }} / {{ $bill->admission->bed->bed_number ?? '-' }}</strong><br>
    Attending Doctor: <strong>{{ $bill->admission->attendingDoctor->name_en ?? '-' }}</strong><br>
    Status: <strong>{{ ucfirst(str_replace('_', ' ', $bill->bill_status)) }}</strong>
  </p>

  <div class="section">
    <h3>Line Items</h3>
    <table>
      <thead><tr><th>Type</th><th>Description</th><th class="right">Qty</th><th class="right">Unit</th><th class="right">Amount</th></tr></thead>
      <tbody>
        @foreach ($bill->items as $item)
        <tr>
          <td>{{ ucfirst(str_replace('_', ' ', $item->item_type)) }}</td>
          <td>{{ $item->description }}</td>
          <td class="right">{{ $item->quantity }}</td>
          <td class="right">{{ number_format((float) $item->unit_price, 2) }}</td>
          <td class="right">{{ number_format((float) $item->line_total, 2) }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="section">
    <h3>Totals</h3>
    <table class="totals">
      <tr><td>Subtotal</td><td class="right">{{ number_format((float) $bill->subtotal, 2) }}</td></tr>
      <tr><td>Discount</td><td class="right">{{ number_format((float) $bill->discount, 2) }}</td></tr>
      <tr><td>Tax</td><td class="right">{{ number_format((float) $bill->tax, 2) }}</td></tr>
      <tr><td>Total</td><td class="right">{{ number_format((float) $bill->total, 2) }}</td></tr>
      <tr><td>Paid</td><td class="right">{{ number_format((float) $bill->paid, 2) }}</td></tr>
      <tr><td>Balance</td><td class="right">{{ number_format((float) $bill->balance, 2) }}</td></tr>
    </table>
  </div>

  <div class="section">
    <h3>Payments</h3>
    <table>
      <thead><tr><th>Method</th><th>Date</th><th class="right">Amount</th><th>Reference</th></tr></thead>
      <tbody>
        @foreach ($bill->payments as $p)
        <tr>
          <td>{{ ucfirst($p->payment_method) }}</td>
          <td>{{ $p->paid_at }}</td>
          <td class="right">{{ number_format((float) $p->amount, 2) }}</td>
          <td>{{ $p->reference_no ?? '-' }}</td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <div class="section">
    Generated on {{ now()->format('Y-m-d H:i') }}
  </div>
</body>
</html>
