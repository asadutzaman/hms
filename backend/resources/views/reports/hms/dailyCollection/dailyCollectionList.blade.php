<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <table>
        <thead>
            <tr className='text-center'>
                <td colspan="7">Daily Collection Report</td>
            </tr>

            <tr>
                <th>Serial No.</th>
                <th>Date</th>
                <th>Department</th>
                <th>Doctor</th>
                <th>Payment Method</th>
                <th>Transactions</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($results as $key => $row)
                <tr>
                    <td>{{ ++$key }}</td>
                    <td>{{ $row['collection_date'] }}</td>
                    <td>{{ $row['department_name'] }}</td>
                    <td>{{ $row['doctor_name'] }}</td>
                    <td>{{ $row['payment_method'] }}</td>
                    <td>{{ $row['transaction_count'] }}</td>
                    <td>{{ $row['total_amount'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
