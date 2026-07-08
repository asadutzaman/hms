<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Claim {{ $claim->claim_no }}</title>
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
  .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; background: #eef; }
</style>
</head>
<body>
  <div class="header-box">
    <div>
      <h1>{{ config('app.name', 'HMS') }}</h1>
      <div>Insurance / TPA Claim</div>
    </div>
    <div style="text-align:right">
      <div><strong>Claim No:</strong> {{ $claim->claim_no }}</div>
      <div><strong>Status:</strong> <span class="badge">{{ ucfirst(str_replace('_', ' ', $claim->claim_status)) }}</span></div>
      <div><strong>Submitted:</strong> {{ $claim->submitted_at ? \Carbon\Carbon::parse($claim->submitted_at)->format('Y-m-d H:i') : '-' }}</div>
    </div>
  </div>

  <p>
    Patient: <strong>{{ trim(($claim->patient->first_name ?? '') . ' ' . ($claim->patient->last_name ?? '')) ?: '-' }}</strong>
    (MRN: {{ $claim->patient->mrn ?? '-' }})<br>
    Insurer: <strong>{{ $claim->insuranceCompany->name ?? '-' }}</strong><br>
    Scheme: <strong>{{ $claim->insuranceScheme->name ?? '-' }}</strong><br>
    Policy Number: <strong>{{ $claim->policy_number ?? '-' }}</strong><br>
    @if ($claim->preAuthorization)
    Pre-Authorization No: <strong>{{ $claim->preAuthorization->pre_auth_no ?? $claim->preAuthorization->id }}</strong><br>
    @endif
    Bill Reference: <strong>{{ strtoupper(str_replace('_', ' ', $claim->billable_type)) }} #{{ $bill->bill_no ?? $claim->billable_id }}</strong>
  </p>

  <div class="section">
    <h3>Billed Line Items</h3>
    <table>
      <thead><tr><th>Type</th><th>Description</th><th class="right">Qty</th><th class="right">Unit</th><th class="right">Amount</th></tr></thead>
      <tbody>
        @foreach (($bill->items ?? []) as $item)
        <tr>
          <td>{{ ucfirst($item->item_type) }}</td>
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
    <h3>Claim Summary</h3>
    <table class="totals">
      <tr><td>Bill Total</td><td class="right">{{ number_format((float) ($bill->total ?? 0), 2) }}</td></tr>
      <tr><td>Claimed Amount</td><td class="right">{{ number_format((float) $claim->claimed_amount, 2) }}</td></tr>
      <tr><td>Approved Amount</td><td class="right">{{ $claim->approved_amount !== null ? number_format((float) $claim->approved_amount, 2) : '-' }}</td></tr>
    </table>
  </div>

  @if ($claim->notes)
  <div class="section">
    <h3>Notes</h3>
    <p>{{ $claim->notes }}</p>
  </div>
  @endif
</body>
</html>
