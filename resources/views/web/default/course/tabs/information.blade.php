@php
    $learningMaterialsExtraDescription = !empty($course->webinarExtraDescription)
        ? $course->webinarExtraDescription->where('type', 'learning_materials')
        : null;
    $companyLogosExtraDescription = !empty($course->webinarExtraDescription)
        ? $course->webinarExtraDescription->where('type', 'company_logos')
        : null;
    $requirementsExtraDescription = !empty($course->webinarExtraDescription)
        ? $course->webinarExtraDescription->where('type', 'requirements')
        : null;
@endphp


{{-- Installments --}}
@if (
    !empty($installments) and
        count($installments) and
        getInstallmentsSettings('installment_plans_position') == 'top_of_page')
    @foreach ($installments as $installmentRow)
        @include('web.default.installment.card', [
            'installment' => $installmentRow,
            'itemPrice' => $course->getPrice(),
            'itemId' => $course->id,
            'itemType' => 'course',
        ])
    @endforeach
@endif

@if (!empty($learningMaterialsExtraDescription) and count($learningMaterialsExtraDescription))
    <div class="mt-20 rounded-sm border bg-info-light p-15">
        <h3 class="font-16 text-secondary font-weight-bold mb-15">{{ trans('update.what_you_will_learn') }}</h3>

        @foreach ($learningMaterialsExtraDescription as $learningMaterial)
            <p class="d-flex align-items-start font-14 text-gray mt-10">
                <i data-feather="check" width="18" height="18"
                    class="mr-10 webinar-extra-description-check-icon"></i>
                <span class="">{{ $learningMaterial->value }}</span>
            </p>
        @endforeach
    </div>
@endif

{{-- course description --}}

@php
    use App\Services\CrossSellingService;
    use App\Services\UpSellingService;

    $crossSellingService = new CrossSellingService();
    $upSellingService = new UpSellingService();

    $cross_selling = $crossSellingService->getCrossSellingItemsWithSettings(
        $course->teacher_id,
        'App\Models\Webinar',
        $course->id,
        'product',
    );

    $up_selling = $upSellingService->getUpsellItems($course->teacher_id, $course->id, 'course');
@endphp

{{-- Popup --}}
{{-- @isset($cross_selling['products'])
    @if ($cross_selling['display_on'] == 1 && count($cross_selling['products']))
        @include('web.default.cross_up_selling.popup', [
            'products' => $cross_selling['products'],
            'title' => $cross_selling['description'],
            'slider' => $cross_selling['slider'],
            'display' => $cross_selling['display_on'],
            'type' => 'course',
        ])
    @endif
@endisset --}}

@isset($cross_selling['products'])
    @if (isset($cross_selling['display_on']) && $cross_selling['display_on'] == 1 && count($cross_selling['products']))
        @include('web.default.cross_up_selling.popup', [
            'products' => $cross_selling['products'],
            'title' => $cross_selling['description'] ?? '',
            'slider' => $cross_selling['slider'] ?? false,
            'display' => $cross_selling['display_on'],
            'type' => 'blog',
        ])
    @endif
@endisset


{{-- Above Description Tab --}}
@isset($cross_selling['products'])
    @if (isset($cross_selling['display_on']) && $cross_selling['display_on'] == 3 && count($cross_selling['products']))
        <x-cross-selling :products="$cross_selling['products']" :title="$cross_selling['description'] ?? ''" :slider="$cross_selling['slider'] ?? false" :display="$cross_selling['display_on']" type="course" />
    @endif
@endisset


{{-- Description --}}
@isset($course->description)
    <div class="mt-20">
        <h2 class="section-title after-line">{{ trans('product.Webinar_description') }}</h2>
        <div class="mt-15 course-description">
            {!! nl2br($course->description) !!}
        </div>
    </div>
@endisset

{{-- Below Description --}}
@if (($cross_selling['display_on'] ?? null) == 4 && !empty($cross_selling['products']))
    <x-cross-selling :products="$cross_selling['products']" :title="$cross_selling['description'] ?? ''" :slider="$cross_selling['slider'] ?? false" :display="$cross_selling['display_on']" type="course" />
@endif


{{-- Below Add to Cart --}}
@if (isset($cross_selling['products'], $cross_selling['display_on']) &&
        $cross_selling['display_on'] == 2 &&
        count($cross_selling['products']))
    @push('below-add-to-cart')
        <x-cross-selling :products="$cross_selling['products']" :title="$cross_selling['description'] ?? ''" :slider="$cross_selling['slider'] ?? false" :display="$cross_selling['display_on']" type="course" />
    @endpush
@endif


{{-- Company Logos --}}
@isset($companyLogosExtraDescription)
    @if (count($companyLogosExtraDescription))
        <div class="mt-20 rounded-sm border bg-white p-15">
            <div class="mb-15">
                <h3 class="font-16 text-secondary font-weight-bold">{{ trans('update.suggested_by_top_companies') }}</h3>
                <p class="font-14 text-gray mt-5">{{ trans('update.suggested_by_top_companies_hint') }}</p>
            </div>
            <div class="row">
                @foreach ($companyLogosExtraDescription as $companyLogo)
                    <div class="col text-center">
                        <img src="{{ $companyLogo->value }}" class="webinar-extra-description-company-logos"
                            alt="{{ trans('update.company_logos') }}">
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endisset

{{-- Requirements Extra Description --}}
@isset($requirementsExtraDescription)
    @if (count($requirementsExtraDescription))
        <div class="mt-20">
            <h3 class="font-16 text-secondary font-weight-bold mb-15">{{ trans('update.requirements') }}</h3>
            @foreach ($requirementsExtraDescription as $requirementExtraDescription)
                <p class="d-flex align-items-start font-14 text-gray mt-10">
                    <i data-feather="check" width="18" height="18"
                        class="mr-10 webinar-extra-description-check-icon"></i>
                    <span class="">{{ $requirementExtraDescription->value }}</span>
                </p>
            @endforeach
        </div>
    @endif
@endisset


{{-- course prerequisites --}}
@if (!empty($course->prerequisites) and $course->prerequisites->count() > 0)

    <div class="mt-20">
        <h2 class="section-title after-line">{{ trans('public.prerequisites') }}</h2>

        @foreach ($course->prerequisites as $prerequisite)
            @if ($prerequisite->prerequisiteWebinar)
                @include('web.default.includes.webinar.list-card', [
                    'webinar' => $prerequisite->prerequisiteWebinar,
                ])
            @endif
        @endforeach
    </div>
@endif
{{-- ./ course prerequisites --}}


{{-- Related Course --}}
@if (!empty($course->relatedCourses) and $course->relatedCourses->count() > 0)

    <div class="mt-20">
        <h2 class="section-title after-line">{{ trans('update.related_courses') }}</h2>

        @foreach ($course->relatedCourses as $relatedCourse)
            @if ($relatedCourse->course)
                @include('web.default.includes.webinar.list-card', ['webinar' => $relatedCourse->course])
            @endif
        @endforeach
    </div>
@endif
{{-- ./ Related Course --}}

{{-- course FAQ --}}
@if (!empty($course->faqs) and $course->faqs->count() > 0)
    <div class="mt-20">
        <h2 class="section-title after-line">{{ trans('public.faq') }}</h2>

        <div class="accordion-content-wrapper mt-15" id="accordion" role="tablist" aria-multiselectable="true">
            @foreach ($course->faqs as $faq)
                <div class="accordion-row rounded-sm shadow-lg border mt-20 py-20 px-35">
                    <div class="font-weight-bold font-14 text-secondary" role="tab" id="faq_{{ $faq->id }}">
                        <div href="#collapseFaq{{ $faq->id }}" aria-controls="collapseFaq{{ $faq->id }}"
                            class="d-flex align-items-center justify-content-between" role="button"
                            data-toggle="collapse" data-parent="#accordion" aria-expanded="true">
                            <span>{{ clean($faq->title, 'title') }}</span>
                            <i class="collapse-chevron-icon" data-feather="chevron-down" width="25"
                                class="text-gray"></i>
                        </div>
                    </div>
                    <div id="collapseFaq{{ $faq->id }}" aria-labelledby="faq_{{ $faq->id }}"
                        class=" collapse" role="tabpanel">
                        <div class="panel-collapse text-gray">
                            {{ clean($faq->answer, 'answer') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
{{-- ./ course FAQ --}}

{{-- Installments --}}
@if (
    !empty($installments) and
        count($installments) and
        getInstallmentsSettings('installment_plans_position') == 'bottom_of_page')
    @foreach ($installments as $installmentRow)
        @include('web.default.installment.card', [
            'installment' => $installmentRow,
            'itemPrice' => $course->getPrice(),
            'itemId' => $course->id,
            'itemType' => 'course',
        ])
    @endforeach
@endif

{{-- course Comments --}}
@include('web.default.includes.comments', [
    'comments' => $course->comments,
    'inputName' => 'webinar_id',
    'inputValue' => $course->id,
])
{{-- ./ course Comments --}}

{{-- Up Selling --}}
<div>
    @php
        $settings = \App\Models\UserUpSelling::where('user_id', $course->teacher_id)->first();
    @endphp
    @if ($settings != null && !$settings->hide_on_single_product)
        <x-up-selling :products="$up_selling" :title="trans('update.Recommend Items')" :slider="true" :display="2" :type="'course'" />
    @endif
</div>
