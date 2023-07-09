<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>

    <link rel="stylesheet"
          href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T"
          crossorigin="anonymous">
</head>

<body>

<table class="table table-striped">
    <tr>
        <th>Company</th>
        <th>Client Name</th>
        <th>Email</th>
        <th>Phone No</th>
        <th>Area</th>
        <th>Product Type</th>
        <th>Interest (%)</th>
        <th>Confirmed On</th>
        <th>Added By</th>
        <th>Added On</th>
    </tr>
    @foreach($client_list as $g)
        <tr>
            <td>{{ $g['company'] }}</td>
            <td>{{ $g['name'] }}</td>
            <td>{{ $g['email'] }}</td>
            <td>{{ $g['phone_no'] }}</td>
            <td>{{ $g['area'] }}</td>
            <td>{{ $g['product_type'] }}</td>
            <td>{{ $g['interest_status'] }}%</td>
            <td>{{ $g['confirmation_date'] }}</td>
            <td>{{ $g['added_by'] }}</td>
            <td>{{ $g['created_at'] }}</td>
        </tr>
    @endforeach
</table>

</body>
</html>
