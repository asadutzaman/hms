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
                <td colspan="9">Drug Expiry Report</td>
            </tr>

            <tr>
                <th>Serial No.</th>
                <th>Generic Name</th>
                <th>Brand Name</th>
                <th>Dosage Form</th>
                <th>Item Code</th>
                <th>Branch</th>
                <th>Balance Quantity</th>
                <th>Expiry Date</th>
                <th>Days Left</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($results as $key => $itemData)
                <tr>
                    <td>{{ ++$key }}</td>
                    <td>{{ $itemData['generic_name'] }}</td>
                    <td>{{ $itemData['brand_name'] }}</td>
                    <td>{{ $itemData['dosage_form'] }}</td>
                    <td>{{ $itemData['item_code'] }}</td>
                    <td>{{ $itemData['branch_name'] }}</td>
                    <td>{{ $itemData['balance_quantity'] }}</td>
                    <td>{{ $itemData['expire_date'] }}</td>
                    <td>{{ $itemData['days_left'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
