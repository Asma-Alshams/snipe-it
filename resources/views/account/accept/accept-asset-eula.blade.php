<!DOCTYPE html>
<html lang="en ">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html">
    <style>
      @page {
  margin: 3mm 10mm 3mm 10mm; /* top, right, bottom, left */
}
        body {
            font-family:'Dejavu Sans', sans-serif;
            font-size: 16px;
            /* direction: rtl; */
            text-align: center;
            margin: 0;
            font-weight: 500;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
            font-family: 'DejaVu Sans', sans-serif;
        }
        td {
            padding:2px;
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
        <img  width="100%" src="{{ $logo }}">
    </center>
@endif
        <br>
        <h3 style="margin: 0; font-weight: 600; color: #00008B;">
            استمارة تسليم واستلام عهدة من إدارة النظم والحلول الرقمية 
        </h3>
        <br>
<table>
    <tr class="label0">
        <td colspan="2"> بيانات مقدم الطلب   </td>
    </tr>
    <tr>
        <td class="label">  المسمى الوظيفي  </td> 
        <td class="label">  الاسم   </td>  
    </tr>
    <tr>
        <td> {{ $user->jobtitle ?? '' }} </td>
        <td> {{$assigned_to}} </td>
    </tr>
    <tr>
        <td class="label">  الإدارة/القسم  </td>
        <td class="label">   الرقم الوظيفي  </td>
    </tr>
    <tr>
        <td> {{ $user->department->name ?? '' }} </td>
        <td>{{ $user->employee_num ?? '' }} </td>
    </tr>
    <tr>
        <td class="label">   تاريخ الطلب   </td>
        <td class="label">  مكان مقدم الطلب   </td>
    </tr>
    <tr>
        <td>   {{ date($date_settings) }} </td>
        <td> {{ $user->userloc->name ?? '' }} </td>
    </tr>
    <tr>
        <td class="label"> تاريخ المتوقع تسليمه </td>
        <td class="label">  تاريخ التسليم  </td>
    </tr>
    <tr>
        <td>   {{ $asset->expected_checkin ? \App\Helpers\Helper::getFormattedDateObject($asset->expected_checkin, 'date')["formatted"] : 'اصل ثابت' }}</td>
        <td> {{$check_out_date}} </td>
    </tr> 
    <tr><td colspan="2" style="padding-top: 20px;"> <br> </td></tr>
    <tr class="label0">
        <td colspan="2">  نوع الجهاز  </td>
    </tr>
    <tr>
        <td class="label">  ماركة الجهاز   </td>
        <td class="label"> رقم الطراز  </td>
    </tr>
    <tr>
        <td>{{ $asset->model->manufacturer->name ?? '' }} </td>
        <td> {{ $asset->name ?? '' }}  ({{ $item_model ?? '' }}) </td>
    </tr>
    <tr>
        <td class="label"> رقم التسلسل   </td>
        <td class="label">   علامة الاصل  </td>
    </tr>
    <tr>
        <td>{{ $item_serial ?? '' }}</td>
        <td>  {{ $item_tag ?? '' }}  </td>
    </tr> 
        <td class="label">الملحقات</td>
        <td class="label">تاريخ الشراء</td>
    </tr>
    <tr>
        <td>{{ $asset->notes ?? '-' }}</td>
        <td>{{ $asset->purchase_date ? \App\Helpers\Helper::getFormattedDateObject($asset->purchase_date, 'date')["formatted"] : '-' }}</td>
    </tr>
    
    <tr><td colspan="2" style="padding-top: 10px;"> <br> </td></tr>
    <tr>
        <td colspan="2" style="border: none;">
            <table width="100%">
                <tr>
                    <td>:ملاحظات التسليم 
                        <br>
                        {{ $checkin_note ?? '' }}</td>
                    <td>:ملاحظات الاستلام  
                        <br>
                        {{ $checkout_note ?? '' }} </td>    
                    <td>:ملاحظة الموافقة  
                        <br>
                        {{ $acceptance_note ?? '' }}</td>
                </tr>
            </table>
        </td>
    </tr> 
            <div style="page-break-before: always;"></div>
    <tr>
    <tr class="label0">
        <td colspan="2">  اتفاقية مستخدمي أجهزة الحاسب المحمول والأجهزة اللوحية </td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: center;"> 
            <div style="border: 1px solid #000; padding-right: 35px; padding-left: 35px; padding-top: 15px; text-align: right; font-size: 13px;">
            @if ($eula)
        
                {!! $eula !!}

            @endif
            </div>
        </td>  
  
    </tr>
    <!-- <tr>
        <td colspan="2" style="border: none;">
            <table width="100%">
                <tr>
                    <td> مخصص ل: {{$assigned_to}}</td>
                    <td> تاريخ القبول: {{$accepted_date}}</td>
                </tr>
            </table>
        </td>
    </tr> -->
</table>      

@if ($signature!='')
<img src="{{ $signature }}" style="max-width: 350px; border-bottom: black solid 1px;">
@endif
<p>{{$assigned_to}}    <br>   
تاريخ القبول: {{$accepted_date}}
    </p>
</body>
</html>