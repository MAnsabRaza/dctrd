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
                                                                    {{ !empty($buyer) ? $buyer->full_name : '-' }}<br>
                                                                </address>
                                                                <address>
                                                                    <strong>{{ trans('admin/main.seller') }}:</strong><br>
                                                                    {{ !empty($seller) ? $seller->full_name : '-' }}<br>
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
                                                                    @if(!empty($item))
                                                                        {{ $item->title }}
                                                                    @else
                                                                        {{ trans('update.deleted_item') }}
                                                                    @endif
                                                                    <br>
                                                                </address>

                                                                {{-- Booking resource + schedule (agar available ho) --}}
                                                                @if(!empty($order->resource) or !empty($order->booking_date))
                                                                    <address>
                                                                        <strong>{{ trans('update.booking_resource_schedule') }}:</strong><br>
                                                                        @if(!empty($order->resource))
                                                                            {{ $order->resource->name ?? $order->resource->title }}<br>
                                                                        @endif
                                                                        @if(!empty($order->booking_date))
                                                                            {{ dateTimeFormat($order->booking_date, 'j M Y') }}
                                                                            @if(!empty($order->start_time) and !empty($order->end_time))
                                                                                ({{ \Carbon\Carbon::parse($order->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($order->end_time)->format('H:i') }})
                                                                            @endif
                                                                        @endif
                                                                    </address>
                                                                @endif
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
                                                                    <td>{{ !empty($item) ? $item->id : $order->id }}</td>
                                                                    <td>{{ !empty($item) ? $item->title : trans('update.deleted_item') }}</td>
                                                                    <td class="text-center">{{ trans('update.booking') }}</td>
                                                                    <td class="text-center">
                                                                        @if(!empty($sale) and !empty($sale->amount))
                                                                            {{ handlePrice($sale->amount) }}
                                                                        @else
                                                                            {{ trans('public.free') }}
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-center">
                                                                        @if(!empty($sale) and !empty($sale->discount))
                                                                            {{ handlePrice($sale->discount) }}
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-right">
                                                                        @if(!empty($sale) and !empty($sale->total_amount))
                                                                            {{ handlePrice($sale->total_amount) }}
                                                                        @else
                                                                            0
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                                @foreach($order->extra_services as $extra)
                                                                    <tr>
                                                                        <td></td>
                                                                        <td>{{ $extra['label'] ?? '-' }}</td>
                                                                        <td class="text-center">{{ trans('update.extra_service') ?? 'Extra Service' }}</td>
                                                                        <td class="text-center">{{ handlePrice($extra['price'] ?? 0) }}</td>
                                                                        <td class="text-center">-</td>
                                                                        <td class="text-right">{{ handlePrice($extra['price'] ?? 0) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </table>
                                                        </div>
                                                        <div class="row mt-4">
                                                            <div class="col-lg-12 text-right">
                                                                <div class="invoice-detail-item">
                                                                    <div class="invoice-detail-name">{{ trans('cart.sub_total') }}</div>
                                                                    <div class="invoice-detail-value">{{ !empty($sale) ? handlePrice($sale->amount) : '-' }}</div>
                                                                </div>
                                                                   @if($order->extra_services_total > 0)
                                                                <div class="invoice-detail-item">
                                                                    <div class="invoice-detail-name">{{ trans('update.extra_services') ?? 'Extra Services' }}</div>
                                                                    <div class="invoice-detail-value">{{ handlePrice($order->extra_services_total) }}</div>
                                                                </div>
                                                                @endif
                                                                <div class="invoice-detail-item">
                                                                    <div class="invoice-detail-name">{{ trans('cart.tax') }} ({{ getFinancialSettings('tax') }}%)</div>
                                                                    <div class="invoice-detail-value">
                                                                        @if(!empty($sale) and !empty($sale->tax))
                                                                            {{ handlePrice($sale->tax) }}
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="invoice-detail-item">
                                                                    <div class="invoice-detail-name">{{ trans('public.discount') }}</div>
                                                                    <div class="invoice-detail-value">
                                                                        @if(!empty($sale) and !empty($sale->discount))
                                                                            {{ handlePrice($sale->discount) }}
                                                                        @else
                                                                            -
                                                                        @endif
                                                                    </div>
                                                            </div
                                                                <hr class="mt-2 mb-2">
                                                                <div class="invoice-detail-item">
                                                                    <div class="invoice-detail-name">{{ trans('cart.total') }}</div>
                                                                    <div class="invoice-detail-value invoice-detail-value-lg">
                                                                        @if(!empty($sale) and !empty($sale->total_amount))
                                                                            {{ handlePrice($sale->total_amount) }}
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