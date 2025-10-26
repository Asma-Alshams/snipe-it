<!DOCTYPE html>
<html lang="en ">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Type" content="text/html">
    <style>
        body {
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            padding: 5px;
        }
    
        .label0 {
            color: #00008B;
            font-weight: bold;
            font-size: 15px;
            font-family: 'aealarabiya';
        }
        .label {
            font-weight: bold;
            color: grey;
            font-size: 13px;
            font-family: 'aealarabiya';
            border: none;
        }
        td {  
            border:1px solid black;
        }
        .header {
            
            font-weight: bold;
            font-family: 'aealarabiya', sans-serif;
        }
        h3 {
            color: #00008B;
            font-weight: bold;
            font-size: 16px;
            font-family: 'aealarabiya';
            
        }
        .eula-content {
           text-align: right;
           padding: 20px;
            font-size: 10px;
            font-family: 'notonaskharabicnormal', sans-serif;
        }
        .no-border {
            border: none;
        }
    </style>
</head>
<body>
@if (!empty($logo))
            <img src="{{ $logo }}" alt="Logo" class="logo" style="width: 600px; "/>   
        @endif
        <br>
        <h3 style="font-weight: 600; color: #00008B; font-size: 16px;">
            استمارة تسليم واستلام عهدة من إدارة النظم والحلول الرقمية 
        </h3>
        <br>
<table>
    <tr class="label0">
        <td colspan="2"> بيانات صاحب الطلب   </td>
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
        <td class="label">  رقم المكتب /المبنى </td>
    </tr>
    <tr>
        <td> {{ date($date_settings) }} </td>
        <td> {{ $asset->location->name ?? '' }} </td>
    </tr>
    <tr>
        <td class="label"> تاريخ إرجاع العهده </td>
        <td class="label">  تاريخ التسليم  </td>
    </tr>
    <tr>
        <td> {{ $asset->expected_checkin ? \App\Helpers\Helper::getFormattedDateObject($asset->expected_checkin, 'date')["formatted"] : 'اصل ثابت' }}</td>
        <td> {{$check_out_date}} </td>
    </tr> 
    <tr ><td class="no-border" colspan="2" style="padding-top: 20px;"></td></tr>
    <tr class="label0">
        <td colspan="2"> بيانات الجهاز  </td>
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
        <td>{{ $item_serial ?? '-' }}</td>
        <td>  {{ $item_tag ?? '' }}  </td>
    </tr>
    <tr>
        <td class="label">الملحقات</td>
        <td class="label">تاريخ الشراء</td>
    </tr>
    <tr>
        <td>{{ $asset->notes ?? '-' }}</td>
        <td>{{ $asset->purchase_date ? \App\Helpers\Helper::getFormattedDateObject($asset->purchase_date, 'date')["formatted"] : '-' }}</td>
    </tr>
    
    <tr><td class="no-border" colspan="2" style="padding-top: 20px;"> </td></tr>
    <tr>
        <td colspan="2" style="border: none;">
            <table width="100%">
                <tr>
                <!-- <td width="33%" style="border: 1px solid #000; padding: 5px;  font-family: 'notonaskharabicnormal', sans-serif; font-size: 10px;">
                <strong style="color: grey; font-family: 'aealarabiya';font-size: 13px;">ملاحظات التسليم</strong><br>
                        {{ $checkin_note ?? '' }}
                    </td> -->
                    <td width="50%" style="border: 1px solid #000; padding: 5px;  font-family: 'notonaskharabicnormal', sans-serif; font-size: 10px;">
                    <strong style="color: grey; font-family: 'aealarabiya';font-size: 13px;">ملاحظة الموافقة</strong><br>
                        {{ $acceptance_note ?? '' }}
                    </td>
                    <td width="50%" style="border: 1px solid #000; padding: 5px;  font-family: 'notonaskharabicnormal', sans-serif; font-size: 10px;">
                        <strong style="color: grey; font-family: 'aealarabiya';font-size: 13px;">ملاحظات الاستلام</strong><br>
                        {{ $checkout_note ?? '' }}
                    </td>
                </tr>
            </table>
        </td>
    </tr> 
            <div style="page-break-before: always;"></div>
    <tr class="label0" >
        <td style="border: none;" colspan="2">  اتفاقية مستخدمي أجهزة الحاسب المحمول والأجهزة اللوحية </td>
    </tr>
    <tr>
        <td colspan="2"> 
            <div class="eula-content">
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


@if($signature)
    <img src="{{ $signature }}" alt="Signature" style="max-width:350px;" />
@else
    -
@endif
<p style="font-family: 'notonaskharabicnormal', sans-serif; font-size: 11px; ">
    {{$assigned_to}}<br>   
    تاريخ القبول: {{$accepted_date}}
</p>
</body>
</html>