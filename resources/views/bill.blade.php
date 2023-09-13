<html>
<head>
    <title>Client Bill</title>
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
            <b>Issued On: </b>{{ \Carbon\Carbon::parse($data['created_at'])->format('d/m/y H:i') }}
        </p>
    </div>
    <div style="float: right">
        <img style="height: 80px" src="{{ public_path('uploads/general/logo.jpg') }}">
        <hr>
        <p style="float: right">
            <b>Bill No: {{ $data['bill_no'] }}</b>
        </p>
    </div>
</section>

<div style="background-color: darkred; height: 1px; width: 100%; margin-top: 220px"></div>

<section style="margin-top: 50px">
    <table>
        <tr style="background-color: #374151;color: #fff;">
            <td>Service</td>
            <td>Amount(Tk)</td>
        </tr>
        @php $total = 0 @endphp
        @foreach($data['services'] as $item)
            <tr class="table_body">
                <td style="color: #000">{{ $item['service']['name'] }}
                    @if($item['service']['status'] == 0)
                        <span style="color: #74787e"> (currently not available)</span>
                    @endif
                </td>
                <td>{{ $item['service']['price'] }}/=</td>
            </tr>
            @php
                $total += $item['service']['price']
            @endphp
        @endforeach
    </table>
    <div style="background-color: #374151; height: 1px; width: 100%;"></div>
    <p>Total: {{ $total }}</p>
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
