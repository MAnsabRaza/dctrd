@extends('design_1.panel.layouts.panel')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')

    {{-- ── Top Stats ──────────────────────────────────────────────────────────── --}}
    @include('design_1.panel.bookings.top_stats')

    <div class="bg-white pt-16 rounded-24 mt-20">

        {{-- Header --}}
        <div class="d-flex align-items-center justify-content-between pb-16 px-16 border-bottom-gray-100">
            <div>
                <h3 class="font-16">{{ trans('panel.booking_management') }}</h3>
            </div>
            <div class="d-flex align-items-center gap-8">
                @can('panel_bookings_calendar')
                    <a href="{{ route('panel.bookings.calendar') }}" class="btn btn-outline-primary btn-sm">
                        Calendar
                    </a>
                @endcan

                @can('panel_bookings_create')
                    <a id="newBookingButton" href="{{ route('panel.bookings.create') }}" class="btn btn-primary btn-sm">
                        + {{ trans('panel.new_booking') }}
                    </a>
                @endcan
            </div>
        </div>

        {{-- Filters --}}
        @include('design_1.panel.bookings.filters')

        {{-- Table --}}
        @if(!empty($bookings) && !$bookings->isEmpty())
            <div id="tableListContainer" class="table-responsive-lg">
                <table class="table panel-table">
                    <thead>
                        <tr>
                            <th class="text-left">{{ trans('panel.title') }}</th>
                            <th class="text-left">{{ trans('panel.category') }}</th>
                            <th class="text-center">{{ trans('panel.price') }}</th>
                            <th class="text-center">{{ trans('panel.capacity') }}</th>
                            <th class="text-center">{{ trans('public.status') }}</th>
                            <th class="text-center">{{ trans('public.date') }}</th>
                            <th class="text-right">{{ trans('update.controls') }}</th>
                        </tr>
                    </thead>
                    <tbody class="js-table-body-lists">
                        @foreach($bookings as $booking)
                            @include('design_1.panel.bookings.table_item', ['booking' => $booking])
                        @endforeach
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div id="pagination" class="js-ajax-pagination">
                    {!! $pagination !!}
                </div>
            </div>
        @else
            <div id="tableListContainer">
                <div class="js-table-body-lists">
                    {{-- Empty state --}}
                </div>
                <div id="pagination"></div>
                <div class="text-center py-40">
                    <p class="text-muted">{{ trans('panel.bookings_no_result') }}</p>
                    @can('panel_bookings_create')
                        <a id="newBookingButtonEmpty" href="{{ route('panel.bookings.create') }}" class="btn btn-primary btn-sm mt-10">
                            + {{ trans('panel.new_booking') }}
                        </a>
                    @endcan
                </div>
            </div>
        @endif
    </div>

    {{-- ── Create / Edit Modal ────────────────────────────────────────────────── --}}
    <div class="modal fade" id="bookingModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-xl" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="bookingModalTitle">{{ trans('panel.new_booking') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="alert alert-danger d-none" id="bookingFormErrorBox"></div>
                    <input type="hidden" id="bookingId" value="" />

                    {{-- Title & Slug --}}
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>{{ trans('panel.title') }} <span class="text-danger">*</span></label>
                            <input id="bookingTitle" type="text" class="form-control" />
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('panel.slug') }}</label>
                            <input id="bookingSlug" type="text" class="form-control" />
                        </div>
                    </div>

                    {{-- Category & Price --}}
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>{{ trans('panel.category') }}</label>
                            <select id="bookingCategory" class="form-control">
                                <option value="">{{ trans('panel.select_category') }}</option>
                                @foreach($allCategoryLists as $category)
                                    <option value="{{ $category->id }}">{{ $category->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('panel.price') }}</label>
                            <input id="bookingPrice" type="number" step="0.01" min="0" class="form-control" />
                        </div>
                    </div>

                    {{-- Discount Price & Capacity --}}
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>{{ trans('panel.discount_price') }}</label>
                            <input id="bookingDiscountPrice" type="number" step="0.01" min="0" class="form-control" />
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('panel.capacity') }}</label>
                            <input id="bookingCapacity" type="number" min="0" class="form-control" />
                        </div>
                    </div>

                    {{-- Min / Max Persons & Duration --}}
                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>{{ trans('panel.min_persons') }}</label>
                            <input id="bookingMinPersons" type="number" min="0" class="form-control" />
                        </div>
                        <div class="col-md-4 form-group">
                            <label>{{ trans('panel.max_persons') }}</label>
                            <input id="bookingMaxPersons" type="number" min="0" class="form-control" />
                        </div>
                        <div class="col-md-4 form-group">
                            <label>{{ trans('panel.duration_minutes') }}</label>
                            <input id="bookingDuration" type="number" min="0" class="form-control" />
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="form-group">
                        <label>{{ trans('panel.description') }}</label>
                        <textarea id="bookingDescription" rows="4" class="form-control"></textarea>
                    </div>

                    {{-- Address --}}
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>{{ trans('panel.address_line') }}</label>
                            <input id="bookingAddressLine" type="text" class="form-control" />
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('panel.city') }}</label>
                            <input id="bookingCity" type="text" class="form-control" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 form-group">
                            <label>{{ trans('panel.state') }}</label>
                            <input id="bookingState" type="text" class="form-control" />
                        </div>
                        <div class="col-md-4 form-group">
                            <label>{{ trans('panel.country') }}</label>
                            <input id="bookingCountry" type="text" class="form-control" />
                        </div>
                        <div class="col-md-4 form-group">
                            <label>{{ trans('panel.postal_code') }}</label>
                            <input id="bookingPostalCode" type="text" class="form-control" />
                        </div>
                    </div>

                    {{-- Lat / Lng --}}
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>{{ trans('panel.latitude') }}</label>
                            <input id="bookingLat" type="number" step="0.000001" class="form-control" />
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('panel.longitude') }}</label>
                            <input id="bookingLng" type="number" step="0.000001" class="form-control" />
                        </div>
                    </div>

                    {{-- Status & Featured --}}
                    <div class="row align-items-center">
                        <div class="col-md-6 form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="bookingStatus" checked>
                                <label class="custom-control-label" for="bookingStatus">{{ trans('public.active') }}</label>
                            </div>
                        </div>
                        <div class="col-md-6 form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="bookingFeatured">
                                <label class="custom-control-label" for="bookingFeatured">{{ trans('panel.featured') }}</label>
                            </div>
                        </div>
                    </div>

                    {{-- Meta JSON --}}
                    <div class="form-group">
                        <label>{{ trans('panel.meta_json') }}</label>
                        <textarea id="bookingMeta" rows="3" class="form-control" placeholder='{"key": "value"}'></textarea>
                        <small class="text-muted">{{ trans('panel.meta_json_hint') }}</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('public.close') }}</button>
                    <button type="button" class="btn btn-primary" id="bookingFormSubmitButton">{{ trans('public.save') }}</button>
                </div>

            </div>
        </div>
    </div>

@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/moment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>

    <script>
    (function () {
        'use strict';

        const csrfToken = '{{ csrf_token() }}';
        let isEditing   = false;
        let currentPage = 1;

        // ── DOM refs ─────────────────────────────────────────────────────────
        const $bookingModal        = $('#bookingModal');
        const bookingModalTitle    = document.getElementById('bookingModalTitle');
        const bookingFormErrorBox  = document.getElementById('bookingFormErrorBox');
        const bookingFormSubmitBtn = document.getElementById('bookingFormSubmitButton');
        const tableContainer       = document.getElementById('tableListContainer');
        const paginationContainer  = document.getElementById('pagination');

        // All field references
        const fields = {
            id:            document.getElementById('bookingId'),
            title:         document.getElementById('bookingTitle'),
            slug:          document.getElementById('bookingSlug'),
            categoryId:    document.getElementById('bookingCategory'),
            price:         document.getElementById('bookingPrice'),
            discountPrice: document.getElementById('bookingDiscountPrice'),
            capacity:      document.getElementById('bookingCapacity'),
            minPersons:    document.getElementById('bookingMinPersons'),
            maxPersons:    document.getElementById('bookingMaxPersons'),
            duration:      document.getElementById('bookingDuration'),
            description:   document.getElementById('bookingDescription'),
            addressLine:   document.getElementById('bookingAddressLine'),
            city:          document.getElementById('bookingCity'),
            state:         document.getElementById('bookingState'),
            country:       document.getElementById('bookingCountry'),
            postalCode:    document.getElementById('bookingPostalCode'),
            lat:           document.getElementById('bookingLat'),
            lng:           document.getElementById('bookingLng'),
            status:        document.getElementById('bookingStatus'),
            featured:      document.getElementById('bookingFeatured'),
            meta:          document.getElementById('bookingMeta'),
        };

        // ── Helpers ──────────────────────────────────────────────────────────
        const showToast = (icon, title) => {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true, position: 'top-end', icon, title,
                    showConfirmButton: false, timer: 2500, timerProgressBar: true,
                });
            } else {
                alert(title);
            }
        };

        const showErrors = (errors) => {
            bookingFormErrorBox.classList.remove('d-none');
            let html = '';
            if (typeof errors === 'string') {
                html = `<p>${errors}</p>`;
            } else if (Array.isArray(errors)) {
                errors.forEach(e => { html += `<p>${e}</p>`; });
            } else if (typeof errors === 'object' && errors !== null) {
                Object.values(errors).forEach(v => {
                    (Array.isArray(v) ? v : [v]).forEach(msg => { html += `<p>${msg}</p>`; });
                });
            }
            bookingFormErrorBox.innerHTML = html;
            // Scroll to top of modal body
            const modalBody = document.querySelector('#bookingModal .modal-body');
            if (modalBody) modalBody.scrollTop = 0;
        };

        const clearErrors = () => {
            bookingFormErrorBox.classList.add('d-none');
            bookingFormErrorBox.innerHTML = '';
        };

        const resetForm = () => {
            isEditing = false;
            bookingModalTitle.textContent    = '{{ trans('panel.new_booking') }}';
            bookingFormSubmitBtn.textContent = '{{ trans('public.save') }}';
            // Clear all fields manually
            fields.id.value            = '';
            fields.title.value         = '';
            fields.slug.value          = '';
            fields.categoryId.value    = '';
            fields.price.value         = '';
            fields.discountPrice.value = '';
            fields.capacity.value      = '';
            fields.minPersons.value    = '';
            fields.maxPersons.value    = '';
            fields.duration.value      = '';
            fields.description.value   = '';
            fields.addressLine.value   = '';
            fields.city.value          = '';
            fields.state.value         = '';
            fields.country.value       = '';
            fields.postalCode.value    = '';
            fields.lat.value           = '';
            fields.lng.value           = '';
            fields.status.checked      = true;
            fields.featured.checked    = false;
            fields.meta.value          = '';
            clearErrors();
        };

        const populateForm = (booking) => {
            isEditing = true;
            bookingModalTitle.textContent    = '{{ trans('panel.edit_booking') }}';
            bookingFormSubmitBtn.textContent = '{{ trans('public.update') }}';

            fields.id.value            = booking.id            || '';
            fields.title.value         = booking.title         || '';
            fields.slug.value          = booking.slug          || '';
            fields.price.value         = booking.price         != null ? booking.price : '';
            fields.discountPrice.value = booking.discount_price != null ? booking.discount_price : '';
            fields.capacity.value      = booking.capacity       != null ? booking.capacity : '';
            fields.minPersons.value    = booking.min_persons    != null ? booking.min_persons : '';
            fields.maxPersons.value    = booking.max_persons    != null ? booking.max_persons : '';
            fields.duration.value      = booking.duration_minutes != null ? booking.duration_minutes : '';
            fields.description.value   = booking.description   || '';
            fields.addressLine.value   = booking.address_line  || '';
            fields.city.value          = booking.city          || '';
            fields.state.value         = booking.state         || '';
            fields.country.value       = booking.country       || '';
            fields.postalCode.value    = booking.postal_code   || '';
            fields.lat.value           = booking.lat           != null ? booking.lat : '';
            fields.lng.value           = booking.lng           != null ? booking.lng : '';
            fields.status.checked      = (booking.status === 'published' || booking.status === 'active');
            fields.featured.checked    = Boolean(booking.featured);
            fields.meta.value          = booking.meta ? (typeof booking.meta === 'string' ? booking.meta : JSON.stringify(booking.meta, null, 2)) : '';

            // Set category dropdown - must happen after value assignment
            if (fields.categoryId) {
                fields.categoryId.value = booking.category_id || '';
            }

            clearErrors();
        };

        // ── AJAX: fetch bookings list ────────────────────────────────────────
        const fetchBookings = async (page = 1) => {
            const filtersForm = document.getElementById('filtersForm');
            const params = new URLSearchParams();

            if (filtersForm) {
                const formData = new FormData(filtersForm);
                formData.forEach((val, key) => {
                    if (val !== '') params.set(key, val);
                });
            }
            params.set('page', page);

            try {
                const res = await fetch(`/panel/bookings?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!res.ok) throw new Error('{{ trans('panel.load_failed') }}');

                const payload = await res.json();

                // Update table body
                const tableBody = tableContainer.querySelector('.js-table-body-lists');
                if (tableBody) {
                    tableBody.innerHTML = payload.data;
                }

                // Update pagination
                if (paginationContainer) {
                    paginationContainer.innerHTML = payload.pagination || '';
                }

                currentPage = page;
                bindTableActions();
                bindPaginationLinks();

            } catch (err) {
                showToast('error', err.message || '{{ trans('panel.load_failed') }}');
            }
        };

        const bindPaginationLinks = () => {
            if (!paginationContainer) return;
            paginationContainer.querySelectorAll('a[data-page]').forEach(link => {
                link.addEventListener('click', e => {
                    e.preventDefault();
                    fetchBookings(parseInt(link.dataset.page, 10));
                });
            });
        };

        // ── Submit (Create / Update) ─────────────────────────────────────────
        const submitBooking = async () => {
            clearErrors();

            const titleVal = fields.title.value.trim();
            if (!titleVal) {
                showErrors('{{ trans('panel.title') }} {{ trans('validation.required') }}');
                return;
            }

            bookingFormSubmitBtn.disabled = true;
            bookingFormSubmitBtn.textContent = '{{ trans('panel.saving') }}...';

            const bookingId = fields.id.value.trim();
            // Use the correct routes defined in web.php
            const url = bookingId
                ? `/panel/bookings/${encodeURIComponent(bookingId)}/update`
                : '/panel/bookings';

            const body = {
                _token:           csrfToken,
                title:            titleVal,
                slug:             fields.slug.value.trim()          || null,
                category_id:      fields.categoryId.value           || null,
                description:      fields.description.value.trim()   || null,
                price:            fields.price.value                || null,
                discount_price:   fields.discountPrice.value        || null,
                capacity:         fields.capacity.value             || null,
                min_persons:      fields.minPersons.value           || null,
                max_persons:      fields.maxPersons.value           || null,
                duration_minutes: fields.duration.value             || null,
                address_line:     fields.addressLine.value.trim()   || null,
                city:             fields.city.value.trim()          || null,
                state:            fields.state.value.trim()         || null,
                country:          fields.country.value.trim()       || null,
                postal_code:      fields.postalCode.value.trim()    || null,
                lat:              fields.lat.value                  || null,
                lng:              fields.lng.value                  || null,
                status:           fields.status.checked   ? 1 : 0,
                featured:         fields.featured.checked ? 1 : 0,
                meta:             fields.meta.value.trim()          || null,
            };

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN':  csrfToken,
                        'Accept':        'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify(body),
                    credentials: 'same-origin',
                });

                const payload = await res.json();

                if (!res.ok) {
                    showErrors(payload.errors || payload.message || '{{ trans('panel.validation_failed') }}');
                    return;
                }

                showToast('success', payload.message);
                $bookingModal.modal('hide');
                resetForm();
                fetchBookings(currentPage);

            } catch (err) {
                showErrors(err.message || '{{ trans('panel.save_failed') }}');
            } finally {
                bookingFormSubmitBtn.disabled = false;
                bookingFormSubmitBtn.textContent = isEditing
                    ? '{{ trans('public.update') }}'
                    : '{{ trans('public.save') }}';
            }
        };

        // ── Delete ───────────────────────────────────────────────────────────
        const deleteBooking = async (id, title) => {
            if (typeof Swal === 'undefined') {
                if (!confirm(`Delete ${title}?`)) return;
            } else {
                const result = await Swal.fire({
                    title:             `{{ trans('panel.delete') }}: ${title}`,
                    text:              '{{ trans('panel.delete_confirm') }}',
                    icon:              'warning',
                    showCancelButton:  true,
                    confirmButtonText: '{{ trans('panel.delete') }}',
                    cancelButtonText:  '{{ trans('public.close') }}',
                    confirmButtonColor: '#dc3545',
                });
                if (!result.isConfirmed) return;
            }

            try {
                const res = await fetch(`/panel/bookings/${encodeURIComponent(id)}/delete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type':     'application/json',
                        'X-CSRF-TOKEN':     csrfToken,
                        'Accept':           'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ _token: csrfToken }),
                    credentials: 'same-origin',
                });

                const payload = await res.json();
                if (!res.ok) throw new Error(payload.message || '{{ trans('panel.delete_failed') }}');

                showToast('success', payload.message);
                fetchBookings(currentPage);

            } catch (err) {
                showToast('error', err.message || '{{ trans('panel.delete_failed') }}');
            }
        };

        // ── Bind table Delete buttons ────────────────────────────────────
        const bindTableActions = () => {
            document.querySelectorAll('.btn-delete-booking').forEach(btn => {
                const newBtn = btn.cloneNode(true);
                btn.parentNode.replaceChild(newBtn, btn);

                newBtn.addEventListener('click', function () {
                    const bookingId    = this.dataset.id;
                    const bookingTitle = this.dataset.title || '';
                    if (!bookingId) {
                        showToast('error', '{{ trans('panel.invalid_booking_id') }}');
                        return;
                    }
                    deleteBooking(bookingId, bookingTitle);
                });
            });
        };

        // ── Auto-slug ────────────────────────────────────────────────────────
        fields.title.addEventListener('input', () => {
            // Only auto-generate slug when creating new booking
            if (!isEditing) {
                fields.slug.value = fields.title.value.trim()
                    .toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .replace(/(^-|-$)/g, '');
            }
        });

        // ── Modal close: reset form ──────────────────────────────────────────
        $bookingModal.on('hidden.bs.modal', function () {
            resetForm();
        });

        const newBtnEmpty = document.getElementById('newBookingButtonEmpty');
        if (newBtnEmpty) {
            newBtnEmpty.addEventListener('click', () => {
                window.location.href = '{{ route('panel.bookings.create') }}';
            });
        }

        // ── Submit button click ──────────────────────────────────────────────
        bookingFormSubmitBtn.addEventListener('click', submitBooking);

        // ── Filters form ─────────────────────────────────────────────────────
        const filtersForm = document.getElementById('filtersForm');
        if (filtersForm) {
            filtersForm.addEventListener('submit', e => {
                e.preventDefault();
                fetchBookings(1);
            });
        }

        // ── Initial bind ─────────────────────────────────────────────────────
        bindTableActions();
        bindPaginationLinks();

    })();
    </script>
@endpush
