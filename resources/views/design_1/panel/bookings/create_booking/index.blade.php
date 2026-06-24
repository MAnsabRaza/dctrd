{{--
    resources/views/design_1/panel/bookings/create_booking/index.blade.php

    Page-reload step flow, same pattern as create_product/index.blade.php:
    - Step 1 posts to panel.bookings.store (no booking exists yet)
    - Steps 2-8 post to panel.bookings.update with current_step + get_step/get_next/draft
    - Every submit causes a normal redirect to the next (or same) step URL
--}}
@extends('design_1.panel.layouts.panel')

@section('content')

<style>
/* ============================================================
   Booking wizard — shared design tokens & components
   ============================================================ */
:root{
    --bk-primary:#2563eb;
    --bk-primary-light:#eef4ff;
    --bk-green:#16a34a;
    --bk-green-light:#e7f6ec;
    --bk-border:#e9ebef;
    --bk-muted:#98a2b3;
    --bk-text:#1f2430;
    --bk-bg:#f8f9fb;
}

/* ---------- step rail (top stepper) ---------- */
.step-rail-card{
    background:#fff;
    border-radius:12px;
    padding:18px 22px 4px;
    margin-bottom:16px;
}
.step-rail{
    display:flex;
    align-items:center;
    overflow-x:auto;
    padding-bottom:6px;
}
.step-rail-node{ display:flex; align-items:center; flex:1; min-width:46px; }
.step-rail-node:last-child{ flex:0 0 auto; }
.step-rail-circle{
    width:34px; height:34px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:13px; flex-shrink:0;
    border:2px solid var(--bk-border);
    background:#fbfbfc; color:var(--bk-muted);
    transition:all .15s ease;
}
.step-rail-line{ flex:1; height:2px; background:var(--bk-border); margin:0 4px; min-width:12px; }
.step-rail-node.is-active .step-rail-circle{
    background:var(--bk-primary); border-color:var(--bk-primary); color:#fff;
    width:38px; height:38px; box-shadow:0 0 0 4px var(--bk-primary-light);
}
.step-rail-node.is-done .step-rail-circle{ background:var(--bk-green); border-color:var(--bk-green); color:#fff; }
.step-rail-node.is-done .step-rail-line{ background:var(--bk-green); opacity:.4; }
.step-rail-caption{ padding:10px 4px 14px; }
.step-rail-eyebrow{ display:block; font-size:11px; text-transform:uppercase; letter-spacing:.06em; color:var(--bk-muted); font-weight:700; }
.step-rail-title{ font-size:15px; color:var(--bk-text); font-weight:700; }

/* ---------- main panel ---------- */
.step-panel{
    background:#fff;
    border-radius:12px;
    padding:28px;
    min-height:320px;
}
.step-footer{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-top:16px;
    background:#fff;
    border-radius:12px;
    padding:14px 22px;
}
.step-footer .btn-circle{
    width:38px; height:38px; border-radius:50%;
    display:inline-flex; align-items:center; justify-content:center;
    padding:0; border:1px solid var(--bk-border); color:#495057; background:#fff;
}
.step-footer .btn-circle:hover{ background:var(--bk-bg); }

/* ---------- reusable section / card components (used across steps) ---------- */
.panel-card{
    background:#fff; border:1px solid var(--bk-border); border-radius:12px;
    padding:20px 22px; margin-bottom:18px;
}
.section-head{ display:flex; align-items:center; gap:10px; margin-bottom:16px; }
.section-head .badge-icon{
    width:32px; height:32px; border-radius:9px; flex-shrink:0;
    background:var(--bk-primary-light); color:var(--bk-primary);
    display:flex; align-items:center; justify-content:center; font-size:14px;
}
.section-head h6{ margin:0; font-weight:700; font-size:15px; color:var(--bk-text); }
.section-head .section-sub{ margin:0; font-size:12px; color:var(--bk-muted); }
.section-head-actions{ margin-left:auto; }

.field-hint{
    display:inline-flex; align-items:center; justify-content:center;
    width:16px; height:16px; border-radius:50%; background:#eef0f3; color:var(--bk-muted);
    font-size:10px; margin-left:6px; cursor:help; vertical-align:middle;
}

/* toggle switch — used on steps 3, 7 & 8 */
.booking-switch-row{ display:flex; align-items:center; gap:12px; padding:10px 0; }
.booking-switch{ position:relative; display:inline-block; width:44px; height:24px; flex-shrink:0; }
.booking-switch input{ opacity:0; width:0; height:0; position:absolute; }
.booking-switch-slider{ position:absolute; inset:0; background:#d8dce2; border-radius:24px; cursor:pointer; transition:background .2s; }
.booking-switch-slider:before{ content:''; position:absolute; height:18px; width:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:transform .2s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
.booking-switch input:checked + .booking-switch-slider{ background:var(--bk-primary); }
.booking-switch input:checked + .booking-switch-slider:before{ transform:translateX(20px); }
.booking-switch-label{ font-size:14px; color:#344054; font-weight:600; cursor:pointer; user-select:none; margin-bottom:0; }
.booking-switch-label small{ display:block; font-size:12px; color:var(--bk-muted); font-weight:400; }
.booking-switch-row-bordered{ border:1px solid var(--bk-border); border-radius:10px; padding:12px 16px; margin-bottom:10px; }

/* pill-style choice cards — booking type, amenities, filters */
.pill-check{ position:relative; display:block; }
.pill-check input{ position:absolute; opacity:0; width:0; height:0; }
.pill-check .pill-box{
    display:block; border:1.5px solid var(--bk-border); border-radius:10px;
    padding:14px 6px; text-align:center; cursor:pointer; transition:.15s;
    font-size:13px; font-weight:600; color:#495057; background:#fff;
}
.pill-check input:checked + .pill-box{ border-color:var(--bk-primary); background:var(--bk-primary-light); color:var(--bk-primary); }
.pill-check input:focus-visible + .pill-box{ outline:2px solid var(--bk-primary); outline-offset:1px; }

.chip-check{ display:inline-flex; align-items:center; }
.chip-check input{ position:absolute; opacity:0; width:0; height:0; }
.chip-check .chip-box{
    display:inline-block; border:1.5px solid var(--bk-border); border-radius:20px;
    padding:6px 14px; font-size:12.5px; font-weight:600; color:#495057; cursor:pointer; transition:.15s;
}
.chip-check input:checked + .chip-box{ border-color:var(--bk-primary); background:var(--bk-primary-light); color:var(--bk-primary); }

/* tables — rate plans, resources, assets, time slots */
.mini-table{ width:100%; border-collapse:separate; border-spacing:0; margin-bottom:0; }
.mini-table thead th{
    background:var(--bk-bg); color:var(--bk-muted); font-size:11px; text-transform:uppercase;
    letter-spacing:.04em; font-weight:700; padding:10px 12px; border-bottom:1px solid var(--bk-border);
    white-space:nowrap;
}
.mini-table thead th:first-child{ border-radius:8px 0 0 0; }
.mini-table thead th:last-child{ border-radius:0 8px 0 0; }
.mini-table tbody td{ padding:9px 12px; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
.mini-table tbody tr:last-child td{ border-bottom:none; }
.mini-table-wrap{ border:1px solid var(--bk-border); border-radius:8px; overflow:hidden; margin-bottom:12px; }

/* empty states */
.empty-state{ text-align:center; color:var(--bk-muted); padding:30px 10px; }
.empty-state .badge-icon{
    width:46px; height:46px; border-radius:12px; background:var(--bk-primary-light); color:var(--bk-primary);
    display:flex; align-items:center; justify-content:center; font-size:18px; margin:0 auto 10px;
}
.empty-state .empty-title{ color:#495057; font-weight:600; font-size:13px; margin-bottom:2px; }
.empty-state .empty-sub{ font-size:12px; }

.booking-map-preview{ width:100%; height:220px; border:1px solid var(--bk-border); border-radius:12px; overflow:hidden; background:#f7f8fa; }
.booking-map-preview iframe{ width:100%; height:100%; border:0; }
</style>

@php
    $isEditing = isset($booking) && !is_null($booking);
    $stepLabels = [
        1 => 'General Info',
        2 => 'Pricing & Availability',
        3 => 'Participants & Resources',
        4 => 'Content',
        5 => 'Prerequisites & Related',
        6 => 'FAQ',
        7 => 'Location & Filters',
        8 => 'Review & Submit',
    ];
    $stepIcons = [
        1 => 'fa-info-circle',
        2 => 'fa-tags',
        3 => 'fa-users',
        4 => 'fa-align-left',
        5 => 'fa-list',
        6 => 'fa-question-circle',
        7 => 'fa-map-marker',
        8 => 'fa-paper-plane',
    ];
@endphp

<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="step-rail-card">
        <div class="step-rail">
            @foreach($stepLabels as $num => $label)
                @php
                    $state = $num == $currentStep ? 'is-active' : ($isEditing && $num < $currentStep ? 'is-done' : '');
                @endphp
                <div class="step-rail-node {{ $state }}">
                    <div class="step-rail-circle">
                        @if($state === 'is-done')
                            <i class="fa fa-check"></i>
                        @else
                            <i class="fa {{ $stepIcons[$num] }}"></i>
                        @endif
                    </div>
                    @if($num < $stepCount)
                        <div class="step-rail-line"></div>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="step-rail-caption">
            <span class="step-rail-eyebrow">Step {{ $currentStep }} of {{ $stepCount }}</span>
            <span class="step-rail-title">{{ $stepLabels[$currentStep] }}</span>
        </div>
    </div>

    {{--
        Step 1 with no booking yet -> POST to store()
        Any other step (or step 1 while editing) -> POST to update()
    --}}
    @if(!$isEditing)
        <form method="POST" action="{{ route('panel.bookings.store') }}" enctype="multipart/form-data" id="bookingStepForm">
            @csrf
    @else
        <form method="POST" action="{{ route('panel.bookings.update', $booking->id) }}" enctype="multipart/form-data" id="bookingStepForm">
            @csrf
            <input type="hidden" name="current_step" value="{{ $currentStep }}">
    @endif

        <div class="step-panel">
            @include('design_1.panel.bookings.create_booking.steps.step' . $currentStep)
        </div>

        <div class="step-footer">
            <div>
                @if($currentStep > 1)
                    <a href="{{ route('panel.bookings.edit', ['id' => $booking->id, 'step' => $currentStep - 1]) }}" class="btn-circle" title="Back">
                        <i class="fa fa-arrow-left"></i>
                    </a>
                @endif
            </div>
            <div class="d-flex align-items-center">
                @if($isEditing)
                    <button type="submit" name="draft" value="1" class="btn btn-light mr-2">Save as Draft</button>
                @endif

                @if($currentStep < $stepCount)
                    <button type="submit" name="get_next" value="1" class="btn btn-primary">
                        Next <i class="fa fa-arrow-right ml-1"></i>
                    </button>
                @else
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-paper-plane mr-1"></i> Submit for Review
                    </button>
                @endif
            </div>
        </div>
    </form>
</div>

@endsection