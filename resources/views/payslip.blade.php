<html>
<head>
    <title>Client Payslip</title>
    <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        td, th {
            text-align: left;
            padding: 10px;
        }
    </style>
</head>
<body>
<section>
    <div style="float: left; margin-top: 41px">
        <b style="font-size: 20px">Issued To:</b>
        <p style="font-size: 17px">{{ $data['client']['name'] }}</p>
        <p style="font-size: 14px">
            Company: {{ $data['client']['company'] }} <br>
            Email: {{ $data['client']['email'] }} <br>
            Contact: {{ $data['client']['phone_no'] }} <br>
            <b>Purchased On: </b>{{ $data['client']['confirmation_date'] }}
        </p>
    </div>
    <div style="float: right">
        <img style="height: 80px" src="{{ public_path('uploads/general/logo.jpg') }}">
        <hr>
        <p style="float: right">
            <b>Invoice No: {{ $data['invoice_no'] }}</b>
        </p>
        <br><br>
        <p style="float: right;font-size: 14px">
            <span style="font-size: 16px;font-weight: 600">{{ $data['category']['name'] }}</span> <br>
            Payment Method: {{ $data['type']['name'] }} <br>
            @if($data['transaction_id'] != null)
                Transaction No: {{ $data['transaction_id'] }}
            @endif
        </p>
    </div>
</section>

<div style="background-color: darkred; height: 1px; width: 100%; margin-top: 220px"></div>

<section style="margin-top: 50px">
    <table>
        <tr style="background-color: #374151;color: #fff;">
            <td>Product Category</td>
            <td>Amount(Tk)</td>
        </tr>

        <tr class="table_body">
            <td style="color: #000">{{ $data['client']['product_type'] }}</td>
            <td>{{ $data['amount'] }}/=</td>
        </tr>
    </table>
    <div style="background-color: #374151; height: 1px; width: 100%;"></div>
</section>
<section style="margin-top: 150px">
    <div>
        <p style="border-top: 1px solid #000; width: 300px"> Authorized by</p>
    </div>
</section>
<section style="position: absolute; bottom: 0; width: 100%">
    <div style="background-color: darkred; height: 1px; width: 100%"></div>
    <p>Email: hello@selopia.com<span></span><br>Contact: (+880) 963-8884444</p>
</section>
</body>
</html>
