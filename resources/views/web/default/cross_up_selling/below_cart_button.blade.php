<section class="home-sections home-sections-swiper container">
    <div class="px-20 px-md-0">
        <h2 class="section-title">{{ $cross_sellings['settings']->description }}</h2>
    </div>

    <div class="feature-slider-container position-relative d-flex justify-content-center mt-10">
        <div class="swiper-container features-swiper-container pb-25">
            <div class="swiper-wrapper py-10">
                @foreach ($cross_sellings['data'] as $webinar)
                    @php
                        $targetModel = app()->make($item['target_type']);
                        $models = $targetModel::whereIn('id', [$item['target_id']])->first();
                    @endphp
                    <div class="swiper-slide">
                        <a href="{{ $models->getUrl() }}">
                            <div class="feature-slider d-flex h-100"
                                style="background-image: url('{{ $models->getImage() }}')">
                                <div class="mask"></div>
                                <div class="p-5 p-md-25 feature-slider-card">
                                    <div class="d-flex flex-column feature-slider-body position-relative h-100">
                                        @if ($models->bestTicket() < $models->price)
                                            <span
                                                class="badge badge-danger mb-2 ">{{ trans('public.offer', ['off' => $models->bestTicket(true)['percent']]) }}</span>
                                        @endif
                                        <a href="{{ $models->getUrl() }}">
                                            <h3 class="card-title mt-1">{{ $models->title }}</h3>
                                        </a>

                                        <div class="user-inline-avatar mt-15 d-flex align-items-center">
                                            <div class="avatar bg-gray200">
                                                <img src="{{ $models->teacher->getAvatar() }}"
                                                    class="img-cover" alt="{{ $models->teacher->full_naem }}">
                                            </div>
                                            <a href="{{ $models->teacher->getProfileUrl() }}" target="_blank"
                                                class="user-name font-14 ml-5">{{ $models->teacher->full_name }}</a>
                                        </div>

                                        <p class="mt-25 feature-desc text-gray">{{ $models->description }}</p>

                                        @include('web.default.includes.webinar.rate', [
                                            'rate' => $models->getRate(),
                                        ])

                                        <div
                                            class="feature-footer mt-auto d-flex align-items-center justify-content-between">
                                            <div class="d-flex justify-content-between">
                                                <div class="d-flex align-items-center">
                                                    <i data-feather="clock" width="20" height="20"
                                                        class="webinar-icon"></i>
                                                    <span
                                                        class="duration ml-5 text-dark-blue font-14">{{ convertMinutesToHourAndMinute($models->duration) }}
                                                        {{ trans('home.hours') }}</span>
                                                </div>

                                                <div class="vertical-line mx-10"></div>

                                                <div class="d-flex align-items-center">
                                                    <i data-feather="calendar" width="20" height="20"
                                                        class="webinar-icon"></i>
                                                    <span
                                                        class="date-published ml-5 text-dark-blue font-14">{{ dateTimeFormat(!empty($models->start_date) ? $models->start_date : $models->created_at, 'j M Y') }}</span>
                                                </div>
                                            </div>

                                            <div class="feature-price-box">
                                                @if (!empty($models->price) and $models->price > 0)
                                                    @if ($models->bestTicket() < $models->price)
                                                        <span
                                                            class="real">{{ handlePrice($models->bestTicket(), true, true, false, null, true) }}</span>
                                                    @else
                                                        {{ handlePrice($models->price, true, true, false, null, true) }}
                                                    @endif
                                                @else
                                                    {{ trans('public.free') }}
                                                @endif


                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="swiper-pagination features-swiper-pagination"></div>
    </div>
</section>
