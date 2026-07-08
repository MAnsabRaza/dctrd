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

<style>
    .calendar-settings-container {
        background: #ffffff;
        border-radius: 8px;
        padding: 24px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }
    .settings-section-title {
        font-size: 16px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .form-section-separator {
        border-top: 1px solid #f1f5f9;
        margin: 30px 0;
    }
    .custom-form-label {
        font-weight: 500;
        font-size: 13px;
        color: #475569;
        margin-bottom: 6px;
    }
    .custom-form-control {
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 10px 14px;
        font-size: 14px;
        transition: all 0.2s;
        background-color: #f8fafc;
    }
    .custom-form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        background-color: #fff;
    }
    .placeholder-hint {
        font-size: 12px;
        color: #64748b;
        margin-top: 6px;
    }
    .placeholder-hint code {
        background: #f1f5f9;
        color: #334155;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 11px;
        margin-right: 4px;
        display: inline-block;
        margin-top: 4px;
    }
    .action-btn {
        padding: 10px 20px;
        font-size: 14px;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.2s;
    }
    .toggle-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #f8fafc;
        padding: 12px 16px;
        border-radius: 6px;
        margin-bottom: 20px;
    }
</style>

<div class="p-16">
    <div class="calendar-settings-container">

        {{-- ================= GOOGLE CALENDAR SECTION ================= --}}
        <div class="settings-section-title">
            <h4 class="mb-0 font-weight-bold" style="font-size: 18px;">{{ trans('calendar.google_calendar_sync') }}</h4>
            <span class="badge {{ $google && $google->isConnected() ? 'badge-success' : 'badge-secondary' }} font-13 px-3 py-2">
                {{ $google && $google->isConnected() ? trans('calendar.connected') : trans('calendar.disconnected') }}
            </span>
        </div>
        <p class="text-muted font-14 mb-4">{{ trans('calendar.google_sync_hint') }}</p>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="custom-form-label">{{ trans('calendar.client_id') }}</label>
                <input type="text" id="google_client_id" class="form-control custom-form-control"
                       value="{{ $google->client_id ?? '' }}"
                       placeholder="{{ trans('calendar.enter_credentials') }}">
            </div>
            <div class="form-group col-md-6">
                <label class="custom-form-label">{{ trans('calendar.client_secret') }}</label>
                <input type="password" id="google_client_secret" class="form-control custom-form-control"
                       value="{{ $google->client_secret ?? '' }}"
                       placeholder="{{ trans('calendar.enter_credentials') }}">
            </div>
        </div>

        <div class="mb-4">
            <button type="button" class="btn btn-secondary action-btn mr-2" onclick="calendarSaveCredentials('google', false)">
                {{ trans('admin/main.submit') }}
            </button>

            @if($google && $google->isConnected())
                <button type="button" class="btn btn-outline-danger action-btn" onclick="calendarDisconnect('google')">
                    {{ trans('calendar.disconnect') }}
                </button>
            @else
                <button type="button" class="btn btn-primary action-btn" onclick="calendarSaveCredentials('google', true)">
                    {{ trans('calendar.connect_google') }}
                </button>
            @endif
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px;" class="mb-4">
            <h5 class="font-weight-bold mb-3" style="font-size: 15px; color: #334155;">{{ trans('calendar.event_template') }}</h5>
            
            <div class="form-group">
                <label class="custom-form-label">{{ trans('calendar.title') }}</label>
                <input type="text" id="google_title_template" class="form-control custom-form-control"
                       value="{{ $googleSettings->event_title_template ?? '{CUSTOMER_NAME} - {PRODUCT_NAME}' }}">
            </div>
            
            <div class="form-group mb-2">
                <label class="custom-form-label">{{ trans('calendar.description') }}</label>
                <textarea id="google_description_template" rows="3" class="form-control custom-form-control">{{ $googleSettings->event_description_template ?? '' }}</textarea>
            </div>
            
            <div class="placeholder-hint mb-3">
                <span class="d-block mb-1 font-weight-500">{{ trans('calendar.placeholders') }}:</span>
                <code>{CUSTOMER_NAME}</code> <code>{BOOKING_STATUS}</code> <code>{PRODUCT_NAME}</code>
                <code>{BOOKING_ID}</code> <code>{START_AT}</code> <code>{END_AT}</code> <code>{RESOURCE_NAME}</code>
            </div>

            <div class="custom-control custom-checkbox mb-2">
                <input type="checkbox" class="custom-control-input" id="google_attendee"
                       {{ ($googleSettings->add_customer_as_attendee ?? false) ? 'checked' : '' }}>
                <label class="custom-control-label font-14" for="google_attendee">{{ trans('calendar.add_customer_as_attendee') }}</label>
            </div>
            <div class="custom-control custom-checkbox mb-3">
                <input type="checkbox" class="custom-control-input" id="google_debug"
                       {{ ($googleSettings->debug_mode ?? false) ? 'checked' : '' }}>
                <label class="custom-control-label font-14" for="google_debug">{{ trans('calendar.debug_mode') }}</label>
            </div>

            <button type="button" class="btn btn-primary action-btn" onclick="calendarSaveSettings('google')">
                {{ trans('admin/main.submit') }}
            </button>
        </div>


        <div class="form-section-separator"></div>


        {{-- ================= OUTLOOK CALENDAR SECTION ================= --}}
        <div class="settings-section-title">
            <h4 class="mb-0 font-weight-bold" style="font-size: 18px;">{{ trans('calendar.outlook_calendar_sync') }}</h4>
            <span class="badge {{ $outlook && $outlook->isConnected() ? 'badge-success' : 'badge-secondary' }} font-13 px-3 py-2">
                {{ $outlook && $outlook->isConnected() ? trans('calendar.connected') : trans('calendar.disconnected') }}
            </span>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="custom-form-label">{{ trans('calendar.client_id') }}</label>
                <input type="text" id="outlook_client_id" class="form-control custom-form-control"
                       value="{{ $outlook->client_id ?? '' }}">
            </div>
            <div class="form-group col-md-6">
                <label class="custom-form-label">{{ trans('calendar.client_secret') }}</label>
                <input type="password" id="outlook_client_secret" class="form-control custom-form-control"
                       value="{{ $outlook->client_secret ?? '' }}">
            </div>
        </div>

        <div class="mb-4">
            <button type="button" class="btn btn-secondary action-btn mr-2" onclick="calendarSaveCredentials('outlook', false)">
                {{ trans('admin/main.submit') }}
            </button>

            @if($outlook && $outlook->isConnected())
                <button type="button" class="btn btn-outline-danger action-btn" onclick="calendarDisconnect('outlook')">
                    {{ trans('calendar.disconnect') }}
                </button>
            @else
                <button type="button" class="btn btn-primary action-btn" onclick="calendarSaveCredentials('outlook', true)">
                    {{ trans('calendar.connect_outlook') }}
                </button>
            @endif
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px;" class="mb-4">
            <h5 class="font-weight-bold mb-3" style="font-size: 15px; color: #334155;">{{ trans('calendar.event_template') }}</h5>
            
            <div class="form-group">
                <label class="custom-form-label">{{ trans('calendar.title') }}</label>
                <input type="text" id="outlook_title_template" class="form-control custom-form-control"
                       value="{{ $outlookSettings->event_title_template ?? '{CUSTOMER_NAME} - {PRODUCT_NAME}' }}">
            </div>
            <div class="form-group">
                <label class="custom-form-label">{{ trans('calendar.description') }}</label>
                <textarea id="outlook_description_template" rows="3" class="form-control custom-form-control">{{ $outlookSettings->event_description_template ?? '' }}</textarea>
            </div>

            <div class="custom-control custom-checkbox mb-2">
                <input type="checkbox" class="custom-control-input" id="outlook_attendee"
                       {{ ($outlookSettings->add_customer_as_attendee ?? false) ? 'checked' : '' }}>
                <label class="custom-control-label font-14" for="outlook_attendee">{{ trans('calendar.add_customer_as_attendee') }}</label>
            </div>
            <div class="custom-control custom-checkbox mb-3">
                <input type="checkbox" class="custom-control-input" id="outlook_debug"
                       {{ ($outlookSettings->debug_mode ?? false) ? 'checked' : '' }}>
                <label class="custom-control-label font-14" for="outlook_debug">{{ trans('calendar.debug_mode') }}</label>
            </div>

            <button type="button" class="btn btn-primary action-btn" onclick="calendarSaveSettings('outlook')">
                {{ trans('admin/main.submit') }}
            </button>
        </div>


        <div class="form-section-separator"></div>


        {{-- ================= ICAL EXPORT SECTION ================= --}}
        <h4 class="font-weight-bold mb-3" style="font-size: 18px; color: #1e293b;">{{ trans('calendar.ical_export') }}</h4>
        
        <div class="toggle-wrapper">
            <span class="font-14 font-weight-500 text-dark">{{ trans('calendar.enable_ical_export') }}</span>
            <div class="custom-control custom-switch mb-0">
                <input type="checkbox" class="custom-control-input" id="p_ical_toggle"
                       onchange="calendarToggleIcal(this.checked)"
                       {{ ($icalSettings->ical_export_enabled ?? false) ? 'checked' : '' }}>
                <label class="custom-control-label" for="p_ical_toggle"></label>
            </div>
        </div>

        @if($calendarIcalUrl)
            <div class="input-group mb-2">
                <input type="text" class="form-control custom-form-control" id="p_icalUrlField" value="{{ $calendarIcalUrl }}" readonly style="background-color: #f1f5f9;">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary px-3" type="button"
                            onclick="navigator.clipboard.writeText(document.getElementById('p_icalUrlField').value)">
                        {{ trans('calendar.copy_url') }}
                    </button>
                    <a href="{{ $calendarIcalUrl }}" class="btn btn-outline-secondary px-3">{{ trans('calendar.download_ics') }}</a>
                </div>
            </div>
            <button type="button" class="btn btn-link p-0 font-13" onclick="calendarRegenerateIcal()">
                {{ trans('calendar.regenerate_url') }}
            </button>
        @endif


        <div class="form-section-separator"></div>


        {{-- ================= SYNC LOGS SECTION ================= --}}
        <h4 class="font-weight-bold mb-3" style="font-size: 18px; color: #1e293b;">{{ trans('calendar.sync_logs') }}</h4>
        
        <div class="table-responsive style-table" style="border: 1px solid #e2e8f0; border-radius: 8px;">
            <table class="table mb-0">
                <thead style="background-color: #f8fafc;">
                    <tr>
                        <th style="border-top: none; color: #64748b; font-weight: 600;">{{ trans('calendar.date') }}</th>
                        <th style="border-top: none; color: #64748b; font-weight: 600;">{{ trans('calendar.provider') }}</th>
                        <th style="border-top: none; color: #64748b; font-weight: 600;">{{ trans('calendar.action') }}</th>
                        <th style="border-top: none; color: #64748b; font-weight: 600;">{{ trans('calendar.status') }}</th>
                        <th style="border-top: none; color: #64748b; font-weight: 600;">{{ trans('calendar.details') }}</th>
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
            ? (provider === 'google'
                ? "{{ route('panel.setting.external-connections.credentials.connect', 'google') }}"
                : "{{ route('panel.setting.external-connections.credentials.connect', 'outlook') }}")
            : (provider === 'google'
                ? "{{ route('panel.setting.external-connections.credentials', 'google') }}"
                : "{{ route('panel.setting.external-connections.credentials', 'outlook') }}");

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
        const url = provider === 'google'
            ? "{{ route('panel.setting.external-connections.settings', 'google') }}"
            : "{{ route('panel.setting.external-connections.settings', 'outlook') }}";

        calendarPost(url, {
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
        calendarPost("{{ url('/calendar') }}/" + provider + "/disconnect", {})
            .then(() => window.location.reload())
            .catch((err) => alert(err.message));
    }
</script>