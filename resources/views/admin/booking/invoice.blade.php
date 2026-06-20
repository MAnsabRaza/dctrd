<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>{{ $pageTitle ?? '' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="/assets/admin/vendor/bootstrap/bootstrap.min.css"/>
    <link rel="stylesheet" href="/assets/vendors/fontawesome/css/all.min.css"/>
    <link rel="stylesheet" href="/assets/admin/css/style.css">
    <link rel="stylesheet" href="/assets/admin/css/custom.css">
    <link rel="stylesheet" href="/assets/admin/css/components.css">
    <style>
        @php
            $themeCustomCssAndJs = getThemeCustomCssAndJs();
        @endphp
        {!! !empty($themeCustomCssAndJs['css']) ? $themeCustomCssAndJs['css'] : '' !!}
    </style>
</head>
<body>
<div id="app">
    <section class="section">
        <div class="container mt-5">
            <div class="row">
                <div class="col-12 col-md-10 offset-md-1 col-lg-10 offset-lg-1">
                    <div class="card card-primary">
                        <div class="row m-0">
                            <div class="col-12 col-md-12">
                                <div class="card-body">
                                    <div class="section-body">
                                        <div class="invoice">
                                            <div class="invoice-print">
                                                <div class="row">
                                                    <div class="col-lg-12">
                                                        <div class="invoice-title">
                                                            <h2>{{ $generalSettings['site_name'] }}</h2>
                                                            <div class="invoice-number">{{ trans('public.item_id') }}: #{{ $order->id }}</div>
                                                            <div class="text-small text-muted">{{ trans('update.display_currency') }}: {{ getUserCurrency() }}</div>
                                                        </div>
                                                        <hr>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <address>
                                                                    <strong>{{ trans('quiz.student') }}:</strong><br>
                                                                    {{ !empty($order->buyer) ? $order->buyer->full_name : '-' }}<br>
                                                                </address>
                                                                <address>
                                                                    <strong>{{ trans('admin/main.seller') }}:</strong><br>
                                                                    {{ !empty($order->seller) ? $order->seller->full_name : '-' }}<br>
                                                                </address>
                                                            </div>
                                                            <div class="col-md-6 text-md-right">
                                                                <address>
                                                                    <strong>{{ trans('home.platform_address') }}:</strong><br>
                                                                    {!! nl2br(getContactPageSettings('address')) !!}
                                                                </address>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <address>
                                                                    <strong>{{ trans('admin/main.item') }}:</strong><br>
                                                                    @if(!empty($order->booking))
                                                                        {{ $order->booking->title }}
                                                                    @else
                                                                        {{ trans('update.deleted_item') }}
                                                                    @endif
                                                                    <br>
                                                                </address>
                                                            </div>
                                                            <div class="col-md-6 text-md-right">
                                                                <address>
                                                                    <strong>{{ trans('panel.purchase_date') }}:</strong><br>
                                                                    {{ dateTimeFormat($order->created_at,'Y M j | H:i') }}<br><br>
                                                                </address>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row mt-4">
                                                    <div class="col-md-12">
                                                        <div class="section-title">{{ trans('home.order_summary') }}</div>
                                                        <div class="table-responsive">
                                                            <table class="table custom-table table-hover table-md">
                                                                <tr>
                                                                    <th data-width="40">#</th>
                                                                    <th>{{ trans('cart.item') }}</th>
                                                                    <th class="text-center">{{ trans('admin/main.type') }}</th>
                                                                    <th class="text-center">{{ trans('public.price') }}</th>
                                                                    <th class="text-center">{{ trans('panel.discount') }}</th>
                                                                    <th class="text-right">{{ trans('cart.total') }}</th>
                                                                </tr>
                                                                <tr>
                                                                    <td>{{ !empty($order->booking) ? $order->booking->id : $order->id }}</td>
                                                                    <td>{{ !empty($order->booking) ? $order->booking->title : trans('update.deleted_item') }}</td>
                                                                    <td class="text-center">{{ trans('update.booking') }}</td>
                                                                    <td class="text-center">
                                                                        @if(!empty($order->sale) and !empty($order->sale->amount))
                                                                            {{ handlePrice($order->sale->amount) }}
                                                                        @else
                                                                            {{ trans('public.free') }}
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-center">
                                                                        @if(!empty($order->sale) and !empty($order->sale->discount))
                                                                            {{ handlePrice($order->sale->discount) }}
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-right">
                                                                        @if(!empty($order->sale) and !empty($order->sale->total_amount))
                                                                            {{ handlePrice($order->sale->total_amount) }}
                                                                        @else
                                                                            0
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                        <div class="row mt-4">
                                                            <div class="col-lg-12 text-right">
                                                                <div class="invoice-detail-item">
                                                                    <div class="invoice-detail-name">{{ trans('cart.sub_total') }}</div>
                                                                    <div class="invoice-detail-value">{{ !empty($order->sale) ? handlePrice($order->sale->amount) : '-' }}</div>
                                                                </div>
                                                                <div class="invoice-detail-item">
                                                                    <div class="invoice-detail-name">{{ trans('cart.tax') }} ({{ getFinancialSettings('tax') }}%)</div>
                                                                    <div class="invoice-detail-value">
                                                                        @if(!empty($order->sale) and !empty($order->sale->tax))
                                                                            {{ handlePrice($order->sale->tax) }}
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="invoice-detail-item">
                                                                    <div class="invoice-detail-name">{{ trans('public.discount') }}</div>
                                                                    <div class="invoice-detail-value">
                                                                        @if(!empty($order->sale) and !empty($order->sale->discount))
                                                                            {{ handlePrice($order->sale->discount) }}
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <hr class="mt-2 mb-2">
                                                                <div class="invoice-detail-item">
                                                                    <div class="invoice-detail-name">{{ trans('cart.total') }}</div>
                                                                    <div class="invoice-detail-value invoice-detail-value-lg">
                                                                        @if(!empty($order->sale) and !empty($order->sale->total_amount))
                                                                            {{ handlePrice($order->sale->total_amount) }}
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="text-md-right">
                                                <button type="button" onclick="window.print()" class="btn btn-warning btn-icon icon-left"><i class="fas fa-print"></i> Print</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
</body>
</html>
