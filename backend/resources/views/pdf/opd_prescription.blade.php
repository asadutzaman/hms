<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Prescription {{ $visit->opd_no ?? '' }}</title>
<style>
  body { font-family: sans-serif; margin: 24px; color: #222; font-size: 13px; }
  h1 { margin: 0 0 4px 0; font-size: 20px; }
  .muted { color: #666; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  th, td { border-bottom: 1px solid #ddd; padding: 6px 8px; text-align: left; }
  th { background: #f4f4f4; }
  .section { margin-top: 20px; }
  .header-box { display: flex; justify-content: space-between; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 16px; }
  .rx-symbol { font-size: 28px; font-weight: bold; margin-top: 12px; }
</style>
</head>
<body>
  <div class="header-box">
    <div>
      <h1>{{ config('app.name', 'HMS') }}</h1>
      <div class="muted">OPD Prescription</div>
    </div>
    <div style="text-align:right">
      <div><strong>OPD No:</strong> {{ $visit->opd_no ?? '-' }}</div>
      <div><strong>Date:</strong> {{ $prescription->prescribed_at ? \Carbon\Carbon::parse($prescription->prescribed_at)->format('Y-m-d H:i') : '-' }}</div>
    </div>
  </div>

  <table>
    <tr>
      <td><strong>Patient:</strong> {{ $patientName }}</td>
      <td><strong>MRN:</strong> {{ $patient->mrn ?? '-' }}</td>
      <td><strong>Age/Gender:</strong> {{ $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth)->age : '-' }} / {{ ucfirst($patient->gender ?? '-') }}</td>
    </tr>
    <tr>
      <td><strong>Doctor:</strong> {{ $doctorName }}</td>
      <td colspan="2"><strong>Department:</strong> {{ $departmentName }}</td>
    </tr>
  </table>

  <div class="rx-symbol">Rx</div>

  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Drug</th>
        <th>Dose</th>
        <th>Frequency</th>
        <th>Duration</th>
        <th>Route</th>
        <th>Instruction</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($items as $item)
      <tr>
        <td>{{ $item->sequence }}</td>
        <td>
          {{ $item->drug_name }}
          @if($item->strength) ({{ $item->strength }}) @endif
        </td>
        <td>{{ $item->dose_value ? rtrim(rtrim(number_format((float) $item->dose_value, 2), '0'), '.') . ' ' . $item->dose_unit : '-' }}</td>
        <td>{{ $item->frequency }}</td>
        <td>{{ $item->duration_value ? $item->duration_value . ' ' . $item->duration_unit : '-' }}</td>
        <td>{{ ucfirst($item->route) }}</td>
        <td>{{ $item->instruction ?? '-' }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  @if ($prescription->advice)
  <div class="section">
    <strong>Advice:</strong>
    <div>{{ $prescription->advice }}</div>
  </div>
  @endif

  @if ($prescription->follow_up_date)
  <div class="section">
    <strong>Follow-up:</strong> {{ \Carbon\Carbon::parse($prescription->follow_up_date)->format('Y-m-d') }}
  </div>
  @endif

  <div class="section muted">
    Generated on {{ now()->format('Y-m-d H:i') }}
  </div>
</body>
</html>
