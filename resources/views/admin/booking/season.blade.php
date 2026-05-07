@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.admin_booking_season') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">{{ trans('admin/main.admin_booking_season') }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            @php
                                $seasonCreateActive = (
                                    (!empty($errors) && $errors->any()) ||
                                    !empty($editSeason) ||
                                    ((empty($seasons) || !$seasons->count()) && auth()->user()->can('admin_booking_season_create'))
                                );
                            @endphp

                            <ul class="nav nav-pills" id="seasonTab" role="tablist">
                                @can('admin_booking_season')
                                    <li class="nav-item">
                                        <a class="nav-link {{ $seasonCreateActive ? '' : 'active' }}"
                                           id="seasons-tab" data-toggle="tab" href="#seasons"
                                           role="tab">
                                            {{ trans('admin/main.admin_booking_season') }}
                                        </a>
                                    </li>
                                @endcan

                                @can('admin_booking_season_create')
                                    <li class="nav-item">
                                        <a class="nav-link {{ $seasonCreateActive ? 'active' : '' }}"
                                           id="newSeason-tab" data-toggle="tab" href="#newSeason"
                                           role="tab">
                                            {{ trans('admin/main.create_booking_season') }}
                                        </a>
                                    </li>
                                @endcan
                            </ul>

                            <div class="tab-content mt-3">

                                {{-- LIST TAB --}}
                                @can('admin_booking_season')
                                    <div class="tab-pane fade {{ $seasonCreateActive ? '' : 'active show' }}"
                                         id="seasons" role="tabpanel">

                                        @if(!empty($seasons) && $seasons->count())
                                            <div class="table-responsive">
                                                <table class="table custom-table font-14">
                                                    <thead>
                                                        <tr>
                                                            <th>{{ trans('admin/main.booking') }}</th>
                                                            <th>{{ trans('admin/main.name') }}</th>
                                                            <th class="text-center">{{ trans('admin/main.start_date') }}</th>
                                                            <th class="text-center">{{ trans('admin/main.end_date') }}</th>
                                                            <th class="text-center">{{ trans('admin/main.price_modifier') }}</th>
                                                            <th class="text-center">{{ trans('admin/main.modifier_type') }}</th>
                                                            <th class="text-center">{{ trans('admin/main.status') }}</th>
                                                            <th>{{ trans('admin/main.action') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($seasons as $season)
                                                            <tr>
                                                                <td>
                                                                    @if($season->booking)
                                                                        #{{ $season->booking->id }} - {{ $season->booking->title }}
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                                <td>{{ $season->name }}</td>
                                                                <td class="text-center">{{ $season->start_date->format('Y-m-d') }}</td>
                                                                <td class="text-center">{{ $season->end_date->format('Y-m-d') }}</td>
                                                                <td class="text-center">{{ $season->price_modifier }}</td>
                                                                <td class="text-center">{{ ucfirst($season->modifier_type) }}</td>
                                                                <td class="text-center">
                                                                    @if($season->status)
                                                                        <span class="badge badge-success">{{ trans('admin/main.active') }}</span>
                                                                    @else
                                                                        <span class="badge badge-danger">{{ trans('admin/main.inactive') }}</span>
                                                                    @endif
                                                                </td>
                                                                <td width="80px">
                                                                    <div class="btn-group dropdown table-actions position-relative">
                                                                        <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown">
                                                                            <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
                                                                        </button>
                                                                        <div class="dropdown-menu dropdown-menu-right">
                                                                            @can('admin_booking_season_edit')
                                                                                <a href="{{ getAdminPanelUrl() }}/booking/season/{{ $season->id }}/edit"
                                                                                   class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                                    <x-iconsax-lin-edit-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                                                    <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                                                                                </a>
                                                                            @endcan

                                                                            @can('admin_booking_season_delete')
                                                                                @include('admin.includes.delete_button', [
                                                                                    'url'       => getAdminPanelUrl() . '/booking/season/' . $season->id . '/delete',
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

                                            {{ $seasons->links() }}

                                        @else
                                            <div class="text-center text-gray-500 mt-30">
                                                {{ trans('admin/main.no_result') }}
                                            </div>
                                        @endif
                                    </div>
                                @endcan

                                {{-- CREATE / EDIT TAB --}}
                                @can('admin_booking_season_create')
                                    <div class="tab-pane fade {{ $seasonCreateActive ? 'active show' : '' }}"
                                         id="newSeason" role="tabpanel">
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <form action="{{ getAdminPanelUrl() }}/booking/season/{{ !empty($editSeason) ? $editSeason->id . '/update' : 'store' }}"
                                                      method="post">
                                                    {{ csrf_field() }}

                                                    {{-- Booking --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.booking') }}</label>
                                                        @php $selectedBookingId = !empty($editSeason) ? $editSeason->booking_id : old('booking_id'); @endphp
                                                        <select name="booking_id" class="form-control @error('booking_id') is-invalid @enderror">
                                                            <option value="">{{ trans('admin/main.select') }}</option>
                                                            @foreach($bookings as $booking)
                                                                <option value="{{ $booking->id }}"
                                                                    {{ (string)$selectedBookingId === (string)$booking->id ? 'selected' : '' }}>
                                                                    #{{ $booking->id }} - {{ $booking->title }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('booking_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>

                                                    {{-- Season Name --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.name') }}</label>
                                                        <input type="text" name="name"
                                                               class="form-control @error('name') is-invalid @enderror"
                                                               value="{{ !empty($editSeason) ? $editSeason->name : old('name') }}"
                                                               placeholder="e.g. Peak, Off-Peak, Holiday"/>
                                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>

                                                    {{-- Start Date --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.start_date') }}</label>
                                                        <input type="date" name="start_date"
                                                               class="form-control @error('start_date') is-invalid @enderror"
                                                               value="{{ !empty($editSeason) ? $editSeason->start_date->format('Y-m-d') : old('start_date') }}"/>
                                                        @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>

                                                    {{-- End Date --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.end_date') }}</label>
                                                        <input type="date" name="end_date"
                                                               class="form-control @error('end_date') is-invalid @enderror"
                                                               value="{{ !empty($editSeason) ? $editSeason->end_date->format('Y-m-d') : old('end_date') }}"/>
                                                        @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>

                                                    {{-- Price Modifier --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.price_modifier') }}</label>
                                                        <input type="number" name="price_modifier" step="0.0001" min="0"
                                                               class="form-control @error('price_modifier') is-invalid @enderror"
                                                               value="{{ !empty($editSeason) ? $editSeason->price_modifier : old('price_modifier', 1) }}"
                                                               placeholder="e.g. 1.5 for +50% or 200 for fixed"/>
                                                        @error('price_modifier') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>

                                                    {{-- Modifier Type --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.modifier_type') }}</label>
                                                        @php $selectedType = !empty($editSeason) ? $editSeason->modifier_type : old('modifier_type', 'multiplier'); @endphp
                                                        <select name="modifier_type" class="form-control @error('modifier_type') is-invalid @enderror">
                                                            <option value="multiplier" {{ $selectedType === 'multiplier' ? 'selected' : '' }}>
                                                                Multiplier (e.g. 1.5 = +50%)
                                                            </option>
                                                            <option value="fixed" {{ $selectedType === 'fixed' ? 'selected' : '' }}>
                                                                Fixed Amount
                                                            </option>
                                                        </select>
                                                        @error('modifier_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>

                                                    {{-- Status --}}
                                                    <div class="form-group">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" name="status"
                                                                   class="custom-control-input" id="status"
                                                                   {{ (!empty($editSeason) && $editSeason->status) || empty($editSeason) ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="status">
                                                                {{ trans('admin/main.active') }}
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="text-right col-12 mt-3">
                                                        @if(!empty($editSeason))
                                                            <a href="{{ getAdminPanelUrl() }}/booking/season"
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
@endsection