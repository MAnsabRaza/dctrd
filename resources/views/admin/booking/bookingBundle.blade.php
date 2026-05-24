@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ trans('admin/main.admin_booking_bundle') }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
            </div>
            <div class="breadcrumb-item">{{ trans('admin/main.admin_booking_bundle') }}</div>
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
                                !empty($editBundle) ||
                                (empty($bundles) || !$bundles->count())
                            );
                        @endphp

                        <ul class="nav nav-pills" role="tablist">
                            @can('admin_booking_bundle')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? '' : 'active' }}"
                                       data-toggle="tab" href="#listTab" role="tab">
                                        {{ trans('admin/main.admin_booking_bundle') }}
                                    </a>
                                </li>
                            @endcan
                            @can('admin_booking_bundle_create')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? 'active' : '' }}"
                                       data-toggle="tab" href="#createTab" role="tab">
                                        {{ !empty($editBundle) ? trans('admin/main.edit') : trans('admin/main.create_booking_bundle') }}
                                    </a>
                                </li>
                            @endcan
                        </ul>

                        <div class="tab-content mt-3">

                            {{-- ══════════════════════════════════
                                 LIST TAB
                            ══════════════════════════════════ --}}
                            @can('admin_booking_bundle')
                                <div class="tab-pane fade {{ $createActive ? '' : 'active show' }}"
                                     id="listTab" role="tabpanel">

                                    @if(!empty($bundles) && $bundles->count())
                                        <div class="table-responsive">
                                            <table class="table custom-table font-14">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{ trans('admin/main.title') }}</th>
                                                        <th class="text-center">Items</th>
                                                        <th class="text-center">Price</th>
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center">Featured</th>
                                                        <th>{{ trans('admin/main.action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($bundles as $bundle)
                                                        <tr>
                                                            <td>{{ $bundle->id }}</td>
                                                            <td>
                                                                @if($bundle->thumbnail)
                                                                    <img src="{{ $bundle->thumbnail }}" width="36" height="36"
                                                                         class="rounded mr-2" style="object-fit:cover;">
                                                                @endif
                                                                {{ $bundle->title }}
                                                            </td>
                                                            <td class="text-center">
                                                                @foreach($bundle->items as $item)
                                                                    <span class="badge badge-info mr-1">
                                                                        {{ $item->booking?->title ?? '#'.$item->booking_id }}
                                                                        <small class="ml-1">×{{ $item->quantity }}</small>
                                                                    </span>
                                                                @endforeach
                                                            </td>
                                                            <td class="text-center">
                                                                {{ $bundle->currency }} {{ number_format($bundle->price, 2) }}
                                                                @if($bundle->discount_price)
                                                                    <br><small class="text-danger">
                                                                        {{ $bundle->currency }} {{ number_format($bundle->discount_price, 2) }}
                                                                    </small>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                @php
                                                                    $statusClass = match($bundle->status) {
                                                                        'published' => 'success',
                                                                        'draft'     => 'secondary',
                                                                        'pending'   => 'warning',
                                                                        'rejected'  => 'danger',
                                                                        default     => 'dark',
                                                                    };
                                                                @endphp
                                                                <span class="badge badge-{{ $statusClass }}">
                                                                    {{ ucfirst($bundle->status) }}
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                @if($bundle->featured)
                                                                    <span class="badge badge-warning">Yes</span>
                                                                @else
                                                                    <span class="badge badge-secondary">No</span>
                                                                @endif
                                                            </td>
                                                            <td width="80px">
                                                                <div class="btn-group dropdown table-actions position-relative">
                                                                    <button type="button"
                                                                            class="btn-transparent dropdown-toggle"
                                                                            data-toggle="dropdown">
                                                                        <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
                                                                    </button>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        @can('admin_booking_bundle_edit')
                                                                            <a href="{{ getAdminPanelUrl() }}/booking/bundle/{{ $bundle->id }}/edit"
                                                                               class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                                <x-iconsax-lin-edit-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                                                <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                                                                            </a>
                                                                        @endcan
                                                                        @can('admin_booking_bundle_delete')
                                                                            @include('admin.includes.delete_button', [
                                                                                'url'       => getAdminPanelUrl() . '/booking/bundle/' . $bundle->id . '/delete',
                                                                                'btnClass'  => 'dropdown-item text-danger mb-0 py-3 px-0 font-14',
                                                                                'btnText'   => trans('admin/main.delete'),
                                                                                'btnIcon'   => 'trash',
                                                                                'iconType'  => 'lin',
                                                                                'iconClass' => 'text-danger mr-2',
                                                                            ])
                                                                        @endcan
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        {{ $bundles->links() }}
                                    @else
                                        <div class="text-center text-gray-500 mt-30">
                                            {{ trans('admin/main.no_result') }}
                                        </div>
                                    @endif
                                </div>
                            @endcan

                            {{-- ══════════════════════════════════
                                 CREATE / EDIT TAB
                            ══════════════════════════════════ --}}
                            @can('admin_booking_bundle_create')
                                <div class="tab-pane fade {{ $createActive ? 'active show' : '' }}"
                                     id="createTab" role="tabpanel">
                                    <div class="row">
                                        <div class="col-12 col-md-8">

                                            <form action="{{ getAdminPanelUrl() }}/booking/bundle/{{ !empty($editBundle) ? $editBundle->id . '/update' : 'store' }}"
                                                  method="post">
                                                {{ csrf_field() }}

                                                {{-- Language --}}
                                                <div class="form-group">
                                                    <label>{{ trans('auth.language') }}</label>
                                                    <select name="language" data-plugin-selectTwo
                                                            class="form-control @error('language') is-invalid @enderror">
                                                        @foreach($userLanguages ?? [app()->getLocale() => ucfirst(app()->getLocale())] as $lang => $language)
                                                            <option value="{{ $lang }}"
                                                                {{ old('language', !empty($editBundle) ? $editBundle->language : app()->getLocale()) == $lang ? 'selected' : '' }}>
                                                                {{ $language }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('language')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Title --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.title') }} <span class="text-danger">*</span></label>
                                                    <input type="text" name="title"
                                                           class="form-control @error('title') is-invalid @enderror"
                                                           value="{{ !empty($editBundle) ? $editBundle->title : old('title') }}"
                                                           placeholder="Bundle title"/>
                                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Slug --}}
                                                <div class="form-group">
                                                    <label>Slug <small class="text-muted">— auto-generated from title</small></label>
                                                    <input type="text" name="slug" id="bundle-slug"
                                                           class="form-control @error('slug') is-invalid @enderror"
                                                           value="{{ !empty($editBundle) ? $editBundle->slug : old('slug') }}"
                                                           placeholder="auto-generated"/>
                                                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Description --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.description') }}</label>
                                                    <textarea name="description" rows="3"
                                                              class="form-control @error('description') is-invalid @enderror"
                                                              placeholder="Bundle description">{{ !empty($editBundle) ? $editBundle->description : old('description') }}</textarea>
                                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                <div class="row">
                                                    {{-- Price --}}
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Price <span class="text-danger">*</span></label>
                                                            <input type="number" name="price" min="0" step="0.01"
                                                                   class="form-control @error('price') is-invalid @enderror"
                                                                   value="{{ !empty($editBundle) ? $editBundle->price : old('price', 0) }}"/>
                                                            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                        </div>
                                                    </div>
                                                    {{-- Discount Price --}}
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Discount Price</label>
                                                            <input type="number" name="discount_price" min="0" step="0.01"
                                                                   class="form-control @error('discount_price') is-invalid @enderror"
                                                                   value="{{ !empty($editBundle) ? $editBundle->discount_price : old('discount_price') }}"/>
                                                            @error('discount_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                        </div>
                                                    </div>
                                                    {{-- Currency --}}
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Currency</label>
                                                            <input type="text" name="currency" maxlength="10"
                                                                   class="form-control @error('currency') is-invalid @enderror"
                                                                   value="{{ !empty($editBundle) ? $editBundle->currency : old('currency', 'USD') }}"/>
                                                            @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    {{-- Validity Days --}}
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Validity Days <small class="text-muted">(optional)</small></label>
                                                            <input type="number" name="validity_days" min="1"
                                                                   class="form-control @error('validity_days') is-invalid @enderror"
                                                                   value="{{ !empty($editBundle) ? $editBundle->validity_days : old('validity_days') }}"/>
                                                            @error('validity_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                        </div>
                                                    </div>
                                                    {{-- Availability Status --}}
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Availability Status</label>
                                                            @php $selAvail = !empty($editBundle) ? $editBundle->availability_status : old('availability_status', 'medium'); @endphp
                                                            <select name="availability_status"
                                                                    class="form-control @error('availability_status') is-invalid @enderror">
                                                                <option value="high"   {{ $selAvail === 'high'   ? 'selected' : '' }}>High</option>
                                                                <option value="medium" {{ $selAvail === 'medium' ? 'selected' : '' }}>Medium</option>
                                                                <option value="low"    {{ $selAvail === 'low'    ? 'selected' : '' }}>Low</option>
                                                                <option value="sold_out" {{ $selAvail === 'sold_out' ? 'selected' : '' }}>Sold Out</option>
                                                            </select>
                                                            @error('availability_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                        </div>
                                                    </div>
                                                    {{-- Status --}}
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Status <span class="text-danger">*</span></label>
                                                            @php $selStatus = !empty($editBundle) ? $editBundle->status : old('status', 'draft'); @endphp
                                                            <select name="status"
                                                                    class="form-control @error('status') is-invalid @enderror">
                                                                <option value="draft"     {{ $selStatus === 'draft'     ? 'selected' : '' }}>Draft</option>
                                                                <option value="pending"   {{ $selStatus === 'pending'   ? 'selected' : '' }}>Pending</option>
                                                                <option value="published" {{ $selStatus === 'published' ? 'selected' : '' }}>Published</option>
                                                                <option value="rejected"  {{ $selStatus === 'rejected'  ? 'selected' : '' }}>Rejected</option>
                                                                <option value="inactive"  {{ $selStatus === 'inactive'  ? 'selected' : '' }}>Inactive</option>
                                                            </select>
                                                            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Thumbnail --}}
                                                <div class="form-group">
                                                    <label>Thumbnail</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <button type="button"
                                                                    class="input-group-text admin-file-manager"
                                                                    data-input="thumbnail" data-preview="thumb-preview">
                                                                <i class="fa fa-upload"></i>
                                                            </button>
                                                        </div>
                                                        <input type="text" name="thumbnail" id="thumbnail"
                                                               class="form-control @error('thumbnail') is-invalid @enderror"
                                                               value="{{ !empty($editBundle) ? $editBundle->thumbnail : old('thumbnail') }}"/>
                                                        <div class="input-group-append">
                                                            <button type="button" class="input-group-text admin-file-view" data-input="thumbnail">
                                                                <i class="fa fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    @if(!empty($editBundle) && $editBundle->thumbnail)
                                                        <img id="thumb-preview" src="{{ $editBundle->thumbnail }}" width="80" class="rounded mt-2">
                                                    @endif
                                                    @error('thumbnail')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Featured --}}
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="featured"
                                                               class="custom-control-input" id="featured"
                                                               {{ (!empty($editBundle) && $editBundle->featured) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="featured">Featured</label>
                                                    </div>
                                                </div>

                                                {{-- ══════════════════════════════════════
                                                     BUNDLE ITEMS
                                                     (booking + qty — bundle_id background میں)
                                                ══════════════════════════════════════ --}}
                                                <div class="form-group mt-4">
                                                    <label class="font-weight-bold">
                                                        Bundle Items <span class="text-danger">*</span>
                                                        <small class="text-muted font-weight-normal ml-2">
                                                            — add bookings to this bundle
                                                        </small>
                                                    </label>

                                                    @error('booking_ids')
                                                        <div class="text-danger small mb-2">{{ $message }}</div>
                                                    @enderror

                                                    {{-- Items Table --}}
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered font-14" id="items-table">
                                                            <thead class="thead-light">
                                                                <tr>
                                                                    <th>Booking</th>
                                                                    <th width="130px">Quantity</th>
                                                                    <th width="60px"></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="items-body">

                                                                @php
                                                                    // Edit mode: existing items
                                                                    // Create mode: old() یا empty row
                                                                    $existingItems = [];
                                                                    if (!empty($editBundle)) {
                                                                        foreach ($editBundle->items as $item) {
                                                                            $existingItems[] = [
                                                                                'booking_id' => $item->booking_id,
                                                                                'quantity'   => $item->quantity,
                                                                            ];
                                                                        }
                                                                    } elseif (old('booking_ids')) {
                                                                        foreach (old('booking_ids') as $i => $bid) {
                                                                            $existingItems[] = [
                                                                                'booking_id' => $bid,
                                                                                'quantity'   => old('quantities')[$i] ?? 1,
                                                                            ];
                                                                        }
                                                                    }
                                                                    if (empty($existingItems)) {
                                                                        $existingItems[] = ['booking_id' => '', 'quantity' => 1];
                                                                    }
                                                                @endphp

                                                                @foreach($existingItems as $item)
                                                                    <tr class="item-row">
                                                                        <td>
                                                                            <select name="booking_ids[]"
                                                                                    class="form-control @error('booking_ids.*') is-invalid @enderror">
                                                                                <option value="">— Select Booking —</option>
                                                                                @foreach($bookings as $booking)
                                                                                    <option value="{{ $booking->id }}"
                                                                                        {{ (string)($item['booking_id']) === (string)$booking->id ? 'selected' : '' }}>
                                                                                        #{{ $booking->id }} — {{ $booking->title }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <input type="number" name="quantities[]"
                                                                                   class="form-control" min="1"
                                                                                   value="{{ $item['quantity'] }}"/>
                                                                        </td>
                                                                        <td class="text-center align-middle">
                                                                            <button type="button"
                                                                                    class="btn btn-sm btn-outline-danger remove-item"
                                                                                    title="Remove">
                                                                                <i class="fas fa-times"></i>
                                                                            </button>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach

                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <button type="button" id="add-item"
                                                            class="btn btn-outline-secondary btn-sm mt-1">
                                                        <i class="fas fa-plus mr-1"></i> Add Booking
                                                    </button>
                                                </div>

                                                {{-- Actions --}}
                                                <div class="text-right col-12 mt-3">
                                                    @if(!empty($editBundle))
                                                        <a href="{{ getAdminPanelUrl() }}/booking/bundle"
                                                           class="btn btn-secondary mr-2">
                                                            {{ trans('admin/main.cancel') }}
                                                        </a>
                                                    @endif
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fa fa-save mr-1"></i>
                                                        {{ trans('admin/main.save_change') }}
                                                    </button>
                                                </div>

                                            </form>
                                        </div>
                                    </div>
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

    // ── Slug auto-generate from title ─────────────────────────────
    const titleInput = document.querySelector('input[name="title"]');
    const slugInput  = document.getElementById('bundle-slug');

    if (titleInput && slugInput && !slugInput.value) {
        titleInput.addEventListener('input', function () {
            slugInput.value = this.value
                .toLowerCase()
                .trim()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        });
    }

    // ── Add item row ──────────────────────────────────────────────
    const itemsBody  = document.getElementById('items-body');
    const addItemBtn = document.getElementById('add-item');

    // پہلی row کا HTML template بنائیں
    function buildRow() {
        const firstSelect = itemsBody.querySelector('select[name="booking_ids[]"]');
        const optionsHtml = firstSelect ? firstSelect.innerHTML : '';

        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.innerHTML =
            '<td>' +
                '<select name="booking_ids[]" class="form-control">' +
                    optionsHtml +
                '</select>' +
            '</td>' +
            '<td>' +
                '<input type="number" name="quantities[]" class="form-control" min="1" value="1"/>' +
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
            row.querySelector('select').focus();
        });
    }

    // ── Remove item row (کم از کم 1 رہے) ─────────────────────────
    if (itemsBody) {
        itemsBody.addEventListener('click', function (e) {
            const btn = e.target.closest('.remove-item');
            if (!btn) return;

            const rows = itemsBody.querySelectorAll('.item-row');
            if (rows.length > 1) {
                btn.closest('.item-row').remove();
            } else {
                // آخری row کو صرف reset کریں
                const row = btn.closest('.item-row');
                row.querySelector('select').value = '';
                row.querySelector('input[type="number"]').value = 1;
            }
        });
    }

})();
</script>
@endpush
