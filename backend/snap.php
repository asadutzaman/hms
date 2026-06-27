<?php
$prefix = config('database.connections.pgsql_local_server.prefix');
$rows = DB::select("select indexname, indexdef from pg_indexes where schemaname='public' and tablename = ? and indexname like '%active%'", [$prefix . 'opd_visits']);
foreach ($rows as $r) {
    echo $r->indexname . PHP_EOL . '  ' . $r->indexdef . PHP_EOL;
}
echo '---' . PHP_EOL;
$tables = ['opd_vitals','opd_diagnoses','opd_prescriptions','opd_prescription_items','opd_investigation_order_items','opd_bill_items','opd_bill_payments','opd_visit_audit_logs'];
foreach ($tables as $t) {
    echo str_pad($t, 38) . ' has_status=' . (Schema::hasColumn($t,'status') ? 'YES' : 'NO') . PHP_EOL;
}
