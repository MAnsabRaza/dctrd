@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Booking Variants</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
            </div>
            <div class="breadcrumb-item">Booking Variants</div>
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
                                !empty($editVariant) ||
                                ((empty($variants) || !$variants->count()) &&
                                  auth()->user()->can('admin_booking_variants_create'))
                            );
                        @endphp

                        <ul class="nav nav-pills" id="variantTab" role="tablist">
                            @can('admin_booking_variants')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? '' : 'active' }}"
                                       id="list-tab" data-toggle="tab" href="#listTab" role="tab">
                                        Booking Variants
                                    </a>
                                </li>
                            @endcan

                            @can('admin_booking_variants_create')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? 'active' : '' }}"
                                       id="create-tab" data-toggle="tab" href="#createTab" role="tab">
                                        {{ !empty($editVariant) ? 'Edit Variant' : 'Create Variant' }}
                                    </a>
                                </li>
                            @endcan
                        </ul>

                        <div class="tab-content mt-3">

                            {{-- LIST TAB --}}
                            @can('admin_booking_variants')
                                <div class="tab-pane fade {{ $createActive ? '' : 'active show' }}"
                                     id="listTab" role="tabpanel">

                                    @if(!empty($variants) && $variants->count())
                                        <div class="table-responsive">
                                            <table class="table custom-table font-14">
                                                <thead>
                                                    <tr>
                                                        <th>{{ trans('admin/main.booking') }}</th>
                                                        <th>Name</th>
                                                        <th class="text-center">Options</th>
                                                        <th class="text-center">Price Modifier</th>
                                                        <th class="text-center">Affects Availability</th>
                                                        <th class="text-center">Sort Order</th>
                                                        <th class="text-center">Status</th>
                                                        <th>{{ trans('admin/main.action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($variants as $variant)
                                                        <tr>
                                                            <td>
                                                                @if($variant->booking)
                                                                    #{{ $variant->booking->id }} - {{ $variant->booking->title }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>{{ $variant->name }}</td>
                                                            <td class="text-center">
                                                                @foreach($variant->options as $option)
                                                                    <span class="badge badge-info mr-1">{{ $option }}</span>
                                                                @endforeach
                                                            </td>
                                                            <td class="text-center">
                                                                @if($variant->price_modifier > 0)
                                                                    <span class="text-success">+{{ $variant->price_modifier }}</span>
                                                                @elseif($variant->price_modifier < 0)
                                                                    <span class="text-danger">{{ $variant->price_modifier }}</span>
                                                                @else
                                                                    <span class="text-muted">0</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                @if($variant->affects_availability)
                                                                    <span class="badge badge-warning">Yes</span>
                                                                @else
                                                                    <span class="badge badge-secondary">No</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">{{ $variant->sort_order }}</td>
                                                            <td class="text-center">
                                                                @if($variant->status)
                                                                    <span class="badge badge-success">{{ trans('admin/main.active') }}</span>
                                                                @else
                                                                    <span class="badge badge-danger">{{ trans('admin/main.inactive') }}</span>
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
                                                                        @can('admin_booking_variants_edit')
                                                                            <a href="{{ getAdminPanelUrl() }}/booking/variant/{{ $variant->id }}/edit"
                                                                               class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                                <x-iconsax-lin-edit-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                                                <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                                                                            </a>
                                                                        @endcan

                                                                        @can('admin_booking_variants_delete')
                                                                            @include('admin.includes.delete_button', [
                                                                                'url'       => getAdminPanelUrl() . '/booking/variant/' . $variant->id . '/delete',
                                                                                'btnClass'  => 'dropdown-item text-danger mb-0 py-3 px-0 font-14',
                                                                                'btnText'   => trans('admin/main.delete'),
                                                                                'btnIcon'   => 'trash',
                                                                                'iconType'  => 'lin',
                                                                                'iconClass' => 'text-danger mr-2'
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

                                        {{ $variants->links() }}

                                    @else
                                        <div class="text-center text-gray-500 mt-30">
                                            {{ trans('admin/main.no_result') }}
                                        </div>
                                    @endif
                                </div>
                            @endcan

                            {{-- CREATE / EDIT TAB --}}
                            @can('admin_booking_variants_create')
                                <div class="tab-pane fade {{ $createActive ? 'active show' : '' }}"
                                     id="createTab" role="tabpanel">
                                    <div class="row">
                                        <div class="col-12 col-md-6">

                                            <form action="{{ getAdminPanelUrl() }}/booking/variant/{{ !empty($editVariant) ? $editVariant->id . '/update' : 'store' }}"
                                                  method="post">
                                                {{ csrf_field() }}

                                                {{-- Booking --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.booking') }} <span class="text-danger">*</span></label>
                                                    @php $selBooking = !empty($editVariant) ? $editVariant->booking_id : old('booking_id'); @endphp
                                                    <select name="booking_id" class="form-control @error('booking_id') is-invalid @enderror">
                                                        <option value="">{{ trans('admin/main.select') }}</option>
                                                        @foreach($bookings as $booking)
                                                            <option value="{{ $booking->id }}" {{ (string)$selBooking === (string)$booking->id ? 'selected' : '' }}>
                                                                #{{ $booking->id }} - {{ $booking->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('booking_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Name --}}
                                                <div class="form-group">
                                                    <label>Variant Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="name"
                                                           class="form-control @error('name') is-invalid @enderror"
                                                           value="{{ !empty($editVariant) ? $editVariant->name : old('name') }}"
                                                           placeholder="e.g. Room Type, Package, Add-on"/>
                                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Options (dynamic) --}}
                                                <div class="form-group">
                                                    <label>Options <span class="text-danger">*</span>
                                                        <small class="text-muted ml-1">— one per line or use the + button</small>
                                                    </label>
                                                    <div id="options-wrapper">
                                                        @php
                                                            $existingOptions = !empty($editVariant)
                                                                ? $editVariant->options
                                                                : (old('options') ?? ['']);
                                                        @endphp
                                                        @foreach($existingOptions as $i => $opt)
                                                            <div class="input-group mb-2 option-row">
                                                                <input type="text" name="options[]"
                                                                       class="form-control @error('options.'.$i) is-invalid @enderror"
                                                                       value="{{ $opt }}"
                                                                       placeholder="Option value"/>
                                                                <div class="input-group-append">
                                                                    <button type="button" class="btn btn-outline-danger remove-option">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    @error('options')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                                    <button type="button" id="add-option" class="btn btn-outline-secondary btn-sm mt-1">
                                                        <i class="fas fa-plus mr-1"></i> Add Option
                                                    </button>
                                                </div>

                                                {{-- Price Modifier --}}
                                                <div class="form-group">
                                                    <label>Price Modifier
                                                        <small class="text-muted">(positive = surcharge, negative = discount, 0 = no change)</small>
                                                    </label>
                                                    <input type="number" name="price_modifier" step="0.01"
                                                           class="form-control @error('price_modifier') is-invalid @enderror"
                                                           value="{{ !empty($editVariant) ? $editVariant->price_modifier : old('price_modifier', 0) }}"/>
                                                    @error('price_modifier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Affects Availability --}}
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="affects_availability"
                                                               class="custom-control-input" id="affects_availability"
                                                               {{ (!empty($editVariant) && $editVariant->affects_availability) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="affects_availability">
                                                            Affects Availability
                                                        </label>
                                                    </div>
                                                    <small class="text-muted">If enabled, selecting this variant reduces available slots.</small>
                                                </div>

                                                {{-- Sort Order --}}
                                                <div class="form-group">
                                                    <label>Sort Order</label>
                                                    <input type="number" name="sort_order" min="0"
                                                           class="form-control @error('sort_order') is-invalid @enderror"
                                                           value="{{ !empty($editVariant) ? $editVariant->sort_order : old('sort_order', 0) }}"/>
                                                    @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Status --}}
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="status"
                                                               class="custom-control-input" id="status"
                                                               {{ (empty($editVariant) || (!empty($editVariant) && $editVariant->status)) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="status">
                                                            Active
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="text-right col-12 mt-3">
                                                    @if(!empty($editVariant))
                                                        <a href="{{ getAdminPanelUrl() }}/booking/variant"
                                                           class="btn btn-secondary mr-2">
                                                            {{ trans('admin/main.cancel') }}
                                                        </a>
                                                    @endif
                                                    <button type="submit" class="btn btn-primary">
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

@push('scripts')
<script>
    // Add option row
    document.getElementById('add-option').addEventListener('click', function () {
        const wrapper = document.getElementById('options-wrapper');
        const row = document.createElement('div');
        row.className = 'input-group mb-2 option-row';
        row.innerHTML = `
            <input type="text" name="options[]" class="form-control" placeholder="Option value"/>
            <div class="input-group-append">
                <button type="button" class="btn btn-outline-danger remove-option">
                    <i class="fas fa-times"></i>
                </button>
            </div>`;
        wrapper.appendChild(row);
        row.querySelector('input').focus();
    });

    // Remove option row (keep at least one)
    document.getElementById('options-wrapper').addEventListener('click', function (e) {
        if (e.target.closest('.remove-option')) {
            const rows = document.querySelectorAll('.option-row');
            if (rows.length > 1) {
                e.target.closest('.option-row').remove();
            }
        }
    });
</script>
@endpush
@endsection