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
        <p style="font-size: 17px">{{ $data[0]['name'] }}</p>
        <p style="font-size: 14px">
            Company: {{ $data[0]['company'] }} <br>
            Email: {{ $data[0]['email'] }} <br>
            Contact: {{ $data[0]['phone_no'] }} <br>
            <b>Purchased On: </b>{{ \Carbon\Carbon::parse($data[0]['confirmation_date'])->format('d M, Y') }}
        </p>
    </div>
    <div style="float: right">
        <img style="height: 50px" src="{{ public_path('uploads/general/logo.jpg') }}">
        <hr>
    </div>
</section>

<div style="background-color: darkred; height: 1px; width: 100%; margin-top: 220px"></div>

<section style="margin-top: 50px">
    <table>
        <tr style="background-color: #374151;color: #fff;">
            <td>Invoice No</td>
            <td>Payment Type</td>
            <td>Amount(Tk)</td>
            <td>Date</td>
        </tr>
        @foreach($data as $item)
            <tr class="table_body">
                <td style="color: #000">{{ $item['invoice_no'] }}</td>
                <td>
                    {{ $item['payment_type'] }}
                    @if($item['payment_type'] != 'Cash')
                        <br>
                        <span>{{ $item['transaction_id'] }}</span>
                    @endif
                </td>
                <td>{{ $item['amount'] }}/-</td>
                <td>{{ \Carbon\Carbon::parse($item['occurred_on'])->format('d M, Y') }}</td>
            </tr>
        @endforeach

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
