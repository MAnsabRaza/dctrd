{{-- <section>
    <div class="panel-section">
        <h2>{{ trans('public.backend_iframe_connection') }}</h2>
        @if(!empty($frontendLink) && filter_var($frontendLink, FILTER_VALIDATE_URL))
            <iframe src="{{ $frontendLink }}" width="100%" height="600px" style="border: none;"></iframe>
        @else
            <p>Embedding not supported.
                @if(!empty($frontendLink))
                    <a href="{{ $frontendLink }}" target="_blank">Click here</a> to open it in a new tab.
                @endif
            </p>
        @endif
    </div>
</section>
 --}}


 <div class="user-profile-wrapper">
    <section class="container" style="margin-bottom: 300px" >
        <div class="rounded-lg shadow-sm px-25 py-20 px-lg-50 py-lg-35 user-profile-info bg-white">
            <div class="profile-info-box d-flex align-items-start justify-content-between">
                <div class="user-details d-flex align-items-center">
                    <div class="user-profile-avatar bg-gray200">
                        <img src="{{ $user->getAvatar(190) }}" class="img-cover" alt="{{ $user['full_name'] }}"/>
                        @if($user->offline)
                            <span class="user-circle-badge unavailable d-flex align-items-center justify-content-center">
                                <i data-feather="slash" width="20" height="20" class="text-white"></i>
                            </span>
                        @elseif($user->verified)
                            <span class="user-circle-badge has-verified d-flex align-items-center justify-content-center">
                                <i data-feather="check" width="20" height="20" class="text-white"></i>
                            </span>
                        @endif
                    </div>
                    <div class="ml-20 ml-lg-40">
                        <h1 class="font-24 font-weight-bold text-dark-blue">{{ $user['full_name'] }}</h1>
                        <span class="text-gray">{{ $user['headline'] }}</span>
                        <div class="stars-card d-flex align-items-center mt-5">
                            @include('web.default.includes.webinar.rate',['rate' => $userRates])
                        </div>
                        <div class="w-100 mt-10 d-flex align-items-center justify-content-center justify-content-lg-start">
                            <div class="d-flex flex-column followers-status">
                                <span class="font-20 font-weight-bold text-dark-blue">{{ $userFollowers->count() }}</span>
                                <span class="font-14 text-gray">{{ trans('panel.followers') }}</span>
                            </div>
                            <div class="d-flex flex-column ml-25 pl-5 following-status">
                                <span class="font-20 font-weight-bold text-dark-blue">{{ $userFollowing->count() }}</span>
                                <span class="font-14 text-gray">{{ trans('panel.following') }}</span>
                            </div>
                        </div>
                        <div class="user-reward-badges d-flex flex-wrap align-items-center mt-15">
                            @if(!empty($userBadges))
                                @foreach($userBadges as $userBadge)
                                    <div class="mr-15" data-toggle="tooltip" data-placement="bottom" data-html="true" title="{!! (!empty($userBadge->badge_id) ? nl2br($userBadge->badge->description) : nl2br($userBadge->description)) !!}">
                                        <img src="{{ !empty($userBadge->badge_id) ? $userBadge->badge->image : $userBadge->image }}" width="32" height="32" alt="{{ !empty($userBadge->badge_id) ? $userBadge->badge->title : $userBadge->title }}">
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                <div class="user-actions d-flex flex-column">
                    <button type="button" id="followToggle" data-user-id="{{ $user['id'] }}" class="btn btn-{{ (!empty($authUserIsFollower) and $authUserIsFollower) ? 'danger' : 'primary' }} btn-sm">
                        @if(!empty($authUserIsFollower) and $authUserIsFollower)
                            {{ trans('panel.unfollow') }}
                        @else
                            {{ trans('panel.follow') }}
                        @endif
                    </button>
                    @if($user->public_message)
                        <button type="button" class="js-send-message btn btn-border-white rounded btn-sm mt-15">{{ trans('site.send_message') }}</button>
                    @endif
                </div>
            </div>
            <div class="mt-40 border-top"></div>
            <div class="row mt-30 w-100 d-flex align-items-center justify-content-around">
                <div class="col-6 col-md-3 user-profile-state d-flex flex-column align-items-center">
                    <div class="state-icon orange p-15 rounded-lg">
                        <img src="/assets/default/img/profile/students.svg" alt="">
                    </div>
                    <span class="font-20 text-dark-blue font-weight-bold mt-5">{{ $user->students_count }}</span>
                    <span class="font-14 text-gray">{{ trans('quiz.students') }}</span>
                </div>
                <div class="col-6 col-md-3 user-profile-state d-flex flex-column align-items-center">
                    <div class="state-icon blue p-15 rounded-lg">
                        <img src="/assets/default/img/profile/webinars.svg" alt="">
                    </div>
                    <span class="font-20 text-dark-blue font-weight-bold mt-5">{{ count($webinars) }}</span>
                    <span class="font-14 text-gray">{{ trans('webinars.classes') }}</span>
                </div>
                <div class="col-6 col-md-3 mt-20 mt-md-0 user-profile-state d-flex flex-column align-items-center">
                    <div class="state-icon green p-15 rounded-lg">
                        <img src="/assets/default/img/profile/reviews.svg" alt="">
                    </div>
                    <span class="font-20 text-dark-blue font-weight-bold mt-5">{{ $user->reviewsCount() }}</span>
                    <span class="font-14 text-gray">{{ trans('product.reviews') }}</span>
                </div>
                <div class="col-6 col-md-3 mt-20 mt-md-0 user-profile-state d-flex flex-column align-items-center">
                    <div class="state-icon royalblue p-15 rounded-lg">
                        <img src="/assets/default/img/profile/appointments.svg" alt="">
                    </div>
                    <span class="font-20 text-dark-blue font-weight-bold mt-5">{{ $appointments }}</span>
                    <span class="font-14 text-gray">{{ trans('site.appointments') }}</span>
                </div>
            </div>
        </div>
    </section>

    <section>
        @if($user->offline)
        <div class="user-offline-alert d-flex mt-40">
            <div class="p-15">
                <h3 class="font-16 text-dark-blue">{{ trans('public.instructor_is_not_available') }}</h3>
                <p class="font-14 font-weight-500 text-gray mt-15">{{ $user->offline_message }}</p>
            </div>

            <div class="offline-icon offline-icon-right ml-auto d-flex align-items-stretch">
                <div class="d-flex align-items-center">
                    <img src="/assets/default/img/profile/time-icon.png" alt="offline">
                </div>
            </div>
        </div>
    @endif

    @if((!empty($educations) and !$educations->isEmpty()) or (!empty($experiences) and !$experiences->isEmpty()) or (!empty($occupations) and !$occupations->isEmpty()) or !empty($user->about))
        @if(!empty($educations) and !$educations->isEmpty())
            <div class="mt-40">
                <h3 class="font-16 text-dark-blue font-weight-bold">{{ trans('site.education') }}</h3>

                <ul class="list-group-custom">
                    @foreach($educations as $education)
                        <li class="mt-15 text-gray">{{ $education->value }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(!empty($experiences) and !$experiences->isEmpty())
            <div class="mt-40">
                <h3 class="font-16 text-dark-blue font-weight-bold">{{ trans('site.experiences') }}</h3>

                <ul class="list-group-custom">
                    @foreach($experiences as $experience)
                        <li class="mt-15 text-gray">{{ $experience->value }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(!empty($user->about))
            <div class="mt-40">
                <h3 class="font-16 text-dark-blue font-weight-bold">{{ trans('site.about') }}</h3>

                <div class="mt-30">
                    {!! nl2br($user->about) !!}
                </div>
            </div>
        @endif

        @if(!empty($occupations) and !$occupations->isEmpty())
            <div class="mt-40">
                <h3 class="font-16 text-dark-blue font-weight-bold">{{ trans('site.occupations') }}</h3>

                <div class="d-flex flex-wrap align-items-center pt-10">
                    @foreach($occupations as $occupation)
                        <div class="bg-gray200 font-14 rounded mt-10 px-10 py-5 text-gray mr-15">{{ $occupation->category->title }}</div>
                    @endforeach
                </div>
            </div>
        @endif

    @else

        @include(getTemplate() . '.includes.no-result',[
            'file_name' => 'bio.png',
            'title' => trans('site.not_create_bio'),
            'hint' => '',
        ])

    @endif
    </section>
</div>

