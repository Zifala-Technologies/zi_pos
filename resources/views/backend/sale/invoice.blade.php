<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="{{ url('logo', $general_setting->site_logo) }}" />
    <title>{{ $general_setting->site_title }}</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="all,follow">

    <style type="text/css">
        * {
            font-size: 14px;
            line-height: 24px;
            font-family: 'Ubuntu', sans-serif;
            text-transform: capitalize;
        }

        .btn {
            padding: 7px 10px;
            text-decoration: none;
            border: none;
            display: block;
            text-align: center;
            margin: 7px;
            cursor: pointer;
        }

        .btn-info {
            background-color: #999;
            color: #FFF;
        }

        .btn-primary {
            background-color: #6449e7;
            color: #FFF;
            width: 100%;
        }

        td,
        th,
        tr,
        table {
            border-collapse: collapse;
        }

        tr {
            border-bottom: 1px dotted #ddd;
        }

        td,
        th {
            padding: 7px 0;
            width: 50%;
        }

        table {
            width: 100%;
        }

        tfoot tr th:first-child {
            text-align: left;
        }

        .centered {
            text-align: center;
            align-content: center;
        }

        .centered>* {
            margin: 3px;
        }

        .invoice_info {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            row-gap: 10px;
        }

        small {
            font-size: 11px;
        }

        @media print {
            * {
                font-size: 12px;
                line-height: 20px;
            }



            .extra-invoice {
                display: block !important;

            }

            .break-after {
                page-break-after: always;
            }

            td,
            th {
                padding: 5px 0;
            }

            .hidden-print {
                display: none !important;
            }

            @page {
                margin: 0ch;
            }

            @page: first {
                margin-top: 00cm;
            }

            /*tbody::after {
                content: ''; display: block;
                page-break-after: always;
                page-break-inside: avoid;
                page-break-before: avoid;
            }*/
        }
    </style>
</head>

<body>
    <div style="max-width:400px;margin:0 auto">
        @if (preg_match('~[0-9]~', url()->previous()))
            @php $url = '../../pos'; @endphp
        @else
            @php $url = url()->previous(); @endphp
        @endif
        <div class="hidden-print">
            <table>
                <tr>
                    <td><a href="{{ $url }}" class="btn btn-info"><i class="fa fa-arrow-left"></i>
                            {{ trans('file.Back') }}</a> </td>
                    <td><button onclick="window.print();" class="btn btn-primary"><i class="dripicons-print"></i>
                            {{ trans('file.Print') }}</button></td>
                </tr>
            </table>
            <br>
        </div>
        {{-- Default User Invoice --}}
        <div id="default-invoice">
            <div class="centered">
                @if ($general_setting->site_logo)
                    <img src="{{ url('logo', $general_setting->site_logo) }}" height="42" width="50"
                        style="margin:10px 0;">
                @endif

                <h2>{{ $lims_biller_data->company_name }}</h2>
                {!! $lims_pos_setting_data->invoice_info !!}
            </div>
            <div class="invoice_info">
                <div>
                    {{ trans('file.Invoice id') }}:
                    <b>{{ $lims_sale_data->id }} </b><br>
                    {{ trans('file.customer') }}: {{ $lims_customer_data->name }}
                </div>
                <div style="text-align: right">
                    {{ trans('file.Date') }}:
                    {{ date($general_setting->date_format, strtotime($lims_sale_data->created_at->toDateString())) }}<br>
                </div>
            </div>
            <table class="table-data">
                <tbody>
                    <?php $total_product_tax = 0; ?>
                    @foreach ($lims_product_sale_data as $key => $product_sale_data)
                        <?php
                        $lims_product_data = \App\Product::find($product_sale_data->product_id);

                        if ($lims_product_data->category_id == $lims_pos_setting_data->printer_one_category_id) {
                            $cat_one= true;
                        } elseif ($lims_product_data->category_id == $lims_pos_setting_data->printer_two_category_id) {
                            $cat_two= true;
                        } elseif ($lims_product_data->category_id == $lims_pos_setting_data->printer_three_category_id) {
                            $cat_three= true;
                        }

                        if ($product_sale_data->variant_id) {
                            $variant_data = \App\Variant::find($product_sale_data->variant_id);
                            $product_name = $lims_product_data->name . ' [' . $variant_data->name . ']';
                            $product_category = $lims_product_data->category->name;
                        } elseif ($product_sale_data->product_batch_id) {
                            $product_batch_data = \App\ProductBatch::select('batch_no')->find($product_sale_data->product_batch_id);
                            $product_name = $lims_product_data->name . ' [' . trans('file.Batch No') . ':' . $product_batch_data->batch_no . ']';
                            $product_category = $lims_product_data->category->name;
                        } else {
                            $product_name = $lims_product_data->name;
                            $product_category = $lims_product_data->category->name;
                        }

                        if ($product_sale_data->imei_number) {
                            $product_name .= '<br>' . trans('IMEI or Serial Numbers') . ': ' . $product_sale_data->imei_number;
                        }
                        ?>
                        <tr>
                            <td colspan="2">
                                {!! $product_name !!}
                                <br>{{ $product_sale_data->qty }} x
                                {{ number_format((float) ($product_sale_data->total / $product_sale_data->qty), 2, '.', '') }}

                                @if ($product_sale_data->tax_rate)
                                    <?php $total_product_tax += $product_sale_data->tax; ?>
                                    [{{ trans('file.Tax') }} ({{ $product_sale_data->tax_rate }}%):
                                    {{ $product_sale_data->tax }}]
                                @endif
                            </td>
                            <td style="text-align:right;vertical-align:bottom">
                                {{ number_format((float) $product_sale_data->total, 2, '.', '') }}</td>
                        </tr>
                    @endforeach

                    <!-- <tfoot> -->
                    <tr>
                        <th colspan="2" style="text-align:left">{{ trans('file.Total') }}</th>
                        <th style="text-align:right">
                            {{ number_format((float) $lims_sale_data->total_price, 2, '.', '') }}</th>
                    </tr>
                    @if ($general_setting->invoice_format == 'gst' && $general_setting->state == 1)
                        <tr>
                            <td colspan="2">IGST</td>
                            <td style="text-align:right">{{ number_format((float) $total_product_tax, 2, '.', '') }}
                            </td>
                        </tr>
                    @elseif($general_setting->invoice_format == 'gst' && $general_setting->state == 2)
                        <tr>
                            <td colspan="2">SGST</td>
                            <td style="text-align:right">
                                {{ number_format((float) ($total_product_tax / 2), 2, '.', '') }}</td>
                        </tr>
                        <tr>
                            <td colspan="2">CGST</td>
                            <td style="text-align:right">
                                {{ number_format((float) ($total_product_tax / 2), 2, '.', '') }}</td>
                        </tr>
                    @endif
                    @if ($lims_sale_data->order_tax)
                        <tr>
                            <th colspan="2" style="text-align:left">{{ trans('file.Order Tax') }}</th>
                            <th style="text-align:right">
                                {{ number_format((float) $lims_sale_data->order_tax, 2, '.', '') }}</th>
                        </tr>
                    @endif
                    @if ($lims_sale_data->order_discount)
                        <tr>
                            <th colspan="2" style="text-align:left">{{ trans('file.Order Discount') }}</th>
                            <th style="text-align:right">
                                {{ number_format((float) $lims_sale_data->order_discount, 2, '.', '') }}</th>
                        </tr>
                    @endif
                    @if ($lims_sale_data->coupon_discount)
                        <tr>
                            <th colspan="2" style="text-align:left">{{ trans('file.Coupon Discount') }}</th>
                            <th style="text-align:right">
                                {{ number_format((float) $lims_sale_data->coupon_discount, 2, '.', '') }}</th>
                        </tr>
                    @endif
                    @if ($lims_sale_data->shipping_cost)
                        <tr>
                            <th colspan="2" style="text-align:left">{{ trans('file.Shipping Cost') }}</th>
                            <th style="text-align:right">
                                {{ number_format((float) $lims_sale_data->shipping_cost, 2, '.', '') }}</th>
                        </tr>
                    @endif
                    <tr>
                        <th colspan="2" style="text-align:left">{{ trans('file.grand total') }}</th>
                        <th style="text-align:right">
                            {{ number_format((float) $lims_sale_data->grand_total, 2, '.', '') }}</th>
                    </tr>
                    <tr>
                        @if ($general_setting->currency_position == 'prefix')
                            <th class="centered" colspan="3">{{ trans('file.In Words') }}:
                                <span>{{ $currency->code }}</span>
                                <span>{{ str_replace('-', ' ', $numberInWords) }}</span>
                            </th>
                        @else
                            <th class="centered" colspan="3">{{ trans('file.In Words') }}:
                                <span>{{ str_replace('-', ' ', $numberInWords) }}</span>
                                <span>{{ $currency->code }}</span>
                            </th>
                        @endif
                    </tr>
                </tbody>
                <!-- </tfoot> -->
            </table>
            <table>
                <tbody>
                    @foreach ($lims_payment_data as $payment_data)
                        <tr style="background-color:#ddd;">
                            <td style="padding: 5px;width:30%">{{ trans('file.Paid By') }}:
                                {{ $payment_data->paying_method }}</td>
                            <td style="padding: 5px;width:40%">{{ trans('file.Amount') }}:
                                {{ number_format((float) $payment_data->amount, 2, '.', '') }}</td>
                            <td style="padding: 5px;width:30%">{{ trans('file.Change') }}:
                                {{ number_format((float) $payment_data->change, 2, '.', '') }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td class="centered" colspan="3">{{ $lims_pos_setting_data->invoice_info_footer }}</td>
                    </tr>

                </tbody>
            </table>
            <div class="centered" style="margin:10px 0 10px">
                <small>
                    {{ trans('file.Developed By') }} ZifalaTech</small>
            </div>
        </div>
        <br>
        {{-- Extra Invoice One --}}
        @if ($lims_pos_setting_data->printer_one_category_id != 0 && $cat_one)
            <div class="break-after"></div>
            <div class="extra-invoice" id="extra-invoice-one" style="display: none">
                <div class="centered">
                    <h2>{{ $lims_biller_data->company_name }}</h2>
                    <p> {{ trans('file.Invoice id') }}:
                        <b>{{ $lims_sale_data->id }} </b><br>
                    </p>
                </div>
                <p>{{ trans('file.Date') }}:
                    {{ date($general_setting->date_format, strtotime($lims_sale_data->created_at->toDateString())) }}<br>
                </p>
                <table class="table-data">
                    <tbody>
                        <?php $total_product_tax = 0; ?>
                        @foreach ($lims_product_sale_data as $key => $product_sale_data)
                            <?php
                            $lims_product_data = \App\Product::find($product_sale_data->product_id);
                            if ($lims_product_data->category_id != $lims_pos_setting_data->printer_one_category_id) {
                                continue;
                            }
                            if ($product_sale_data->variant_id) {
                                $variant_data = \App\Variant::find($product_sale_data->variant_id);
                                $product_name = $lims_product_data->name . ' [' . $variant_data->name . ']';
                            } elseif ($product_sale_data->product_batch_id) {
                                $product_batch_data = \App\ProductBatch::select('batch_no')->find($product_sale_data->product_batch_id);
                                $product_name = $lims_product_data->name . ' [' . trans('file.Batch No') . ':' . $product_batch_data->batch_no . ']';
                            } else {
                                $product_name = $lims_product_data->name;
                            }

                            if ($product_sale_data->imei_number) {
                                $product_name .= '<br>' . trans('IMEI or Serial Numbers') . ': ' . $product_sale_data->imei_number;
                            }
                            ?>
                            <tr>
                                <td colspan="2">
                                    {!! $product_name !!}
                                </td>
                                <td style="text-align:right;vertical-align:bottom">
                                    {{ $product_sale_data->qty }} </td>
                            </tr>
                        @endforeach

                        <!-- <tfoot> -->

                        @if ($general_setting->invoice_format == 'gst' && $general_setting->state == 1)
                            <tr>
                                <td colspan="2">IGST</td>
                                <td style="text-align:right">
                                    {{ number_format((float) $total_product_tax, 2, '.', '') }}
                                </td>
                            </tr>
                        @elseif($general_setting->invoice_format == 'gst' && $general_setting->state == 2)
                            <tr>
                                <td colspan="2">SGST</td>
                                <td style="text-align:right">
                                    {{ number_format((float) ($total_product_tax / 2), 2, '.', '') }}</td>
                            </tr>
                            <tr>
                                <td colspan="2">CGST</td>
                                <td style="text-align:right">
                                    {{ number_format((float) ($total_product_tax / 2), 2, '.', '') }}</td>
                            </tr>
                        @endif
                        @if ($lims_sale_data->order_tax)
                            <tr>
                                <th colspan="2" style="text-align:left">{{ trans('file.Order Tax') }}</th>
                                <th style="text-align:right">
                                    {{ number_format((float) $lims_sale_data->order_tax, 2, '.', '') }}</th>
                            </tr>
                        @endif
                        @if ($lims_sale_data->order_discount)
                            <tr>
                                <th colspan="2" style="text-align:left">{{ trans('file.Order Discount') }}</th>
                                <th style="text-align:right">
                                    {{ number_format((float) $lims_sale_data->order_discount, 2, '.', '') }}</th>
                            </tr>
                        @endif

                    </tbody>
                    <!-- </tfoot> -->
                </table>

                <div class="centered" style="margin:10px 0 50px">
                    <small>
                        {{ trans('file.Developed By') }} ZifalaTech</small>
                </div>
            </div>
        @endif
        {{-- Extra Invoice Two --}}
        @if ($lims_pos_setting_data->printer_two_category_id != 0 && $cat_two)
            <div class="break-after"></div>
            <div class="extra-invoice" id="extra-invoice-two" style="display: none">
                <div class="centered">


                    <h2>{{ $lims_biller_data->company_name }}</h2>

                    <p> {{ trans('file.Invoice id') }}:
                        <b>{{ $lims_sale_data->id }} </b><br>
                    </p>
                </div>
                <p>{{ trans('file.Date') }}:
                    {{ date($general_setting->date_format, strtotime($lims_sale_data->created_at->toDateString())) }}<br>

                </p>
                <table class="table-data">
                    <tbody>
                        <?php $total_product_tax = 0; ?>
                        @foreach ($lims_product_sale_data as $key => $product_sale_data)
                            <?php
                            $lims_product_data = \App\Product::find($product_sale_data->product_id);
                            if ($lims_product_data->category_id != $lims_pos_setting_data->printer_two_category_id) {
                                continue;
                            }
                            if ($product_sale_data->variant_id) {
                                $variant_data = \App\Variant::find($product_sale_data->variant_id);
                                $product_name = $lims_product_data->name . ' [' . $variant_data->name . ']';
                            } elseif ($product_sale_data->product_batch_id) {
                                $product_batch_data = \App\ProductBatch::select('batch_no')->find($product_sale_data->product_batch_id);
                                $product_name = $lims_product_data->name . ' [' . trans('file.Batch No') . ':' . $product_batch_data->batch_no . ']';
                            } else {
                                $product_name = $lims_product_data->name;
                            }

                            if ($product_sale_data->imei_number) {
                                $product_name .= '<br>' . trans('IMEI or Serial Numbers') . ': ' . $product_sale_data->imei_number;
                            }
                            ?>
                            <tr>
                                <td colspan="2">
                                    {!! $product_name !!}


                                </td>
                                <td style="text-align:right;vertical-align:bottom">
                                    {{ $product_sale_data->qty }} </td>
                            </tr>
                        @endforeach

                        <!-- <tfoot> -->

                        @if ($general_setting->invoice_format == 'gst' && $general_setting->state == 1)
                            <tr>
                                <td colspan="2">IGST</td>
                                <td style="text-align:right">
                                    {{ number_format((float) $total_product_tax, 2, '.', '') }}
                                </td>
                            </tr>
                        @elseif($general_setting->invoice_format == 'gst' && $general_setting->state == 2)
                            <tr>
                                <td colspan="2">SGST</td>
                                <td style="text-align:right">
                                    {{ number_format((float) ($total_product_tax / 2), 2, '.', '') }}</td>
                            </tr>
                            <tr>
                                <td colspan="2">CGST</td>
                                <td style="text-align:right">
                                    {{ number_format((float) ($total_product_tax / 2), 2, '.', '') }}</td>
                            </tr>
                        @endif
                        @if ($lims_sale_data->order_tax)
                            <tr>
                                <th colspan="2" style="text-align:left">{{ trans('file.Order Tax') }}</th>
                                <th style="text-align:right">
                                    {{ number_format((float) $lims_sale_data->order_tax, 2, '.', '') }}</th>
                            </tr>
                        @endif
                        @if ($lims_sale_data->order_discount)
                            <tr>
                                <th colspan="2" style="text-align:left">{{ trans('file.Order Discount') }}</th>
                                <th style="text-align:right">
                                    {{ number_format((float) $lims_sale_data->order_discount, 2, '.', '') }}</th>
                            </tr>
                        @endif

                    </tbody>
                    <!-- </tfoot> -->
                </table>

                <div class="centered" style="margin:10px 0 50px">
                    <small>
                        {{ trans('file.Developed By') }} ZifalaTech</small>
                </div>
            </div>
        @endif
        {{-- Extra Invoice Three --}}
        @if ($lims_pos_setting_data->printer_three_category_id != 0 && $cat_three)
            <div class="break-after"></div>
            <div class="extra-invoice" id="extra-invoice-three" style="display: none">
                <div class="centered">


                    <h2>{{ $lims_biller_data->company_name }}</h2>

                    <p> {{ trans('file.Invoice id') }}:
                        <b>{{ $lims_sale_data->id }} </b><br>
                    </p>
                </div>
                <p>{{ trans('file.Date') }}:
                    {{ date($general_setting->date_format, strtotime($lims_sale_data->created_at->toDateString())) }}<br>

                </p>
                <table class="table-data">
                    <tbody>
                        <?php $total_product_tax = 0; ?>
                        @foreach ($lims_product_sale_data as $key => $product_sale_data)
                            <?php
                            $lims_product_data = \App\Product::find($product_sale_data->product_id);
                            if ($lims_product_data->category_id != $lims_pos_setting_data->printer_three_category_id) {
                                continue;
                            }
                            if ($product_sale_data->variant_id) {
                                $variant_data = \App\Variant::find($product_sale_data->variant_id);
                                $product_name = $lims_product_data->name . ' [' . $variant_data->name . ']';
                            } elseif ($product_sale_data->product_batch_id) {
                                $product_batch_data = \App\ProductBatch::select('batch_no')->find($product_sale_data->product_batch_id);
                                $product_name = $lims_product_data->name . ' [' . trans('file.Batch No') . ':' . $product_batch_data->batch_no . ']';
                            } else {
                                $product_name = $lims_product_data->name;
                            }

                            if ($product_sale_data->imei_number) {
                                $product_name .= '<br>' . trans('IMEI or Serial Numbers') . ': ' . $product_sale_data->imei_number;
                            }
                            ?>
                            <tr>
                                <td colspan="2">
                                    {!! $product_name !!}


                                </td>
                                <td style="text-align:right;vertical-align:bottom">
                                    {{ $product_sale_data->qty }} </td>
                            </tr>
                        @endforeach

                        <!-- <tfoot> -->

                        @if ($general_setting->invoice_format == 'gst' && $general_setting->state == 1)
                            <tr>
                                <td colspan="2">IGST</td>
                                <td style="text-align:right">
                                    {{ number_format((float) $total_product_tax, 2, '.', '') }}
                                </td>
                            </tr>
                        @elseif($general_setting->invoice_format == 'gst' && $general_setting->state == 2)
                            <tr>
                                <td colspan="2">SGST</td>
                                <td style="text-align:right">
                                    {{ number_format((float) ($total_product_tax / 2), 2, '.', '') }}</td>
                            </tr>
                            <tr>
                                <td colspan="2">CGST</td>
                                <td style="text-align:right">
                                    {{ number_format((float) ($total_product_tax / 2), 2, '.', '') }}</td>
                            </tr>
                        @endif
                        @if ($lims_sale_data->order_tax)
                            <tr>
                                <th colspan="2" style="text-align:left">{{ trans('file.Order Tax') }}</th>
                                <th style="text-align:right">
                                    {{ number_format((float) $lims_sale_data->order_tax, 2, '.', '') }}</th>
                            </tr>
                        @endif
                        @if ($lims_sale_data->order_discount)
                            <tr>
                                <th colspan="2" style="text-align:left">{{ trans('file.Order Discount') }}</th>
                                <th style="text-align:right">
                                    {{ number_format((float) $lims_sale_data->order_discount, 2, '.', '') }}</th>
                            </tr>
                        @endif

                    </tbody>
                    <!-- </tfoot> -->
                </table>

                <div class="centered" style="margin:10px 0 50px">
                    <small>
                        {{ trans('file.Developed By') }} ZifalaTech</small>
                </div>
            </div>
        @endif

    </div>

    <script type="text/javascript">
        localStorage.clear();

        function auto_print() {
            window.print()
        }
        setTimeout(auto_print, 1000);
    </script>

</body>

</html>
