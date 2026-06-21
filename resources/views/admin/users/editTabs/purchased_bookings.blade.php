<div class="tab-pane mt-0 fade {{ (request()->get('tab') == 'purchased_bookings') ? 'active show' : '' }}" id="purchased_bookings" role="tabpanel" aria-labelledby="purchased_bookings-tab">
    <div class="row">
        @can('admin_users_edit')
            <div class="col-12 col-md-6">
                <h5 class="section-title after-line">Add Booking to User</h5>

                <form action="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/booking-orders/store" method="post">
                    {{ csrf_field() }}
                    <input type="hidden" name="item_type" value="booking">

                    <div class="form-group">
                        <label class="input-label">Booking</label>
                        <select name="booking_id" class="form-control select2" data-placeholder="Search Bookings">
                            <option value="">Search Bookings</option>
                            @foreach($availableBookings ?? [] as $booking)
                                <option value="{{ $booking->id }}">
                                    #{{ $booking->id }} - {{ $booking->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="input-label">Booking Date</label>
                                <input type="date" name="booking_date" class="form-control">
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group">
                                <label class="input-label">Persons</label>
                                <input type="number" name="persons" min="1" value="1" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="input-label">Notes</label>
                        <textarea name="notes" rows="3" class="form-control"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <x-iconsax-lin-calendar-add class="icons text-white mr-1" width="18px" height="18px"/>
                        {{ trans('admin/main.submit') }}
                    </button>
                </form>
            </div>
        @endcan

        <div class="col-12">
            <div class="mt-5">
                <h5 class="section-title after-line">Manually Added Bookings</h5>

                <div class="table-responsive mt-3">
                    <table class="table custom-table table-md">
                        <tr>
                            <th>Booking</th>
                            <th>{{ trans('admin/main.price') }}</th>
                            <th>{{ trans('update.provider') }}</th>
                            <th class="text-center">{{ trans('panel.purchase_date') }}</th>
                            @can('admin_users_edit')
                                <th class="text-center">{{ trans('admin/main.actions') }}</th>
                            @endcan
                        </tr>

                        @forelse($manualAddedBookings ?? [] as $order)
                            <tr>
                                <td width="30%">
                                    <a href="{{ !empty($order->booking) ? $order->booking->getUrl() : '#!' }}" target="_blank" class="text-dark">
                                        {{ !empty($order->booking) ? $order->booking->title : trans('update.deleted_item') }}
                                    </a>
                                </td>
                                <td>
                                    @php
                                        $unitPrice = !empty($order->booking) ? ($order->booking->discount_price ?: $order->booking->price) : 0;
                                    @endphp
                                    {{ !empty($order->booking) ? $order->booking->currency : getDefaultCurrency() }} {{ number_format((float) $unitPrice * $order->quantity, 2) }}
                                </td>
                                <td>{{ $order->booking->creator->full_name ?? '-' }}</td>
                                <td class="text-center">{{ dateTimeFormat($order->created_at,'j M Y | H:i') }}</td>
                                @can('admin_users_edit')
                                    <td class="text-center">
                                        <form action="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/booking-orders/{{ $order->id }}/delete" method="post" onsubmit="return confirm('{{ trans('admin/main.are_you_sure') }}');">
                                            {{ csrf_field() }}
                                            <button type="submit" class="btn btn-sm btn-icon" title="Remove">
                                                <x-iconsax-lin-trash class="icons text-danger" width="18px" height="18px"/>
                                            </button>
                                        </form>
                                    </td>
                                @endcan
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">{{ trans('admin/main.no_result') }}</td>
                            </tr>
                        @endforelse
                    </table>
                </div>
            </div>

            <div class="mt-5">
                <h5 class="section-title after-line">Manually Removed Bookings</h5>

                <div class="table-responsive mt-3">
                    <table class="table custom-table table-md">
                        <tr>
                            <th>Booking</th>
                            <th>{{ trans('admin/main.price') }}</th>
                            <th>{{ trans('update.provider') }}</th>
                            <th class="text-center">{{ trans('panel.purchase_date') }}</th>
                        </tr>

                        @forelse($manualRemovedBookings ?? [] as $order)
                            <tr class="text-muted">
                                <td width="30%">
                                    {{ !empty($order->booking) ? $order->booking->title : trans('update.deleted_item') }}
                                </td>
                                <td>
                                    @php
                                        $unitPrice = !empty($order->booking) ? ($order->booking->discount_price ?: $order->booking->price) : 0;
                                    @endphp
                                    {{ !empty($order->booking) ? $order->booking->currency : getDefaultCurrency() }} {{ number_format((float) $unitPrice * $order->quantity, 2) }}
                                </td>
                                <td>{{ $order->booking->creator->full_name ?? '-' }}</td>
                                <td class="text-center">{{ dateTimeFormat($order->created_at,'j M Y | H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">{{ trans('admin/main.no_result') }}</td>
                            </tr>
                        @endforelse
                    </table>
                </div>
            </div>

            <div class="mt-5">
                <h5 class="section-title after-line">Purchased Bookings</h5>

                <div class="table-responsive mt-3">
                    <table class="table custom-table table-md">
                        <tr>
                            <th>Booking</th>
                            <th>{{ trans('admin/main.price') }}</th>
                            <th>{{ trans('update.provider') }}</th>
                            <th class="text-center">{{ trans('admin/main.status') }}</th>
                            <th class="text-center">{{ trans('panel.purchase_date') }}</th>
                        </tr>

                        @forelse($purchasedBookings ?? [] as $order)
                            <tr>
                                <td width="30%">
                                    <a href="{{ !empty($order->booking) ? $order->booking->getUrl() : '#!' }}" target="_blank" class="text-dark">
                                        {{ !empty($order->booking) ? $order->booking->title : trans('update.deleted_item') }}
                                    </a>
                                </td>
                                <td>{{ optional($order->sale)->total_amount ? handlePrice($order->sale->total_amount) : '-' }}</td>
                                <td>{{ $order->booking->creator->full_name ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-primary">{{ $order->status }}</span>
                                </td>
                                <td class="text-center">{{ dateTimeFormat($order->created_at,'j M Y | H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">{{ trans('admin/main.no_result') }}</td>
                            </tr>
                        @endforelse
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
