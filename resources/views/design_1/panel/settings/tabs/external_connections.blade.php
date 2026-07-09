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
        --cs-border: #e5e9f0;
        --cs-bg-soft: #f8fafc;
        --cs-text: #1e293b;
        --cs-text-muted: #64748b;
        --cs-primary: #2563eb;
        --cs-primary-dark: #1d4ed8;
        --cs-success: #16a34a;
        --cs-success-bg: #ecfdf3;
        --cs-danger: #dc2626;
        --cs-radius: 12px;

        background: #ffffff;
        border: 1px solid var(--cs-border);
        border-radius: var(--cs-radius);
        padding: 28px 32px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        color: var(--cs-text);
    }

    .cs-provider-block {
        border: 1px solid var(--cs-border);
        border-radius: var(--cs-radius);
        padding: 24px;
        background: var(--cs-bg-soft);
        margin-bottom: 24px;
    }

    .settings-section-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--cs-text);
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .settings-section-title h4 {
        font-size: 17px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .cs-provider-icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: 700;
        color: #fff;
        flex-shrink: 0;
    }
    .cs-icon-google { background: linear-gradient(135deg,#4285f4,#34a853); }
    .cs-icon-outlook { background: linear-gradient(135deg,#0078d4,#2b88d8); }
    .cs-icon-ical { background: linear-gradient(135deg,#7c3aed,#a855f7); }

    .cs-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        font-weight: 600;
        padding: 5px 12px;
        border-radius: 999px;
        white-space: nowrap;
    }
    .cs-badge::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
    }
    .cs-badge-connected {
        background: var(--cs-success-bg);
        color: var(--cs-success);
    }
    .cs-badge-connected::before { background: var(--cs-success); }
    .cs-badge-disconnected {
        background: #f1f5f9;
        color: var(--cs-text-muted);
    }
    .cs-badge-disconnected::before { background: #94a3b8; }

    .form-section-separator {
        border-top: 1px solid var(--cs-border);
        margin: 32px 0;
    }

    .custom-form-label {
        font-weight: 600;
        font-size: 12.5px;
        color: var(--cs-text-muted);
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 6px;
        display: block;
    }

    .custom-form-control {
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 10px 14px;
        font-size: 14px;
        width: 100%;
        transition: all 0.15s ease;
        background-color: #ffffff;
        color: var(--cs-text);
    }
    .custom-form-control::placeholder { color: #94a3b8; }
    .custom-form-control:focus {
        outline: none;
        border-color: var(--cs-primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        background-color: #fff;
    }

    .placeholder-hint {
        font-size: 12.5px;
        color: var(--cs-text-muted);
        margin-top: 10px;
    }
    .placeholder-hint .hint-label {
        display: block;
        margin-bottom: 6px;
        font-weight: 600;
        color: var(--cs-text);
    }
    .placeholder-hint code {
        background: #eef2f7;
        color: #334155;
        padding: 3px 8px;
        border-radius: 5px;
        font-size: 11.5px;
        font-family: 'SFMono-Regular', Consolas, Menlo, monospace;
        margin: 0 4px 4px 0;
        display: inline-block;
        border: 1px solid #e2e8f0;
    }

    .action-btn {
        padding: 10px 20px;
        font-size: 13.5px;
        font-weight: 600;
        border-radius: 8px;
        border: 1px solid transparent;
        transition: all 0.15s ease;
        cursor: pointer;
    }
    .action-btn.btn-primary {
        background: var(--cs-primary);
        border-color: var(--cs-primary);
    }
    .action-btn.btn-primary:hover { background: var(--cs-primary-dark); }
    .action-btn.btn-secondary {
        background: #fff;
        border-color: #cbd5e1;
        color: var(--cs-text);
    }
    .action-btn.btn-secondary:hover { background: #f8fafc; }
    .action-btn.btn-outline-danger {
        background: #fff;
        border-color: #fecaca;
        color: var(--cs-danger);
    }
    .action-btn.btn-outline-danger:hover { background: #fef2f2; }

    .cs-template-card {
        background: #ffffff;
        border: 1px solid var(--cs-border);
        border-radius: 10px;
        padding: 20px;
    }
    .cs-template-card h5 {
        font-weight: 700;
        margin-bottom: 16px;
        font-size: 14px;
        color: var(--cs-text);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .cs-checkbox-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 4px 0;
        font-size: 13.5px;
        color: var(--cs-text);
    }

    .toggle-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--cs-bg-soft);
        border: 1px solid var(--cs-border);
        padding: 16px 18px;
        border-radius: 10px;
        margin-bottom: 16px;
    }
    .toggle-wrapper .toggle-label {
        font-size: 14px;
        font-weight: 600;
        color: var(--cs-text);
    }
    .toggle-wrapper .toggle-sub {
        font-size: 12.5px;
        color: var(--cs-text-muted);
        margin-top: 2px;
    }

    .cs-ical-url-row {
        display: flex;
        gap: 8px;
        margin-bottom: 10px;
    }
    .cs-ical-url-row input {
        font-family: 'SFMono-Regular', Consolas, Menlo, monospace;
        font-size: 13px;
        color: var(--cs-text);
        background-color: var(--cs-bg-soft) !important;
        flex: 1;
    }
    .cs-icon-btn {
        border: 1px solid #cbd5e1;
        background: #fff;
        border-radius: 8px;
        padding: 0 16px;
        font-size: 13px;
        font-weight: 600;
        color: var(--cs-text);
        white-space: nowrap;
        cursor: pointer;
    }
    .cs-icon-btn:hover { background: var(--cs-bg-soft); }

    .cs-regenerate-link {
        font-size: 12.5px;
        color: var(--cs-primary);
        font-weight: 600;
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
    }
    .cs-regenerate-link:hover { text-decoration: underline; }

    .cs-logs-table-wrap {
        border: 1px solid var(--cs-border);
        border-radius: 10px;
        overflow: hidden;
    }
    .cs-logs-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
    }
    .cs-logs-table thead th {
        background: var(--cs-bg-soft);
        color: var(--cs-text-muted);
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        text-align: left;
        padding: 12px 16px;
        border-bottom: 1px solid var(--cs-border);
    }
    .cs-logs-table tbody td {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        color: var(--cs-text);
    }
    .cs-logs-table tbody tr:last-child td { border-bottom: none; }
    .cs-logs-table tbody tr:hover { background: #fafbfc; }
    .cs-logs-table .cs-empty { text-align: center; color: var(--cs-text-muted); padding: 32px; }

    .cs-status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-weight: 600;
        font-size: 12.5px;
        padding: 3px 10px;
        border-radius: 999px;
    }
    .cs-status-success { background: var(--cs-success-bg); color: var(--cs-success); }
    .cs-status-failed { background: #fef2f2; color: var(--cs-danger); }
    .cs-status-pending { background: #fffbeb; color: #b45309; }
</style>

<div class="p-16">
    <div class="calendar-settings-container">

        {{-- ================= GOOGLE CALENDAR SECTION ================= --}}
        <div class="settings-section-title">
            <h4 class="mb-0"><span class="cs-provider-icon cs-icon-google">G</span>{{ trans('calendar.google_calendar_sync') }}</h4>
            <span class="cs-badge {{ $google && $google->isConnected() ? 'cs-badge-connected' : 'cs-badge-disconnected' }}">
                {{ $google && $google->isConnected() ? trans('calendar.connected') : trans('calendar.disconnected') }}
            </span>
        </div>
        <p class="text-muted font-14 mb-4" style="color:var(--cs-text-muted);">{{ trans('calendar.google_sync_hint') }}</p>

        <div class="cs-provider-block">
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

            <div class="mb-0 mt-2">
                <button type="button" class="btn action-btn btn-secondary mr-2" onclick="calendarSaveCredentials('google', false)">
                    {{ trans('admin/main.submit') }}
                </button>

                @if($google && $google->isConnected())
                    <button type="button" class="btn action-btn btn-outline-danger" onclick="calendarDisconnect('google')">
                        {{ trans('calendar.disconnect') }}
                    </button>
                @else
                    <button type="button" class="btn action-btn btn-primary" onclick="calendarSaveCredentials('google', true)">
                        {{ trans('calendar.connect_google') }}
                    </button>
                @endif
            </div>
        </div>

        <div class="cs-template-card mb-4">
            <h5>{{ trans('calendar.event_template') }}</h5>

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
                <span class="hint-label">{{ trans('calendar.placeholders') }}</span>
                <code>{CUSTOMER_NAME}</code> <code>{BOOKING_STATUS}</code> <code>{PRODUCT_NAME}</code>
                <code>{BOOKING_ID}</code> <code>{START_AT}</code> <code>{END_AT}</code> <code>{RESOURCE_NAME}</code>
            </div>

            <div class="cs-checkbox-row">
                <input type="checkbox" id="google_attendee" style="width:16px;height:16px;"
                       {{ ($googleSettings->add_customer_as_attendee ?? false) ? 'checked' : '' }}>
                <label class="mb-0" for="google_attendee">{{ trans('calendar.add_customer_as_attendee') }}</label>
            </div>
            <div class="cs-checkbox-row mb-3">
                <input type="checkbox" id="google_debug" style="width:16px;height:16px;"
                       {{ ($googleSettings->debug_mode ?? false) ? 'checked' : '' }}>
                <label class="mb-0" for="google_debug">{{ trans('calendar.debug_mode') }}</label>
            </div>

            <button type="button" class="btn action-btn btn-primary" onclick="calendarSaveSettings('google')">
                {{ trans('admin/main.submit') }}
            </button>
        </div>


        <div class="form-section-separator"></div>


        {{-- ================= OUTLOOK CALENDAR SECTION ================= --}}
        <div class="settings-section-title">
            <h4 class="mb-0"><span class="cs-provider-icon cs-icon-outlook">O</span>{{ trans('calendar.outlook_calendar_sync') }}</h4>
            <span class="cs-badge {{ $outlook && $outlook->isConnected() ? 'cs-badge-connected' : 'cs-badge-disconnected' }}">
                {{ $outlook && $outlook->isConnected() ? trans('calendar.connected') : trans('calendar.disconnected') }}
            </span>
        </div>
        <p class="text-muted font-14 mb-3" style="color:var(--cs-text-muted);">&nbsp;</p>

        <div class="cs-provider-block">
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

            <div class="mb-0 mt-2">
                <button type="button" class="btn action-btn btn-secondary mr-2" onclick="calendarSaveCredentials('outlook', false)">
                    {{ trans('admin/main.submit') }}
                </button>

                @if($outlook && $outlook->isConnected())
                    <button type="button" class="btn action-btn btn-outline-danger" onclick="calendarDisconnect('outlook')">
                        {{ trans('calendar.disconnect') }}
                    </button>
                @else
                    <button type="button" class="btn action-btn btn-primary" onclick="calendarSaveCredentials('outlook', true)">
                        {{ trans('calendar.connect_outlook') }}
                    </button>
                @endif
            </div>
        </div>

        <div class="cs-template-card mb-4">
            <h5>{{ trans('calendar.event_template') }}</h5>

            <div class="form-group">
                <label class="custom-form-label">{{ trans('calendar.title') }}</label>
                <input type="text" id="outlook_title_template" class="form-control custom-form-control"
                       value="{{ $outlookSettings->event_title_template ?? '{CUSTOMER_NAME} - {PRODUCT_NAME}' }}">
            </div>
            <div class="form-group mb-2">
                <label class="custom-form-label">{{ trans('calendar.description') }}</label>
                <textarea id="outlook_description_template" rows="3" class="form-control custom-form-control">{{ $outlookSettings->event_description_template ?? '' }}</textarea>
            </div>

            <div class="cs-checkbox-row">
                <input type="checkbox" id="outlook_attendee" style="width:16px;height:16px;"
                       {{ ($outlookSettings->add_customer_as_attendee ?? false) ? 'checked' : '' }}>
                <label class="mb-0" for="outlook_attendee">{{ trans('calendar.add_customer_as_attendee') }}</label>
            </div>
            <div class="cs-checkbox-row mb-3">
                <input type="checkbox" id="outlook_debug" style="width:16px;height:16px;"
                       {{ ($outlookSettings->debug_mode ?? false) ? 'checked' : '' }}>
                <label class="mb-0" for="outlook_debug">{{ trans('calendar.debug_mode') }}</label>
            </div>

            <button type="button" class="btn action-btn btn-primary" onclick="calendarSaveSettings('outlook')">
                {{ trans('admin/main.submit') }}
            </button>
        </div>


        <div class="form-section-separator"></div>


        {{-- ================= ICAL EXPORT SECTION ================= --}}
        <div class="settings-section-title" style="margin-bottom:16px;">
            <h4 class="mb-0"><span class="cs-provider-icon cs-icon-ical">i</span>{{ trans('calendar.ical_export') }}</h4>
        </div>

        <div class="toggle-wrapper">
            <div>
                <div class="toggle-label">{{ trans('calendar.enable_ical_export') }}</div>
                <div class="toggle-sub">{{ trans('calendar.ical_export_hint') ?? 'Share a read-only calendar feed with external apps' }}</div>
            </div>
            <div class="custom-control custom-switch mb-0">
                <input type="checkbox" class="custom-control-input" id="p_ical_toggle"
                       onchange="calendarToggleIcal(this.checked)"
                       {{ ($icalSettings->ical_export_enabled ?? false) ? 'checked' : '' }}>
                <label class="custom-control-label" for="p_ical_toggle"></label>
            </div>
        </div>

        @if($calendarIcalUrl)
            <div class="cs-ical-url-row">
                <input type="text" class="form-control custom-form-control" id="p_icalUrlField" value="{{ $calendarIcalUrl }}" readonly>
                <button class="cs-icon-btn" type="button"
                        onclick="navigator.clipboard.writeText(document.getElementById('p_icalUrlField').value)">
                    {{ trans('calendar.copy_url') }}
                </button>
                <a href="{{ $calendarIcalUrl }}" class="cs-icon-btn" style="display:inline-flex;align-items:center;text-decoration:none;">{{ trans('calendar.download_ics') }}</a>
            </div>
            <button type="button" class="cs-regenerate-link" onclick="calendarRegenerateIcal()">
                {{ trans('calendar.regenerate_url') }}
            </button>
        @endif


        <div class="form-section-separator"></div>


        {{-- ================= SYNC LOGS SECTION ================= --}}
        <div class="settings-section-title" style="margin-bottom:16px;">
            <h4 class="mb-0">{{ trans('calendar.sync_logs') }}</h4>
        </div>

        <div class="cs-logs-table-wrap">
            <table class="cs-logs-table">
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
                                    <span class="cs-status-pill cs-status-success">&#10003; {{ trans('calendar.success') }}</span>
                                @elseif($log->status === 'failed')
                                    <span class="cs-status-pill cs-status-failed">&#10007; {{ trans('calendar.failed') }}</span>
                                @else
                                    <span class="cs-status-pill cs-status-pending">{{ trans('calendar.pending') }}</span>
                                @endif
                            </td>
                            <td class="font-13" style="color:var(--cs-text-muted);">
                                {{ \Illuminate\Support\Str::limit($log->error_message ?? '-', 60) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="cs-empty">{{ trans('calendar.no_logs') }}</td>
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