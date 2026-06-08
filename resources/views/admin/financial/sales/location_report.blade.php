@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item"><a href="{{ getAdminPanelUrl() }}/financial/sales">{{ trans('admin/main.sales') }}</a></div>
                <div class="breadcrumb-item">{{ $pageTitle }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header justify-content-between">
                    <div>
                        <h5 class="font-14 mb-0">{{ $pageTitle }}</h5>
                        <p class="font-12 mt-4 mb-0 text-gray-500">Orders grouped by user {{ strtolower($locationLabel) }}.</p>
                    </div>

                    <a href="{{ $exportUrl }}" class="btn bg-white bg-hover-gray-100 border-gray-400 text-gray-500">
                        <x-iconsax-lin-import-2 class="icons text-gray-500" width="18px" height="18px"/>
                        <span class="ml-4 font-12">Export CSV</span>
                    </a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table custom-table font-14">
                            <tr>
                                <th class="text-left">{{ $locationLabel }}</th>
                                <th>{{ trans('admin/main.orders') ?? 'Total Orders' }}</th>
                                <th>{{ trans('admin/main.revenue') ?? 'Total Revenue' }}</th>
                            </tr>

                            @forelse($rows as $row)
                                <tr>
                                    <td class="text-left">{{ $row->{$locationKey} }}</td>
                                    <td>{{ $row->total_orders }}</td>
                                    <td>{{ handlePrice($row->total_revenue) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-gray-500">No sales found.</td>
                                </tr>
                            @endforelse
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
