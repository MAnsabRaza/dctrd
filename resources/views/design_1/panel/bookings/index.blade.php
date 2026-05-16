@extends('design_1.panel.layouts.panel')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')

    {{-- ── Top Stats ──────────────────────────────────────────────────────────── --}}
    @include('design_1.panel.bookings.top_stats')

    @if(!empty($bookings) && !$bookings->isEmpty())
        <div class="bg-white pt-16 rounded-24 mt-20">

            {{-- Header --}}
            <div class="d-flex align-items-center justify-content-between pb-16 px-16 border-bottom-gray-100">
                <div>
                    <h3 class="font-16">{{ trans('panel.booking_management') }}</h3>
                </div>
                <button id="newBookingButton" type="button" class="btn btn-primary btn-sm">
                    + {{ trans('panel.new_booking') }}
                </button>
            </div>

            {{-- Filters --}}
            @include('design_1.panel.bookings.filters')

            {{-- Table --}}
            <div id="tableListContainer"
                 class="table-responsive-lg"
                 data-view-data-path="/panel/bookings">

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
                <div id="pagination"
                     class="js-ajax-pagination"
                     data-container-id="tableListContainer"
                     data-container-items=".js-table-body-lists">
                    {!! $pagination !!}
                </div>
            </div>
        </div>

    @else
        @include('design_1.panel.includes.no-result', [
            'file_name' => 'bookings_no_result.svg',
            'title'     => trans('panel.bookings_no_result'),
            'hint'      => nl2br(trans('panel.bookings_no_result_hint')),
        ])
    @endif

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

                <form id="bookingForm">
                    @csrf
                    <div class="modal-body">

                        <div class="alert alert-danger d-none" id="bookingFormErrorBox"></div>
                        <input type="hidden" id="bookingId" name="id" value="" />

                        {{-- Title & Slug --}}
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>{{ trans('panel.title') }} <span class="text-danger">*</span></label>
                                <input id="bookingTitle" name="title" type="text" class="form-control" required />
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ trans('panel.slug') }}</label>
                                <input id="bookingSlug" name="slug" type="text" class="form-control" />
                            </div>
                        </div>

                        {{-- Category & Price --}}
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>{{ trans('panel.category') }}</label>
                                <select id="bookingCategory" name="category_id" class="form-control">
                                    <option value="">{{ trans('panel.select_category') }}</option>
                                    @foreach($allCategoryLists as $category)
                                        <option value="{{ $category->id }}">{{ $category->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ trans('panel.price') }}</label>
                                <input id="bookingPrice" name="price" type="number" step="0.01" min="0" class="form-control" />
                            </div>
                        </div>

                        {{-- Discount Price & Capacity --}}
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>{{ trans('panel.discount_price') }}</label>
                                <input id="bookingDiscountPrice" name="discount_price" type="number" step="0.01" min="0" class="form-control" />
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ trans('panel.capacity') }}</label>
                                <input id="bookingCapacity" name="capacity" type="number" min="0" class="form-control" />
                            </div>
                        </div>

                        {{-- Min / Max Persons & Duration --}}
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>{{ trans('panel.min_persons') }}</label>
                                <input id="bookingMinPersons" name="min_persons" type="number" min="0" class="form-control" />
                            </div>
                            <div class="col-md-4 form-group">
                                <label>{{ trans('panel.max_persons') }}</label>
                                <input id="bookingMaxPersons" name="max_persons" type="number" min="0" class="form-control" />
                            </div>
                            <div class="col-md-4 form-group">
                                <label>{{ trans('panel.duration_minutes') }}</label>
                                <input id="bookingDuration" name="duration_minutes" type="number" min="0" class="form-control" />
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="form-group">
                            <label>{{ trans('panel.description') }}</label>
                            <textarea id="bookingDescription" name="description" rows="4" class="form-control"></textarea>
                        </div>

                        {{-- Address --}}
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>{{ trans('panel.address_line') }}</label>
                                <input id="bookingAddressLine" name="address_line" type="text" class="form-control" />
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ trans('panel.city') }}</label>
                                <input id="bookingCity" name="city" type="text" class="form-control" />
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label>{{ trans('panel.state') }}</label>
                                <input id="bookingState" name="state" type="text" class="form-control" />
                            </div>
                            <div class="col-md-4 form-group">
                                <label>{{ trans('panel.country') }}</label>
                                <input id="bookingCountry" name="country" type="text" class="form-control" />
                            </div>
                            <div class="col-md-4 form-group">
                                <label>{{ trans('panel.postal_code') }}</label>
                                <input id="bookingPostalCode" name="postal_code" type="text" class="form-control" />
                            </div>
                        </div>

                        {{-- Lat / Lng --}}
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>{{ trans('panel.latitude') }}</label>
                                <input id="bookingLat" name="lat" type="number" step="0.000001" class="form-control" />
                            </div>
                            <div class="col-md-6 form-group">
                                <label>{{ trans('panel.longitude') }}</label>
                                <input id="bookingLng" name="lng" type="number" step="0.000001" class="form-control" />
                            </div>
                        </div>

                        {{-- Status & Featured --}}
                        <div class="row align-items-center">
                            <div class="col-md-6 form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="bookingStatus" name="status" checked>
                                    <label class="custom-control-label" for="bookingStatus">{{ trans('public.active') }}</label>
                                </div>
                            </div>
                            <div class="col-md-6 form-group">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="bookingFeatured" name="featured">
                                    <label class="custom-control-label" for="bookingFeatured">{{ trans('panel.featured') }}</label>
                                </div>
                            </div>
                        </div>

                        {{-- Meta JSON --}}
                        <div class="form-group">
                            <label>{{ trans('panel.meta_json') }}</label>
                            <textarea id="bookingMeta" name="meta" rows="3" class="form-control" placeholder='{"key": "value"}'></textarea>
                            <small class="text-muted">{{ trans('panel.meta_json_hint') }}</small>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ trans('public.close') }}</button>
                        <button type="submit" class="btn btn-primary" id="bookingFormSubmitButton">{{ trans('public.save') }}</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/moment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="{{ getDesign1ScriptPath('get_view_data') }}"></script>

    <script>
    (function () {
        'use strict';

        const csrfToken  = '{{ csrf_token() }}';
        let isEditing    = false;
        let currentPage  = 1;

        // ── DOM refs ────────────────────────────────────────────────────────
        const bookingModal          = $('#bookingModal');
        const bookingModalTitle     = document.getElementById('bookingModalTitle');
        const bookingForm           = document.getElementById('bookingForm');
        const bookingFormErrorBox   = document.getElementById('bookingFormErrorBox');
        const bookingFormSubmitBtn  = document.getElementById('bookingFormSubmitButton');
        const tableBody             = document.querySelector('.js-table-body-lists');
        const paginationContainer   = document.getElementById('pagination');

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

        // ── Helpers ─────────────────────────────────────────────────────────
        const showToast = (icon, title) => Swal.fire({
            toast: true, position: 'top-end', icon, title,
            showConfirmButton: false, timer: 2500, timerProgressBar: true,
        });

        const showErrors = (errors) => {
            bookingFormErrorBox.classList.remove('d-none');
            let html = '';
            if (typeof errors === 'string') {
                html = `<p>${errors}</p>`;
            } else if (Array.isArray(errors)) {
                html = errors.map(e => `<p>${e}</p>`).join('');
            } else if (typeof errors === 'object') {
                Object.values(errors).forEach(v => {
                    (Array.isArray(v) ? v : [v]).forEach(msg => { html += `<p>${msg}</p>`; });
                });
            }
            bookingFormErrorBox.innerHTML = html;
        };

        const clearErrors = () => {
            bookingFormErrorBox.classList.add('d-none');
            bookingFormErrorBox.innerHTML = '';
        };

        const resetForm = () => {
            isEditing = false;
            bookingModalTitle.textContent   = '{{ trans('panel.new_booking') }}';
            bookingFormSubmitBtn.textContent = '{{ trans('public.save') }}';
            bookingForm.reset();
            fields.status.checked   = true;
            fields.featured.checked = false;
            fields.id.value         = '';
            clearErrors();
        };

        const populateForm = (booking) => {
            isEditing = true;
            bookingModalTitle.textContent   = '{{ trans('panel.edit_booking') }}';
            bookingFormSubmitBtn.textContent = '{{ trans('public.update') }}';
            fields.id.value            = booking.id;
            fields.title.value         = booking.title         || '';
            fields.slug.value          = booking.slug          || '';
            fields.categoryId.value    = booking.category_id   || '';
            fields.price.value         = booking.price         ?? '';
            fields.discountPrice.value = booking.discount_price ?? '';
            fields.capacity.value      = booking.capacity       ?? '';
            fields.minPersons.value    = booking.min_persons    ?? '';
            fields.maxPersons.value    = booking.max_persons    ?? '';
            fields.duration.value      = booking.duration_minutes ?? '';
            fields.description.value   = booking.description   || '';
            fields.addressLine.value   = booking.address_line  || '';
            fields.city.value          = booking.city          || '';
            fields.state.value         = booking.state         || '';
            fields.country.value       = booking.country       || '';
            fields.postalCode.value    = booking.postal_code   || '';
            fields.lat.value           = booking.lat           ?? '';
            fields.lng.value           = booking.lng           ?? '';
            fields.status.checked      = booking.status === 'published' || booking.status === 'active';
            fields.featured.checked    = Boolean(booking.featured);
            fields.meta.value          = booking.meta ? JSON.stringify(booking.meta, null, 2) : '';
            clearErrors();
        };

        // ── AJAX fetch (re-uses project's get_view_data pattern) ─────────────
        const fetchBookings = async (page = 1) => {
            const params = new URLSearchParams(
                document.getElementById('filtersForm')
                    ? new FormData(document.getElementById('filtersForm'))
                    : {}
            );
            params.set('page', page);

            try {
                const res = await fetch(`/panel/bookings?${params}`, {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });

                if (!res.ok) throw new Error('{{ trans('panel.load_failed') }}');

                const payload = await res.json();
                tableBody.innerHTML        = payload.data;
                paginationContainer.innerHTML = payload.pagination;
                currentPage = page;
                bindTableActions();
                bindPaginationLinks();
            } catch (err) {
                showToast('error', err.message || '{{ trans('panel.load_failed') }}');
            }
        };

        const bindPaginationLinks = () => {
            paginationContainer.querySelectorAll('a[data-page]').forEach(link => {
                link.addEventListener('click', e => {
                    e.preventDefault();
                    fetchBookings(parseInt(link.dataset.page, 10));
                });
            });
        };

        // ── Submit (Create / Update) ─────────────────────────────────────────
        const submitBooking = async (e) => {
            e.preventDefault();
            clearErrors();
            bookingFormSubmitBtn.disabled = true;

            const bookingId = fields.id.value;
            const url    = bookingId ? `/panel/bookings/${bookingId}` : '/panel/bookings';
            const method = bookingId ? 'PUT' : 'POST';

            const body = {
                title:            fields.title.value.trim(),
                slug:             fields.slug.value.trim(),
                category_id:      fields.categoryId.value    || null,
                description:      fields.description.value.trim(),
                price:            fields.price.value         || null,
                discount_price:   fields.discountPrice.value || null,
                capacity:         fields.capacity.value      || null,
                min_persons:      fields.minPersons.value    || null,
                max_persons:      fields.maxPersons.value    || null,
                duration_minutes: fields.duration.value      || null,
                address_line:     fields.addressLine.value.trim(),
                city:             fields.city.value.trim(),
                state:            fields.state.value.trim(),
                country:          fields.country.value.trim(),
                postal_code:      fields.postalCode.value.trim(),
                lat:              fields.lat.value  || null,
                lng:              fields.lng.value  || null,
                status:           fields.status.checked   ? 1 : 0,
                featured:         fields.featured.checked ? 1 : 0,
                meta:             fields.meta.value.trim() || null,
            };

            try {
                const res     = await fetch(url, {
                    method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        Accept: 'application/json',
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
                bookingModal.modal('hide');
                resetForm();
                fetchBookings(currentPage);

            } catch (err) {
                showErrors(err.message || '{{ trans('panel.save_failed') }}');
            } finally {
                bookingFormSubmitBtn.disabled = false;
            }
        };

        // ── Delete ───────────────────────────────────────────────────────────
        const deleteBooking = async (id, title) => {
            const result = await Swal.fire({
                title: `{{ trans('panel.delete') }} ${title}?`,
                text:  '{{ trans('panel.delete_confirm') }}',
                icon:  'warning',
                showCancelButton:  true,
                confirmButtonText: '{{ trans('panel.delete') }}',
                cancelButtonText:  '{{ trans('public.close') }}',
            });

            if (!result.isConfirmed) return;

            try {
                const res = await fetch(`/panel/bookings/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                    credentials: 'same-origin',
                });

                const payload = await res.json();
                if (!res.ok) throw new Error(payload.message || '{{ trans('panel.delete_failed') }}');

                showToast('success', payload.message);
                fetchBookings(currentPage);
            } catch (err) {
                showToast('error', err.message);
            }
        };

        // ── Bind table action buttons ────────────────────────────────────────
        const bindTableActions = () => {
            document.querySelectorAll('.btn-edit-booking').forEach(btn => {
                btn.addEventListener('click', () => {
                    const row     = btn.closest('tr');
                    const booking = JSON.parse(decodeURIComponent(row.dataset.booking));
                    populateForm(booking);
                    bookingModal.modal('show');
                });
            });

            document.querySelectorAll('.btn-delete-booking').forEach(btn => {
                btn.addEventListener('click', () => {
                    deleteBooking(btn.dataset.id, btn.dataset.title);
                });
            });
        };

        // ── Auto-slug on title input ─────────────────────────────────────────
        fields.title.addEventListener('input', () => {
            if (!isEditing || fields.slug.value.trim() === '') {
                fields.slug.value = fields.title.value.trim()
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/(^-|-$)/g, '');
            }
        });

        // ── Event listeners ──────────────────────────────────────────────────
        document.getElementById('newBookingButton').addEventListener('click', () => {
            resetForm();
            bookingModal.modal('show');
        });

        bookingForm.addEventListener('submit', submitBooking);

        // Filters form submit (if filters blade includes a form with id="filtersForm")
        const filtersForm = document.getElementById('filtersForm');
        if (filtersForm) {
            filtersForm.addEventListener('submit', e => { e.preventDefault(); fetchBookings(1); });
        }

        // Initial bind for server-rendered rows
        bindTableActions();
        bindPaginationLinks();

    })();
    </script>
@endpush