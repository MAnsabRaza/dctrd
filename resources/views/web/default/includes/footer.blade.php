@php
    $socials = getSocials();

    if (!empty($socials) and count($socials)) {
        $socials = collect($socials)->sortBy('order')->toArray();
    }

    $footerColumns = getFooterColumns();

    /*
     * Detect public user profile pages:
     * /users/light-moon/profile
     * /users/james-kong/profile
     */
    $isUserProfile = request()->is('users/*/profile');
@endphp

<footer class="footer bg-secondary position-relative user-select-none">

    <div class="container footer-containers" @if($isUserProfile) style="display: none;" @endif>
        <div class="row">
            <div class="col-12">
                <div class="footer-subscribe d-block d-md-flex align-items-center justify-content-between">
                    <div class="flex-grow-1">
                        <strong>{{ trans('footer.join_us_today') }}</strong>
                        <span class="d-block mt-5 text-white">{{ trans('footer.subscribe_content') }}</span>
                    </div>

                    <div class="subscribe-input bg-white p-10 flex-grow-1 mt-30 mt-md-0">
                        <form action="/newsletters" method="post">
                            {{ csrf_field() }}

                            <div class="form-group d-flex align-items-center m-0">
                                <div class="w-100">
                                    <input type="text"
                                           name="newsletter_email"
                                           class="form-control border-0 @error('newsletter_email') is-invalid @enderror"
                                           placeholder="{{ trans('footer.enter_email_here') }}"/>

                                    @error('newsletter_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-primary rounded-pill">
                                    {{ trans('footer.join') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    @php
        $columns = ['first_column', 'second_column', 'third_column', 'forth_column'];
    @endphp

    <div class="container footer-containers" @if($isUserProfile) style="display: none;" @endif>
        <div class="row">

            @foreach($columns as $column)
                <div class="col-6 col-md-3">
                    @if(!empty($footerColumns[$column]))
                        @if(!empty($footerColumns[$column]['title']))
                            <span class="header d-block text-white font-weight-bold">
                                {{ $footerColumns[$column]['title'] }}
                            </span>
                        @endif

                        @if(!empty($footerColumns[$column]['value']))
                            <div class="mt-20">
                                {!! $footerColumns[$column]['value'] !!}
                            </div>
                        @endif
                    @endif
                </div>
            @endforeach

        </div>

        <div class="mt-40 border-blue py-25 d-flex align-items-center justify-content-between">
            <div class="footer-logo">
                <a href="/">
                    @if(!empty($generalSettings['footer_logo']))
                        <img src="{{ $generalSettings['footer_logo'] }}" class="img-cover" alt="footer logo">
                    @endif
                </a>
            </div>

            <div class="footer-social">
                @if(!empty($socials) and count($socials))
                    @foreach($socials as $social)
                        <a href="{{ $social['link'] }}" target="_blank">
                            <img src="{{ $social['image'] }}" alt="{{ $social['title'] }}" class="mr-15">
                        </a>
                    @endforeach
                @endif
            </div>
        </div>
    </div>


    @if(getOthersPersonalizationSettings('platform_phone_and_email_position') == 'footer')
        <div class="footer-copyright-card">
            <div class="container d-flex align-items-center justify-content-between py-15">
                <div class="font-14 text-white">
                    {{ trans('update.platform_copyright_hint') }}
                </div>

                <div class="d-flex align-items-center justify-content-center">
                    @if(!empty($generalSettings['site_phone']))
                        <div class="d-flex align-items-center text-white font-14">
                            <i data-feather="phone" width="20" height="20" class="mr-10"></i>
                            {{ $generalSettings['site_phone'] }}
                        </div>
                    @endif

                    @if(!empty($generalSettings['site_email']))
                        <div class="border-left mx-5 mx-lg-15 h-100"></div>

                        <div class="d-flex align-items-center text-white font-14">
                            <i data-feather="mail" width="20" height="20" class="mr-10"></i>
                            {{ $generalSettings['site_email'] }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

</footer>


@if($isUserProfile)
    <button id="toggleContainers"
            type="button"
            title="Show / hide footer"
            style="
                position: fixed;
                bottom: 25px;
                right: 25px;
                z-index: 999999;
                width: 46px;
                height: 46px;
                border-radius: 50%;
                border: none;
                background-color: #3cade5;
                color: white;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            ">
        &#128065;
    </button>
@endif


<script>
    document.addEventListener("DOMContentLoaded", function() {
        const toggleButton = document.getElementById("toggleContainers");
        const footerContainers = document.querySelectorAll(".footer-containers");

        if (toggleButton && footerContainers.length) {
            toggleButton.addEventListener("click", function() {
                footerContainers.forEach(function(container) {
                    if (container.style.display === "none") {
                        container.style.display = "block";
                    } else {
                        container.style.display = "none";
                    }
                });
            });
        }
    });
</script>