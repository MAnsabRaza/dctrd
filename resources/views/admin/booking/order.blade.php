@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ trans('admin/main.booking_orders') ?? 'Booking Orders' }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
            </div>
            <div class="breadcrumb-item">{{ trans('admin/main.booking_orders') ?? 'Booking Orders' }}</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        @php
                            $createActive = (
                                (!empty($errors) && $errors->any()) ||
                                !empty($editOrder) ||
                                (empty($orders) || !$orders->count())
                            );
                        @endphp

                        <ul class="nav nav-pills" role="tablist">
                            @can('admin_booking_orders')
                                <li class="nav-item">
                                    <a class="nav-link {{ !$createActive ? 'active' : '' }}" data-toggle="pill" href="#orders-list">
                                        <i class="fa fa-list"></i> {{ trans('admin/main.list') ?? 'List' }}
                                    </a>
                                </li>
                            @endcan
                            @can('admin_booking_orders_create')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? 'active' : '' }}" data-toggle="pill" href="#orders-create">
                                        <i class="fa fa-plus"></i> {{ trans('admin/main.create') ?? 'Create' }}
                                    </a>
                                </li>
                            @endcan
                        </ul>

                        <div class="tab-content mt-3">

                            {{-- ══════════════════════════════════
                                 LIST TAB
                            ══════════════════════════════════ --}}
                            @can('admin_booking_orders')
                            <div class="tab-pane fade {{ !$createActive ? 'show active' : '' }}" id="orders-list">
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('admin/main.id') ?? 'ID' }}</th>
                                                <th>{{ trans('admin/main.order_number') ?? 'Order #' }}</th>
                                                <th>{{ trans('admin/main.user') ?? 'User' }}</th>
                                                <th>{{ trans('admin/main.items') ?? 'Items' }}</th>
                                                <th>{{ trans('admin/main.total') ?? 'Total' }}</th>
                                                <th>{{ trans('admin/main.status') ?? 'Status' }}</th>
                                                <th>{{ trans('admin/main.payment_status') ?? 'Payment' }}</th>
                                                <th>{{ trans('admin/main.date') ?? 'Date' }}</th>
                                                <th>{{ trans('admin/main.actions') ?? 'Actions' }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($orders as $order)
                                            <tr>
                                                <td>{{ $order->id }}</td>
                                                <td>{{ $order->order_number }}</td>
                                                <td>{{ $order->user->name ?? '-' }}</td>
                                                <td>{{ $order->items->count() }}</td>
                                                <td>
                                                    {{ $order->currency }} {{ number_format($order->total, 2) }}
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $order->status == 'pending' ? 'warning' : ($order->status == 'confirmed' ? 'success' : ($order->status == 'cancelled' ? 'danger' : 'info')) }}">
                                                        {{ ucfirst($order->status) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $order->payment_status == 'paid' ? 'success' : ($order->payment_status == 'unpaid' ? 'danger' : 'warning') }}">
                                                        {{ ucfirst($order->payment_status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $order->created_at->format('Y-m-d') }}</td>
                                                <td>
                                                    @can('admin_booking_orders_edit')
                                                    <a href="{{ getAdminPanelUrl('/booking/order/' . $order->id . '/edit') }}" class="btn btn-sm btn-primary">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    @endcan
                                                    @can('admin_booking_orders_delete')
                                                        @include('admin.includes.delete_button', [
                                                            'url'       => getAdminPanelUrl('/booking/order/' . $order->id . '/delete'),
                                                            'btnClass'  => 'btn btn-sm btn-danger ml-1',
                                                            'btnText'   => '',
                                                            'btnIcon'   => 'trash',
                                                            'iconType'  => 'lin',
                                                            'iconClass' => 'text-white',
                                                            'tooltip'   => trans('admin/main.delete'),
                                                            'noBtnTransparent' => true,
                                                        ])
                                                    @endcan
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="9" class="text-center">
                                                    {{ trans('admin/main.no_results') ?? 'No orders found' }}
                                                </td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Pagination --}}
                                <div class="mt-3">
                                    {{ $orders->links() }}
                                </div>
                            </div>
                            @endcan

                            {{-- ══════════════════════════════════
                                 CREATE / EDIT TAB
                            ══════════════════════════════════ --}}
                            @can('admin_booking_orders_create')
                            <div class="tab-pane fade {{ $createActive ? 'show active' : '' }}" id="orders-create">

                                <form action="{{ empty($editOrder) ? getAdminPanelUrl('/booking/order/store') : getAdminPanelUrl('/booking/order/' . $editOrder->id . '/update') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ old('user_id') ?? $editOrder->user_id ?? auth()->id() }}">

                                    <div class="row">
                                        {{-- Left Column --}}
                                        <div class="col-md-6">

                                            {{-- Currency --}}
                                            <div class="form-group">
                                                <label>{{ trans('admin/main.currency') ?? 'Currency' }} <span class="text-danger">*</span></label>
                                                <select name="currency" class="form-control @error('currency') is-invalid @enderror" required>
                                                    <option value="USD" {{ (old('currency') ?? $editOrder->currency ?? 'USD') == 'USD' ? 'selected' : '' }}>USD</option>
                                                    <option value="EUR" {{ (old('currency') ?? $editOrder->currency ?? null) == 'EUR' ? 'selected' : '' }}>EUR</option>
                                                    <option value="GBP" {{ (old('currency') ?? $editOrder->currency ?? null) == 'GBP' ? 'selected' : '' }}>GBP</option>
                                                    <option value="PKR" {{ (old('currency') ?? $editOrder->currency ?? null) == 'PKR' ? 'selected' : '' }}>PKR</option>
                                                </select>
                                                @error('currency')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            {{-- Status --}}
                                            <div class="form-group">
                                                <label>{{ trans('admin/main.status') ?? 'Status' }} <span class="text-danger">*</span></label>
                                                <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                                                    <option value="pending" {{ (old('status') ?? $editOrder->status ?? 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="confirmed" {{ (old('status') ?? $editOrder->status ?? null) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                    <option value="completed" {{ (old('status') ?? $editOrder->status ?? null) == 'completed' ? 'selected' : '' }}>Completed</option>
                                                    <option value="cancelled" {{ (old('status') ?? $editOrder->status ?? null) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                    <option value="no_show" {{ (old('status') ?? $editOrder->status ?? null) == 'no_show' ? 'selected' : '' }}>No Show</option>
                                                </select>
                                                @error('status')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            {{-- Payment Status --}}
                                            <div class="form-group">
                                                <label>{{ trans('admin/main.payment_status') ?? 'Payment Status' }} <span class="text-danger">*</span></label>
                                                <select name="payment_status" class="form-control @error('payment_status') is-invalid @enderror" required>
                                                    <option value="unpaid" {{ (old('payment_status') ?? $editOrder->payment_status ?? 'unpaid') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                                    <option value="partial" {{ (old('payment_status') ?? $editOrder->payment_status ?? null) == 'partial' ? 'selected' : '' }}>Partial</option>
                                                    <option value="paid" {{ (old('payment_status') ?? $editOrder->payment_status ?? null) == 'paid' ? 'selected' : '' }}>Paid</option>
                                                    <option value="refunded" {{ (old('payment_status') ?? $editOrder->payment_status ?? null) == 'refunded' ? 'selected' : '' }}>Refunded</option>
                                                </select>
                                                @error('payment_status')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>

                                        </div>

                                        {{-- Right Column --}}
                                        <div class="col-md-6">

                                            {{-- Discount --}}
                                            <div class="form-group">
                                                <label>{{ trans('admin/main.discount') ?? 'Discount Amount' }}</label>
                                                <input type="number" name="discount_amount" class="form-control @error('discount_amount') is-invalid @enderror" step="0.01" min="0" value="{{ old('discount_amount') ?? $editOrder->discount_amount ?? 0 }}">
                                                @error('discount_amount')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            {{-- Tax --}}
                                            <div class="form-group">
                                                <label>{{ trans('admin/main.tax') ?? 'Tax Amount' }}</label>
                                                <input type="number" name="tax_amount" class="form-control @error('tax_amount') is-invalid @enderror" step="0.01" min="0" value="{{ old('tax_amount') ?? $editOrder->tax_amount ?? 0 }}">
                                                @error('tax_amount')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>

                                            {{-- Notes --}}
                                            <div class="form-group">
                                                <label>{{ trans('admin/main.notes') ?? 'Notes' }}</label>
                                                <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') ?? $editOrder->notes ?? '' }}</textarea>
                                                @error('notes')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>

                                        </div>
                                    </div>

                                    {{-- ══════════════════════════════════
                                         ORDER ITEMS SECTION
                                    ══════════════════════════════════ --}}

                                    <div class="card mt-4">
                                        <div class="card-header">
                                            <h6 class="card-title">{{ trans('admin/main.items') ?? 'Order Items' }}</h6>
                                            <div class="card-header-action">
                                                <button type="button" class="btn btn-sm btn-primary" id="add-item">
                                                    <i class="fa fa-plus"></i> {{ trans('admin/main.add') ?? 'Add Item' }}
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 150px;">{{ trans('admin/main.type') ?? 'Type' }}</th>
                                                        <th style="width: 200px;">{{ trans('admin/main.item') ?? 'Item' }}</th>
                                                        <th style="width: 150px;">{{ trans('admin/main.resource') ?? 'Resource' }}</th>
                                                        <th style="width: 120px;">{{ trans('admin/main.date') ?? 'Date' }}</th>
                                                        <th style="width: 100px;">{{ trans('admin/main.start_time') ?? 'Start' }}</th>
                                                        <th style="width: 100px;">{{ trans('admin/main.end_time') ?? 'End' }}</th>
                                                        <th style="width: 80px;">{{ trans('admin/main.qty') ?? 'Qty' }}</th>
                                                        <th style="width: 80px;">{{ trans('admin/main.persons') ?? 'Persons' }}</th>
                                                        <th style="width: 100px;">{{ trans('admin/main.unit_price') ?? 'Unit Price' }}</th>
                                                        <th style="width: 100px;">{{ trans('admin/main.total_price') ?? 'Total' }}</th>
                                                        <th style="width: 150px;">{{ trans('admin/main.status') ?? 'Status' }}</th>
                                                        <th style="width: 60px;"></th>
                                                    </tr>
                                                </thead>
                                                <tbody id="items-body">
                                                    @if(!empty($editOrder) && $editOrder->items->count())
                                                        @foreach($editOrder->items as $index => $item)
                                                        <tr class="item-row">
                                                            <td>
                                                                <select name="item_type[]" class="form-control item-type" required>
                                                                    <option value="">Select</option>
                                                                    <option value="booking" {{ $item->item_type == 'booking' ? 'selected' : '' }}>Booking</option>
                                                                    <option value="bundle" {{ $item->item_type == 'bundle' ? 'selected' : '' }}>Bundle</option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <select name="item_id[]" class="form-control item-select" required>
                                                                    <option value="">Select</option>
                                                                    @if($item->item_type == 'booking' && $item->booking_id)
                                                                        <option value="{{ $item->booking_id }}" selected>
                                                                            {{ $item->booking->title ?? 'Booking #' . $item->booking_id }}
                                                                        </option>
                                                                    @elseif($item->item_type == 'bundle' && $item->bundle_id)
                                                                        <option value="{{ $item->bundle_id }}" selected>
                                                                            {{ $item->bundle->title ?? 'Bundle #' . $item->bundle_id }}
                                                                        </option>
                                                                    @endif
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <select name="resource_id[]" class="form-control">
                                                                    <option value="">Select</option>
                                                                    @foreach($resources as $resource)
                                                                        <option value="{{ $resource->id }}" {{ $item->resource_id == $resource->id ? 'selected' : '' }}>
                                                                            {{ $resource->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input type="date" name="booking_date[]" class="form-control" value="{{ $item->booking_date ? $item->booking_date->format('Y-m-d') : '' }}">
                                                            </td>
                                                            <td>
                                                                <input type="time" name="start_time[]" class="form-control" value="{{ $item->start_time ? substr($item->start_time, 0, 5) : '' }}">
                                                            </td>
                                                            <td>
                                                                <input type="time" name="end_time[]" class="form-control" value="{{ $item->end_time ? substr($item->end_time, 0, 5) : '' }}">
                                                            </td>
                                                            <td>
                                                                <input type="number" name="quantity[]" class="form-control item-qty" min="1" value="{{ $item->quantity }}">
                                                            </td>
                                                            <td>
                                                                <input type="number" name="persons[]" class="form-control" min="1" value="{{ $item->persons }}">
                                                            </td>
                                                            <td>
                                                                <input type="number" name="unit_price[]" class="form-control item-price" step="0.01" min="0" value="{{ $item->unit_price }}">
                                                            </td>
                                                            <td>
                                                                <input type="number" name="total_price[]" class="form-control item-total" step="0.01" min="0" value="{{ $item->total_price }}" readonly>
                                                            </td>
                                                            <td>
                                                                <select name="item_status[]" class="form-control">
                                                                    <option value="pending" {{ $item->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                                    <option value="confirmed" {{ $item->status == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                                                    <option value="completed" {{ $item->status == 'completed' ? 'selected' : '' }}>Completed</option>
                                                                    <option value="cancelled" {{ $item->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                                    <option value="no_show" {{ $item->status == 'no_show' ? 'selected' : '' }}>No Show</option>
                                                                </select>
                                                            </td>
                                                            <td class="text-center align-middle">
                                                                <button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Remove">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    @else
                                                    <tr class="item-row">
                                                        <td>
                                                            <select name="item_type[]" class="form-control item-type" required>
                                                                <option value="">Select</option>
                                                                <option value="booking">Booking</option>
                                                                <option value="bundle">Bundle</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select name="item_id[]" class="form-control item-select" required>
                                                                <option value="">Select Item Type First</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select name="resource_id[]" class="form-control">
                                                                <option value="">Select</option>
                                                                @foreach($resources as $resource)
                                                                    <option value="{{ $resource->id }}">{{ $resource->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="date" name="booking_date[]" class="form-control">
                                                        </td>
                                                        <td>
                                                            <input type="time" name="start_time[]" class="form-control">
                                                        </td>
                                                        <td>
                                                            <input type="time" name="end_time[]" class="form-control">
                                                        </td>
                                                        <td>
                                                            <input type="number" name="quantity[]" class="form-control item-qty" min="1" value="1">
                                                        </td>
                                                        <td>
                                                            <input type="number" name="persons[]" class="form-control" min="1" value="1">
                                                        </td>
                                                        <td>
                                                            <input type="number" name="unit_price[]" class="form-control item-price" step="0.01" min="0" value="0">
                                                        </td>
                                                        <td>
                                                            <input type="number" name="total_price[]" class="form-control item-total" step="0.01" min="0" value="0" readonly>
                                                        </td>
                                                        <td>
                                                            <select name="item_status[]" class="form-control">
                                                                <option value="pending">Pending</option>
                                                                <option value="confirmed">Confirmed</option>
                                                                <option value="completed">Completed</option>
                                                                <option value="cancelled">Cancelled</option>
                                                                <option value="no_show">No Show</option>
                                                            </select>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Remove">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {{-- Form Actions --}}
                                    <div class="form-group mt-4">
                                        <button type="submit" class="btn btn-success">
                                            <i class="fa fa-save"></i> {{ trans('admin/main.save') ?? 'Save' }}
                                        </button>
                                        <a href="{{ getAdminPanelUrl('/booking/order') }}" class="btn btn-secondary">
                                            <i class="fa fa-times"></i> {{ trans('admin/main.cancel') ?? 'Cancel' }}
                                        </a>
                                    </div>

                                </form>

                            </div>
                            @endcan

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts_bottom')
<script>
(function () {

    // ── Add item row ──────────────────────────────────────────────
    const itemsBody  = document.getElementById('items-body');
    const addItemBtn = document.getElementById('add-item');

    const bookingItems = @json($bookings->values());
    const bundleItems = @json($bundles->values());
    const resourceItems = @json($resources->values());

    function escapeHtml(value) {
        return String(value ?? '').replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function buildOptions(items, placeholder, emptyText) {
        let options = '<option value="">' + escapeHtml(placeholder) + '</option>';

        if (!items.length && emptyText) {
            options += '<option value="" disabled>' + escapeHtml(emptyText) + '</option>';
        }

        items.forEach(function (item) {
            options += '<option value="' + escapeHtml(item.id) + '">' + escapeHtml(item.title || item.name || ('#' + item.id)) + '</option>';
        });

        return options;
    }

    const resourcesHtml = buildOptions(resourceItems, 'Select', 'No resources found');

    function buildRow() {
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML =
            '<td>' +
                '<select name="item_type[]" class="form-control item-type" required>' +
                    '<option value="">Select</option>' +
                    '<option value="booking">Booking</option>' +
                    '<option value="bundle">Bundle</option>' +
                '</select>' +
            '</td>' +
            '<td>' +
                '<select name="item_id[]" class="form-control item-select" required>' +
                    '<option value="">Select Item Type First</option>' +
                '</select>' +
            '</td>' +
            '<td>' +
                '<select name="resource_id[]" class="form-control">' +
                    resourcesHtml +
                '</select>' +
            '</td>' +
            '<td>' +
                '<input type="date" name="booking_date[]" class="form-control">' +
            '</td>' +
            '<td>' +
                '<input type="time" name="start_time[]" class="form-control">' +
            '</td>' +
            '<td>' +
                '<input type="time" name="end_time[]" class="form-control">' +
            '</td>' +
            '<td>' +
                '<input type="number" name="quantity[]" class="form-control item-qty" min="1" value="1">' +
            '</td>' +
            '<td>' +
                '<input type="number" name="persons[]" class="form-control" min="1" value="1">' +
            '</td>' +
            '<td>' +
                '<input type="number" name="unit_price[]" class="form-control item-price" step="0.01" min="0" value="0">' +
            '</td>' +
            '<td>' +
                '<input type="number" name="total_price[]" class="form-control item-total" step="0.01" min="0" value="0" readonly>' +
            '</td>' +
            '<td>' +
                '<select name="item_status[]" class="form-control">' +
                    '<option value="pending">Pending</option>' +
                    '<option value="confirmed">Confirmed</option>' +
                    '<option value="completed">Completed</option>' +
                    '<option value="cancelled">Cancelled</option>' +
                    '<option value="no_show">No Show</option>' +
                '</select>' +
            '</td>' +
            '<td class="text-center align-middle">' +
                '<button type="button" class="btn btn-sm btn-outline-danger remove-item" title="Remove">' +
                    '<i class="fas fa-times"></i>' +
                '</button>' +
            '</td>';
        return tr;
    }

    if (addItemBtn) {
        addItemBtn.addEventListener('click', function () {
            const row = buildRow();
            itemsBody.appendChild(row);
            attachEventListeners(row);
            row.querySelector('select[name="item_type[]"]').focus();
        });
    }

    // ── Item type change - load items ──────────────────────────────
    function attachEventListeners(row) {
        const typeSelect = row.querySelector('.item-type');
        const itemSelect = row.querySelector('.item-select');
        const qtyInput = row.querySelector('.item-qty');
        const priceInput = row.querySelector('.item-price');
        const totalInput = row.querySelector('.item-total');

        if (typeSelect) {
            typeSelect.addEventListener('change', function () {
                const type = this.value;
                itemSelect.innerHTML = '<option value="">Loading...</option>';

                if (type === 'booking') {
                    itemSelect.innerHTML = buildOptions(bookingItems, 'Select Booking', 'No bookings found');
                } else if (type === 'bundle') {
                    itemSelect.innerHTML = buildOptions(bundleItems, 'Select Bundle', 'No bundles found');
                } else {
                    itemSelect.innerHTML = '<option value="">Select Item Type First</option>';
                }
            });
        }

        // Calculate total price = quantity × unit price
        if (qtyInput && priceInput) {
            const calculateTotal = () => {
                const qty = parseFloat(qtyInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                totalInput.value = (qty * price).toFixed(2);
            };

            qtyInput.addEventListener('input', calculateTotal);
            priceInput.addEventListener('input', calculateTotal);
        }
    }

    // Attach to existing rows
    document.querySelectorAll('.item-row').forEach(row => {
        attachEventListeners(row);
    });

    // ── Remove item row ───────────────────────────────────────────
    if (itemsBody) {
        itemsBody.addEventListener('click', function (e) {
            const btn = e.target.closest('.remove-item');
            if (!btn) return;

            const rows = itemsBody.querySelectorAll('.item-row');
            if (rows.length > 1) {
                btn.closest('.item-row').remove();
            } else {
                // Reset last row
                const row = btn.closest('.item-row');
                row.querySelector('select[name="item_type[]"]').value = '';
                row.querySelector('select[name="item_id[]"]').value = '';
                row.querySelector('input[name="booking_date[]"]').value = '';
                row.querySelector('input[name="start_time[]"]').value = '';
                row.querySelector('input[name="end_time[]"]').value = '';
                row.querySelector('.item-qty').value = 1;
                row.querySelector('input[name="persons[]"]').value = 1;
                row.querySelector('.item-price').value = 0;
                row.querySelector('.item-total').value = 0;
            }
        });
    }

})();
</script>
@endpush
