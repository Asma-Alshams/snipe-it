<!DOCTYPE html>
<html lang="ar " dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html">
    <style>
      @page {
  margin: 2mm 10mm 2mm 10mm; /* top, right, bottom, left */
}
        body {
            font-family:'Dejavu Sans', sans-serif;
            font-size: 12px;
            direction: rtl;
            text-align: center;
            margin: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-family: 'DejaVu Sans', sans-serif;
        }
        td {
            padding:4px;
        }
        tr:nth-child(odd) td {
            border:1px solid black;
        }
        tr:nth-child(even) td {
            border: none;
        }
        .label0 {
            color: #00008B;
            font-weight: 600;
        }
        .label {
            font-weight: 500;
            color: grey;
        }
    </style>
</head>
<body>

@if ($logo)
    <center>
        <img  width="90%" src="{{ $logo }}">
    </center>
@endif

<div style="display: flex; justify-content: space-between; align-items: flex-start;">
    <div style="text-align: center; flex-grow: 1;">
        <h3 style="margin: 0; font-weight: 600; color: #00008B;">
            Technical Support Requisition Form <br>
            استمارة طلب اجهزة من قسم الدعم الفني 
        </h3>
    </div>
</div>

<table>
    <tr class="label0">
        <td colspan="2">{{$user->notes }}  Requestor Information  بيانات مقدم الطلب   </td>
    </tr>
    <tr>
        <td class="label"> Name  الاسم   </td> 
        <td class="label"> Designation  المسمى الوظيفي  </td>  
    </tr>
    <tr>
        <td> {{$assigned_to}} </td>
        <td> {{ $user->jobtitle }} </td>
    </tr>
    <tr>
        <td class="label"> Employee No.  الرقم الوظيفي  </td>
        <td class="label"> Directorate/Department  الإدارة/القسم  </td>
    </tr>
    <tr>
        <td> {{ $user->employee_num }} </td>
        <td> {{ $user->department->name }} </td>
    </tr>
    <tr>
        <td class="label">  Email  البريد الإلكتروني  </td>
        <td class="label">  Request Date  تاريخ الطلب  </td>
    </tr>
    <tr>
        <td> {{ $user->email }} 
        <td> {{ date($date_settings) }} </td>
    </tr>
    <tr>
        <td class="label"> Mobile  النقال </td>
        <td class="label"> Requestor Location  مكان مقدم الطلب  </td>
    </tr>
    <tr>
        <td> {{ $user->phone }} </td>
        <td> {{ $user->userloc->name }} </td>
    </tr> 
    <tr><td colspan="2" style="padding-top: 20px;"> </td></tr>
    <tr class="label0">
        <td colspan="2"> Device Details  نوع الجهاز  </td>
    </tr>
    <tr>
        <td class="label"> Model No. رقم الطراز   </td>
        <td class="label">  Device Brand   ماركة الجهاز </td>
    </tr>
    <tr>
        <td> {{ $item_model }} </td>
        <td>{{ $asset->model->manufacturer->name ?? '' }}</td>
    </tr>
    <tr>
        <td class="label"> Asset Tag  علامة الاصل  </td>
        <td class="label"> Serial No.  رقم التسلسل   </td>
    </tr>
    <tr>
        <td> {{ $item_tag }} </td>
        <td> {{ $item_serial }} </td>
    </tr> 
    <tr><td colspan="2" style="padding-top: 10px;"> </td></tr>
    <tr>
        <td colspan="2" style="border: none;">
            <table width="100%">
                <tr>
                    <td>Checkout Note ملاحظات الاستلام  {{ $checkout_note ?? '' }} </td>
                    <td>Checkin Note ملاحظات التسليم {{ $checkin_note ?? '' }}</td>
                    <td>Acceptance Note ملاحظة الموافقة  {{ $acceptance_note ?? '' }}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr class="label0">
        <td colspan="2"> اتفاقية مستخدمي أجهزة الحاسب المحمول والأجهزة اللوحية </td>
    </tr>
    <tr>
        <td colspan="2" dir="rtl"> 
            @if ($eula)
                {!! $eula !!}
            @endif
        </td>  
    </tr>
    <tr>
        <td colspan="2" style="border: none;">
            <table width="100%">
                <tr>
                    <td> {{ trans('general.assigned_date') }}: {{$check_out_date}}</td>
                    <td> {{ trans('general.assignee') }}: {{$assigned_to}}</td>
                    <td> {{ trans('general.accepted_date') }}: {{$accepted_date}}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>      

@if ($signature!='')
<img src="{{ $signature }}" style="max-width: 350px; border-bottom: black solid 1px;">
@endif
<h4 >{{$assigned_to}}</h4>
      
</body>
</html>