<div class="tab-pane mt-0 fade" id="purchased_booking_bundles" role="tabpanel" aria-labelledby="purchased_booking_bundles-tab">
    <div class="row">
        @can('admin_users_edit')
            <div class="col-12 col-md-6">
                <h5 class="section-title after-line">Add Booking Bundle to User</h5>

                <form action="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/booking-orders/store" method="post">
                    {{ csrf_field() }}
                    <input type="hidden" name="item_type" value="bundle">

                    <div class="form-group">
                        <label class="input-label">Booking Bundle</label>
                        <select name="bundle_id" class="form-control select2" data-placeholder="Search Booking Bundles">
                            <option value="">Search Booking Bundles</option>
                            @foreach($availableBookingBundles ?? [] as $bundle)
                                <option value="{{ $bundle->id }}">
                                    #{{ $bundle->id }} - {{ $bundle->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="input-label">Quantity</label>
                        <input type="number" name="quantity" min="1" value="1" class="form-control">
                    </div>

                    <div class="form-group">
                        <label class="input-label">Notes</label>
                        <textarea name="notes" rows="3" class="form-control"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <x-iconsax-lin-box class="icons text-white mr-1" width="18px" height="18px"/>
                        {{ trans('admin/main.submit') }}
                    </button>
                </form>
            </div>
        @endcan

        <div class="col-12">
            <div class="mt-5">
                <h5 class="section-title after-line">Purchased BOOKING Bundles</h5>

                <div class="table-responsive mt-3">
                    <table class="table custom-table table-md">
                        <tr>
                            <th>Booking Bundle</th>
                            <th>{{ trans('admin/main.price') }}</th>
                            <th>{{ trans('update.provider') }}</th>
                            <th class="text-center">{{ trans('admin/main.status') }}</th>
                            <th class="text-center">{{ trans('panel.purchase_date') }}</th>
                        </tr>

                        @forelse($purchasedBookingBundleItems ?? [] as $item)
                            <tr>
                                <td width="30%">{{ $item->bundle->title ?? trans('update.deleted_item') }}</td>
                                <td>{{ $item->order->currency ?? getDefaultCurrency() }} {{ number_format((float) $item->total_price, 2) }}</td>
                                <td>{{ $item->bundle->creator->full_name ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-primary">{{ $item->status }}</span>
                                </td>
                                <td class="text-center">{{ dateTimeFormat($item->created_at,'j M Y | H:i') }}</td>
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
