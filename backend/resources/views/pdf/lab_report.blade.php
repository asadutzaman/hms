<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Lab Report {{ $order->lab_order_no }}</title>
<style>
  body { font-family: sans-serif; margin: 24px; color: #222; font-size: 13px; }
  h1 { margin: 0 0 4px 0; font-size: 20px; }
  table { width: 100%; border-collapse: collapse; margin-top: 8px; }
  th, td { border-bottom: 1px solid #ddd; padding: 6px 8px; text-align: left; }
  th { background: #f4f4f4; }
  .right { text-align: right; }
  .section { margin-top: 20px; }
  .header-box { display: flex; justify-content: space-between; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 16px; }
  .critical { color: #c00; font-weight: bold; }
  .abnormal { font-weight: bold; }
  .test-title { background: #eef; padding: 4px 8px; margin-top: 14px; font-weight: bold; }
  .signoff { margin-top: 30px; font-size: 12px; color: #444; }
</style>
</head>
<body>
  <div class="header-box">
    <div>
      <h1>{{ config('app.name', 'HMS') }}</h1>
      <div>Laboratory Report</div>
    </div>
    <div style="text-align:right">
      <div><strong>Order No:</strong> {{ $order->lab_order_no }}</div>
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
    <div class="test-title">{{ $item->test_name_snapshot }}</div>
    <table>
      <thead>
        <tr><th>Parameter</th><th class="right">Result</th><th>Unit</th><th>Reference Range</th><th>Flag</th></tr>
      </thead>
      <tbody>
        @forelse ($item->results as $result)
          <tr>
            <td>{{ $result->parameter_name_snapshot }}</td>
            <td class="right {{ $result->result_flag && $result->result_flag !== 'normal' ? ($result->result_flag == 'critical_low' || $result->result_flag == 'critical_high' ? 'critical' : 'abnormal') : '' }}">
              {{ $result->result_value ?? '-' }}
            </td>
            <td>{{ $result->unit_snapshot ?? '-' }}</td>
            <td>{{ $result->reference_range_display ?? '-' }}</td>
            <td>{{ $result->result_flag ? strtoupper(str_replace('_', ' ', $result->result_flag)) : '-' }}</td>
          </tr>
        @empty
          <tr><td colspan="5">No results entered.</td></tr>
        @endforelse
      </tbody>
    </table>
  @endforeach

  <div class="signoff">
    Status: <strong>{{ ucfirst(str_replace('_', ' ', $order->order_status)) }}</strong><br>
    Generated on {{ now()->format('Y-m-d H:i') }}
  </div>
</body>
</html>
