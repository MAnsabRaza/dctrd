@php
    $calendarIntegrations = $calendarIntegrations ?? collect();
    $calendarSettings     = $calendarSettings ?? collect();
    $calendarLogs         = $calendarLogs ?? collect();
    $calendarIcalUrl      = $calendarIcalUrl ?? null;

    $google = $calendarIntegrations->get('google');
    $outlook = $calendarIntegrations->get('outlook');
    $googleSettings = $calendarSettings->get('google');
    $outlookSettings = $calendarSettings->get('outlook');
    $icalSettings = $calendarSettings->get('ical');
@endphp

<div class="p-16">

    {{-- ================= GOOGLE CALENDAR ================= --}}
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
            <h4 class="mb-0">{{ trans('calendar.google_calendar_sync') }}</h4>
            <span class="badge {{ $google && $google->isConnected() ? 'badge-success' : 'badge-secondary' }} font-14 px-3 py-2"
                  id="google_status_badge">
                {{ $google && $google->isConnected() ? trans('calendar.connected') : trans('calendar.disconnected') }}
            </span>
        </div>
        <div class="card-body">
            <p class="text-muted font-14">{{ trans('calendar.google_sync_hint') }}</p>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>{{ trans('calendar.client_id') }}</label>
                    <input type="text" id="google_client_id" class="form-control"
                           value="{{ $google->client_id ?? '' }}"
                           placeholder="{{ trans('calendar.enter_credentials') }}">
                </div>
                <div class="form-group col-md-6">
                    <label>{{ trans('calendar.client_secret') }}</label>
                    <input type="password" id="google_client_secret" class="form-control"
                           value="{{ $google->client_secret ?? '' }}"
                           placeholder="{{ trans('calendar.enter_credentials') }}">
                </div>
            </div>

            <button type="button" class="btn btn-secondary" onclick="calendarSaveCredentials('google', false)">
                {{ trans('admin/main.submit') }}
            </button>

            @if($google && $google->isConnected())
                <button type="button" class="btn btn-outline-danger" onclick="calendarDisconnect('google')">
                    {{ trans('calendar.disconnect') }}
                </button>
            @else
                <button type="button" class="btn btn-primary" onclick="calendarSaveCredentials('google', true)">
                    {{ trans('calendar.connect_google') }}
                </button>
            @endif

            <hr>

            <h5>{{ trans('calendar.event_template') }}</h5>
            <div class="form-group">
                <label>{{ trans('calendar.title') }}</label>
                <input type="text" id="google_title_template" class="form-control"
                       value="{{ $googleSettings->event_title_template ?? '{CUSTOMER_NAME} - {PRODUCT_NAME}' }}">
            </div>
            <div class="form-group">
                <label>{{ trans('calendar.description') }}</label>
                <textarea id="google_description_template" rows="3" class="form-control">{{ $googleSettings->event_description_template ?? '' }}</textarea>
            </div>
            <p class="text-muted font-13">
                {{ trans('calendar.placeholders') }}:
                <code>{CUSTOMER_NAME}</code> <code>{BOOKING_STATUS}</code> <code>{PRODUCT_NAME}</code>
                <code>{BOOKING_ID}</code> <code>{START_AT}</code> <code>{END_AT}</code> <code>{RESOURCE_NAME}</code>
            </p>

            <div class="custom-control custom-checkbox mb-2">
                <input type="checkbox" class="custom-control-input" id="google_attendee"
                       {{ ($googleSettings->add_customer_as_attendee ?? false) ? 'checked' : '' }}>
                <label class="custom-control-label" for="google_attendee">{{ trans('calendar.add_customer_as_attendee') }}</label>
            </div>
            <div class="custom-control custom-checkbox mb-3">
                <input type="checkbox" class="custom-control-input" id="google_debug"
                       {{ ($googleSettings->debug_mode ?? false) ? 'checked' : '' }}>
                <label class="custom-control-label" for="google_debug">{{ trans('calendar.debug_mode') }}</label>
            </div>

            <button type="button" class="btn btn-primary" onclick="calendarSaveSettings('google')">
                {{ trans('admin/main.submit') }}
            </button>
        </div>
    </div>

    {{-- ================= OUTLOOK CALENDAR ================= --}}
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between flex-wrap">
            <h4 class="mb-0">{{ trans('calendar.outlook_calendar_sync') }}</h4>
            <span class="badge {{ $outlook && $outlook->isConnected() ? 'badge-success' : 'badge-secondary' }} font-14 px-3 py-2"
                  id="outlook_status_badge">
                {{ $outlook && $outlook->isConnected() ? trans('calendar.connected') : trans('calendar.disconnected') }}
            </span>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>{{ trans('calendar.client_id') }}</label>
                    <input type="text" id="outlook_client_id" class="form-control"
                           value="{{ $outlook->client_id ?? '' }}">
                </div>
                <div class="form-group col-md-6">
                    <label>{{ trans('calendar.client_secret') }}</label>
                    <input type="password" id="outlook_client_secret" class="form-control"
                           value="{{ $outlook->client_secret ?? '' }}">
                </div>
            </div>

            <button type="button" class="btn btn-secondary" onclick="calendarSaveCredentials('outlook', false)">
                {{ trans('admin/main.submit') }}
            </button>

            @if($outlook && $outlook->isConnected())
                <button type="button" class="btn btn-outline-danger" onclick="calendarDisconnect('outlook')">
                    {{ trans('calendar.disconnect') }}
                </button>
            @else
                <button type="button" class="btn btn-primary" onclick="calendarSaveCredentials('outlook', true)">
                    {{ trans('calendar.connect_outlook') }}
                </button>
            @endif

            <hr>

            <h5>{{ trans('calendar.event_template') }}</h5>
            <div class="form-group">
                <label>{{ trans('calendar.title') }}</label>
                <input type="text" id="outlook_title_template" class="form-control"
                       value="{{ $outlookSettings->event_title_template ?? '{CUSTOMER_NAME} - {PRODUCT_NAME}' }}">
            </div>
            <div class="form-group">
                <label>{{ trans('calendar.description') }}</label>
                <textarea id="outlook_description_template" rows="3" class="form-control">{{ $outlookSettings->event_description_template ?? '' }}</textarea>
            </div>

            <div class="custom-control custom-checkbox mb-2">
                <input type="checkbox" class="custom-control-input" id="outlook_attendee"
                       {{ ($outlookSettings->add_customer_as_attendee ?? false) ? 'checked' : '' }}>
                <label class="custom-control-label" for="outlook_attendee">{{ trans('calendar.add_customer_as_attendee') }}</label>
            </div>
            <div class="custom-control custom-checkbox mb-3">
                <input type="checkbox" class="custom-control-input" id="outlook_debug"
                       {{ ($outlookSettings->debug_mode ?? false) ? 'checked' : '' }}>
                <label class="custom-control-label" for="outlook_debug">{{ trans('calendar.debug_mode') }}</label>
            </div>

            <button type="button" class="btn btn-primary" onclick="calendarSaveSettings('outlook')">
                {{ trans('admin/main.submit') }}
            </button>
        </div>
    </div>

    {{-- ================= ICAL EXPORT ================= --}}
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">{{ trans('calendar.ical_export') }}</h4>
        </div>
        <div class="card-body">
            <div class="custom-control custom-switch mb-3">
                <input type="checkbox" class="custom-control-input" id="p_ical_toggle"
                       onchange="calendarToggleIcal(this.checked)"
                       {{ ($icalSettings->ical_export_enabled ?? false) ? 'checked' : '' }}>
                <label class="custom-control-label" for="p_ical_toggle">{{ trans('calendar.enable_ical_export') }}</label>
            </div>

            @if($calendarIcalUrl)
                <div class="input-group mb-2">
                    <input type="text" class="form-control" id="p_icalUrlField" value="{{ $calendarIcalUrl }}" readonly>
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button"
                                onclick="navigator.clipboard.writeText(document.getElementById('p_icalUrlField').value)">
                            {{ trans('calendar.copy_url') }}
                        </button>
                        <a href="{{ $calendarIcalUrl }}" class="btn btn-outline-secondary">{{ trans('calendar.download_ics') }}</a>
                    </div>
                </div>
                <button type="button" class="btn btn-link p-0 font-13" onclick="calendarRegenerateIcal()">
                    {{ trans('calendar.regenerate_url') }}
                </button>
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
                    <tbody id="calendarLogsBody">
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
                                <td class="font-13 text-muted">
                                    {{ \Illuminate\Support\Str::limit($log->error_message ?? '-', 60) }}
                                </td>
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

<script>
    function calendarCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('input[name="_token"]')?.value;
    }

    function calendarPost(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': calendarCsrfToken(),
            },
            body: JSON.stringify(data || {}),
        }).then(async (res) => {
            const body = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw new Error(body.message || 'Request failed');
            }
            return body;
        });
    }

    function calendarSaveCredentials(provider, connect) {
        const clientId = document.getElementById(provider + '_client_id').value;
        const clientSecret = document.getElementById(provider + '_client_secret').value;

        const url = connect
            ? "{{ url('/panel/setting/external-connections') }}/" + provider + "/credentials/connect"
            : "{{ url('/panel/setting/external-connections') }}/" + provider + "/credentials";

        calendarPost(url, {
            client_id: clientId,
            client_secret: clientSecret,
            return_to: window.location.href,
        }).then((res) => {
            if (connect && res.redirect) {
                window.location.href = res.redirect;
            } else {
                window.location.reload();
            }
        }).catch((err) => alert(err.message));
    }

    function calendarSaveSettings(provider) {
        calendarPost("{{ url('/panel/setting/external-connections') }}/" + provider + "/settings", {
            event_title_template: document.getElementById(provider + '_title_template').value,
            event_description_template: document.getElementById(provider + '_description_template').value,
            add_customer_as_attendee: document.getElementById(provider + '_attendee').checked,
            debug_mode: document.getElementById(provider + '_debug').checked,
        }).then(() => window.location.reload())
          .catch((err) => alert(err.message));
    }

    function calendarToggleIcal(enabled) {
        calendarPost("{{ route('panel.setting.external-connections.ical.toggle') }}", {
            ical_export_enabled: enabled,
        }).then(() => window.location.reload())
          .catch((err) => alert(err.message));
    }

    function calendarRegenerateIcal() {
        calendarPost("{{ route('panel.setting.external-connections.ical.regenerate') }}", {})
            .then(() => window.location.reload())
            .catch((err) => alert(err.message));
    }

    function calendarDisconnect(provider) {
        calendarPost("{{ url('/panel/calendar/disconnect') }}/" + provider, {})
            .then(() => window.location.reload())
            .catch((err) => alert(err.message));
    }
</script>