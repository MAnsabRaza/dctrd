{{--
    Admin > Users > Edit > External Connections tab.

    Expects (already available when included from the user edit page via the
    Admin\CalendarSettingsController::calendarViewData() helper):
        $user                  App\Models\User
        $calendarIntegrations  Collection keyed by provider
        $calendarSettings      Collection keyed by provider (google/outlook/ical)
        $calendarIcalUrl       string|null
        $calendarLogs          Collection of CalendarLog
--}}

<div class="tab-pane mt-3 fade {{ (request()->get('tab') == 'externalConnections') ? 'active show' : '' }}"
     id="externalConnections" role="tabpanel" aria-labelledby="externalConnections-tab">

    @php
        $google = $calendarIntegrations->get('google');
        $outlook = $calendarIntegrations->get('outlook');
        $googleSettings = $calendarSettings->get('google');
        $outlookSettings = $calendarSettings->get('outlook');
        $icalSettings = $calendarSettings->get('ical');
    @endphp

    <div class="row">
        <div class="col-12">

            {{-- ================= GOOGLE CALENDAR ================= --}}
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                    <div>
                        <h4 class="mb-0">{{ trans('calendar.google_calendar_sync') }}</h4>
                        <p class="text-muted mb-0 font-14">
                            {{ trans('calendar.google_sync_hint') }}
                        </p>
                    </div>
                    <span class="badge {{ $google && $google->isConnected() ? 'badge-success' : 'badge-secondary' }} font-14 px-3 py-2">
                        {{ $google && $google->isConnected() ? trans('calendar.connected') : trans('calendar.disconnected') }}
                    </span>
                </div>
                <div class="card-body">

                    <p class="font-13 mb-3">
                        <a href="#" target="_blank">{{ trans('calendar.read_more_setup') }}</a> &middot;
                        <a href="#" target="_blank">{{ trans('calendar.read_more_troubleshoot') }}</a> &middot;
                        <a href="#" target="_blank">{{ trans('calendar.read_more_faq') }}</a>
                    </p>

                    <form action="{{ getAdminPanelUrl("/users/{$user->id}/calendar/google/credentials") }}" method="POST">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>{{ trans('calendar.client_id') }}</label>
                                <input type="text" name="client_id" class="form-control"
                                       value="{{ old('client_id', $google->client_id ?? '') }}"
                                       placeholder="{{ trans('calendar.enter_credentials') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>{{ trans('calendar.client_secret') }}</label>
                                <input type="password" name="client_secret" class="form-control"
                                       value="{{ old('client_secret', $google->client_secret ?? '') }}"
                                       placeholder="{{ trans('calendar.enter_credentials') }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-secondary">{{ trans('admin/main.submit') }}</button>

                        @if($google && $google->isConnected())
                            <a href="#" onclick="event.preventDefault(); document.getElementById('googleDisconnectForm').submit();"
                               class="btn btn-outline-danger">{{ trans('calendar.disconnect') }}</a>
                        @else
                            <a href="{{ route('calendar.google.redirect', ['return_to' => url()->current()]) }}"
                               class="btn btn-primary">{{ trans('calendar.connect_google') }}</a>
                        @endif
                    </form>

                    <form id="googleDisconnectForm" action="{{ route('calendar.disconnect', 'google') }}" method="POST" class="d-none">
                        @csrf
                    </form>

                    <hr>

                    <form action="{{ getAdminPanelUrl("/users/{$user->id}/calendar/google/settings") }}" method="POST">
                        @csrf
                        <h5>{{ trans('calendar.event_template') }}</h5>
                        <div class="form-group">
                            <label>{{ trans('calendar.title') }}</label>
                            <input type="text" name="event_title_template" class="form-control"
                                   value="{{ old('event_title_template', $googleSettings->event_title_template ?? '{CUSTOMER_NAME} - {PRODUCT_NAME}') }}">
                        </div>
                        <div class="form-group">
                            <label>{{ trans('calendar.description') }}</label>
                            <textarea name="event_description_template" rows="3" class="form-control">{{ old('event_description_template', $googleSettings->event_description_template ?? '') }}</textarea>
                        </div>
                        <p class="text-muted font-13">
                            {{ trans('calendar.placeholders') }}:
                            <code>{CUSTOMER_NAME}</code> <code>{BOOKING_STATUS}</code> <code>{PRODUCT_NAME}</code>
                            <code>{BOOKING_ID}</code> <code>{START_AT}</code> <code>{END_AT}</code> <code>{RESOURCE_NAME}</code>
                        </p>

                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="google_attendee" name="add_customer_as_attendee" value="1"
                                   {{ old('add_customer_as_attendee', $googleSettings->add_customer_as_attendee ?? false) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="google_attendee">{{ trans('calendar.add_customer_as_attendee') }}</label>
                        </div>
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="google_debug" name="debug_mode" value="1"
                                   {{ old('debug_mode', $googleSettings->debug_mode ?? false) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="google_debug">{{ trans('calendar.debug_mode') }}</label>
                        </div>

                        <button type="submit" class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
                    </form>
                </div>
            </div>

            {{-- ================= OUTLOOK CALENDAR ================= --}}
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
                    <div>
                        <h4 class="mb-0">{{ trans('calendar.outlook_calendar_sync') }}</h4>
                        <p class="text-muted mb-0 font-14">{{ trans('calendar.outlook_sync_hint') }}</p>
                    </div>
                    <span class="badge {{ $outlook && $outlook->isConnected() ? 'badge-success' : 'badge-secondary' }} font-14 px-3 py-2">
                        {{ $outlook && $outlook->isConnected() ? trans('calendar.connected') : trans('calendar.disconnected') }}
                    </span>
                </div>
                <div class="card-body">
                    <form action="{{ getAdminPanelUrl("/users/{$user->id}/calendar/outlook/credentials") }}" method="POST">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>{{ trans('calendar.client_id') }}</label>
                                <input type="text" name="client_id" class="form-control"
                                       value="{{ old('client_id', $outlook->client_id ?? '') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label>{{ trans('calendar.client_secret') }}</label>
                                <input type="password" name="client_secret" class="form-control"
                                       value="{{ old('client_secret', $outlook->client_secret ?? '') }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-secondary">{{ trans('admin/main.submit') }}</button>

                        @if($outlook && $outlook->isConnected())
                            <a href="#" onclick="event.preventDefault(); document.getElementById('outlookDisconnectForm').submit();"
                               class="btn btn-outline-danger">{{ trans('calendar.disconnect') }}</a>
                        @else
                            <a href="{{ route('calendar.outlook.redirect', ['return_to' => url()->current()]) }}"
                               class="btn btn-primary">{{ trans('calendar.connect_outlook') }}</a>
                        @endif
                    </form>

                    <form id="outlookDisconnectForm" action="{{ route('calendar.disconnect', 'outlook') }}" method="POST" class="d-none">
                        @csrf
                    </form>

                    <hr>

                    <form action="{{ getAdminPanelUrl("/users/{$user->id}/calendar/outlook/settings") }}" method="POST">
                        @csrf
                        <h5>{{ trans('calendar.event_template') }}</h5>
                        <div class="form-group">
                            <label>{{ trans('calendar.title') }}</label>
                            <input type="text" name="event_title_template" class="form-control"
                                   value="{{ old('event_title_template', $outlookSettings->event_title_template ?? '{CUSTOMER_NAME} - {PRODUCT_NAME}') }}">
                        </div>
                        <div class="form-group">
                            <label>{{ trans('calendar.description') }}</label>
                            <textarea name="event_description_template" rows="3" class="form-control">{{ old('event_description_template', $outlookSettings->event_description_template ?? '') }}</textarea>
                        </div>

                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input" id="outlook_attendee" name="add_customer_as_attendee" value="1"
                                   {{ old('add_customer_as_attendee', $outlookSettings->add_customer_as_attendee ?? false) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="outlook_attendee">{{ trans('calendar.add_customer_as_attendee') }}</label>
                        </div>
                        <div class="custom-control custom-checkbox mb-3">
                            <input type="checkbox" class="custom-control-input" id="outlook_debug" name="debug_mode" value="1"
                                   {{ old('debug_mode', $outlookSettings->debug_mode ?? false) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="outlook_debug">{{ trans('calendar.debug_mode') }}</label>
                        </div>

                        <button type="submit" class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
                    </form>
                </div>
            </div>

            {{-- ================= ICAL EXPORT ================= --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">{{ trans('calendar.ical_export') }}</h4>
                </div>
                <div class="card-body">
                    <form action="{{ getAdminPanelUrl("/users/{$user->id}/calendar/ical/toggle") }}" method="POST" class="mb-3">
                        @csrf
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="ical_toggle" name="ical_export_enabled" value="1"
                                   onchange="this.form.submit()"
                                   {{ ($icalSettings->ical_export_enabled ?? false) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="ical_toggle">{{ trans('calendar.enable_ical_export') }}</label>
                        </div>
                    </form>

                    @if($calendarIcalUrl)
                        <div class="input-group mb-2">
                            <input type="text" class="form-control" id="icalUrlField" value="{{ $calendarIcalUrl }}" readonly>
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button"
                                        onclick="navigator.clipboard.writeText(document.getElementById('icalUrlField').value)">
                                    {{ trans('calendar.copy_url') }}
                                </button>
                                <a href="{{ $calendarIcalUrl }}" class="btn btn-outline-secondary">{{ trans('calendar.download_ics') }}</a>
                            </div>
                        </div>
                        <form action="{{ getAdminPanelUrl("/users/{$user->id}/calendar/ical/regenerate") }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-link p-0 font-13">{{ trans('calendar.regenerate_url') }}</button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- ================= SYNC LOGS ================= --}}
            <div class="card mb-0">
                <div class="card-header">
                    <h4 class="mb-0">{{ trans('calendar.sync_logs') }}</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>{{ trans('calendar.date') }}</th>
                                    <th>{{ trans('calendar.provider') }}</th>
                                    <th>{{ trans('calendar.action') }}</th>
                                    <th>{{ trans('calendar.status') }}</th>
                                    <th>{{ trans('calendar.details') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($calendarLogs as $log)
                                    <tr>
                                        <td>{{ $log->created_at->format('M d, H:i') }}</td>
                                        <td class="text-capitalize">{{ $log->provider }}</td>
                                        <td class="text-capitalize">{{ $log->action }}</td>
                                        <td>
                                            @if($log->status === 'success')
                                                <span class="text-success">&#10003; {{ trans('calendar.success') }}</span>
                                            @elseif($log->status === 'failed')
                                                <span class="text-danger">&#10007; {{ trans('calendar.failed') }}</span>
                                            @else
                                                <span class="text-warning">{{ trans('calendar.pending') }}</span>
                                            @endif
                                        </td>
                                        <td class="font-13 text-muted">{{ \Illuminate\Support\Str::limit($log->details, 60) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">{{ trans('calendar.no_logs') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
