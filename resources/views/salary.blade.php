<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Transport Allowance</title>
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
                            <h2 style="margin: 0; color: #e63c3c">SELOPIA</h2>
                        </td>
                    </tr>
                </table>
                <p style="padding: 0; margin: 0; line-height: 16px; font-size: 14px">
                    Head Office : 704,Concord Tower,113 Kazi Nazrul Islam Ave,Dhaka-1205 <br>
                    Registered Address : 5B Navana Zohura Square,28 Mymensingh Road,Dhaka-1205 <br>
                    Phone: +88 0963 888 4444, Email: hello@selopia.com, Website:www.selopia.com
                </p>
            </td>
        </tr>
    </table>

    <table width="100%" align="center" style="margin-bottom: 50px;">
        <tr>
            <td width="300">
                Voucher No : {{ auth()->user()->id }}-{{ rand(10000, 99999) }}
            </td>
            <td width="44"></td>
            <td width="150">Date : {{ \Carbon\Carbon::now()->format('d M, Y') }}
            </td>
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
                Employee Detail
            </th>
            <th style="border: 1px solid rgb(201, 201, 201); padding: 10px" align="left">
                Salary (+KPI)
            </th>
            <th style="border: 1px solid rgb(201, 201, 201); padding: 10px" align="left">
                Total Payable
            </th>
            <th style="border: 1px solid rgb(201, 201, 201); padding: 10px" align="left">
                Total Paid
            </th>
        </tr>
        </thead>
        <tbody>

        @foreach ($data as $item)
            <tr>
                <td style="border: 1px solid rgb(201, 201, 201); padding-left: 10px">
                    {{ $item->user->name }}
                </td>

                <td style="border: 1px solid rgb(201, 201, 201); padding-left: 10px">
                    {{ $item->salary }} {{ $item->kpi_payable != 0 ? ' (+' . $item->kpi_payable . ')' : '' }}
                </td>

                <td style="border: 1px solid rgb(201, 201, 201); padding-left: 10px">
                    {{ $item->salary + $item->kpi_payable }}
                </td>

                <td style="border: 1px solid rgb(201, 201, 201); padding-left: 10px">
                    {!! count($item->salary_data) != 0 ? $item->salary_data[0]->paid_amount : '<span style="color: red">Not Paid</span>' !!}
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table width="100%" align="center" style="margin-top: 100px;">
        <tr>
            <td width="50" align="center">
                <input type="text" style="background: transparent;border: none;border-bottom: 1px dotted gray;width: 80%;" />
                Received by
            </td>
            <td width="50" align="center">
                <input type="text"
                       style="
                background: transparent;
                border: none;
                border-bottom: 1px dotted gray;
                width: 80%;
              " />
                Prepared by
            </td>
            <td width="50" align="center">
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
