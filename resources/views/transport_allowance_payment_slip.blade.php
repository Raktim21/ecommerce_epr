<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Order confirmation</title>
    <meta name="robots" content="noindex,nofollow" />
    <meta name="viewport" content="width=device-width; initial-scale=1.0;" />
    <style type="text/css">
        @import url("https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap");

        body {
            margin: 0;
            padding: 0;
            background: #cacaca;
            font-family: "Poppins", sans-serif;
        }

        input:focus {
            outline: none;
        }
    </style>
</head>

<body>
    <main style="background: white;width: 600px;height: 85vh;margin: 5vh auto;border-radius: 0px; padding: 20px;">

        <table width="100%" align="center" style="margin-bottom: 50px;">
            <tr>
                <td>
                    <img src="https://i.ibb.co/YDr2SKt/logo-png.png" width="100" height="100" alt="logo"
                        border="0" />
                </td>
                <td style="padding-left: 20px">
                    <table>
                        <tr>
                            <td width="400" align="left">
                                <h2 style="margin: 0; color: #e63c3c">Selopia</h2>
                            </td>
                            <td width="400" align="right">
                                <button style="border: none; padding: 5px; background: #c51e1e; color: white;">
                                    DEBIT VOUCHER
                                </button>
                            </td>
                        </tr>
                    </table>
                    <p style="padding: 0; margin: 0; line-height: 16px; font-size: 14px">
                        Head Office : 704,Concord Tower,113 Kazi Nazrul Islam Ave,Dhaka -
                        1205 Registered Address : 5B Navana Zohura Square,28 Mymensingh
                        Road,Dhaka-1205 Phone : +88 0963 888 4444, Email :
                        hello@selopia.com, Website : www.selopia.com
                    </p>
                </td>
            </tr>
        </table>

        <table width="100%" align="center" style="margin-bottom: 50px;">
            <tr>
                <td width="300">
                    Voucher No : {{ $data['user']->id }}-{{ rand(10000, 99999) }}
                </td>
                <td width="150"></td>
                <td width="150">Date : {{ \Carbon\Carbon::now()->format('d M, Y') }}
                </td>
            </tr>
        </table>

        <table>
            <tr>
                <td width="300">Name : {{ $data['user']->name }}</td>
                <td width="300">Email : {{ $data['user']->email }}</td>
            </tr>
        </table>
        <table width="100%"
            style="
          margin: 0 auto;
          border: 1px solid rgb(201, 201, 201);
          margin-bottom: 30px;
          border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="border: 1px solid rgb(201, 201, 201); padding: 10px" align="left">
                        Journy details
                    </th>
                    <th style="border: 1px solid rgb(201, 201, 201); padding: 10px" align="left">
                        Vehicle type
                    </th>
                    <th style="border: 1px solid rgb(201, 201, 201); padding: 10px" align="left">
                        Date
                    </th>
                    <th style="border: 1px solid rgb(201, 201, 201); padding: 10px" align="left">
                        Price
                    </th>
                </tr>
            </thead>
            <tbody>

            @foreach ($data['transport_allowances'] as $transport_allowance)
                <tr>
                    <td style="border: 1px solid rgb(201, 201, 201); padding-left: 10px">
                        {!! $transport_allowance->start_from ?? '<span style="color: red">Undefained</span>' !!}  to {!! $transport_allowance->end_to ?? '<span style="color: red">Undefained</span>' !!}
                    </td>
                    
                    <td style="border: 1px solid rgb(201, 201, 201); padding-left: 10px">
                        {{ $transport_allowance->transport_type }}
                    </td>

                    <td style="border: 1px solid rgb(201, 201, 201); padding-left: 10px">
                        {{ \Carbon\Carbon::parse($transport_allowance->created_at)->format('d M, Y') }}
                    </td>

                    <td style="border: 1px solid rgb(201, 201, 201); padding-left: 10px">
                        {{ $transport_allowance->amount }}/-
                    </td>
                </tr>
            @endforeach
                <tr>
                    <td style="padding-left: 10px">Total =</td>
                    <td></td>
                    <td></td>
                    <td style="padding-left: 10px">{{ $data['transport_allowances']->sum('amount') }}/-</td>
                </tr>
            </tbody>
        </table>

        <table width="100%" align="center" style="margin-top: 100px;">
            <tr>
                <td width="250" align="center">
                    <input type="text" style="background: transparent;border: none;border-bottom: 1px dotted gray;width: 80%;" />
                    Recieved by
                </td>
                <td width="250" align="center">
                    <input type="text"
                        style="
                background: transparent;
                border: none;
                border-bottom: 1px dotted gray;
                width: 80%;
              " />
                    Prepared by
                </td>
                <td width="250" align="center">
                    <input type="text"
                        style="
                background: transparent;
                border: none;
                border-bottom: 1px dotted gray;
                width: 80%;
              " />
                    Authorized by
                </td>
            </tr>
        </table>

    </main>
</body>

</html>
