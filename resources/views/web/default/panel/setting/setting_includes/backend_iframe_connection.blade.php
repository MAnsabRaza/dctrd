<section>
    <div class="panel-section">
        <h2>{{ trans('public.backend_iframe_connection') }}</h2>
        @if(!empty($backendLink))
            <iframe src="{{ $backendLink }}" width="100%"
                height="{{ !empty($user) && !empty($user->back_iframe_height) ? $user->back_iframe_height : '600' }}px"
                style="border: none;"></iframe>
        @else
            <p>Embedding not supported. <a href="{{ $backendLink }}" target="_blank">Click here</a> to open it in a new tab.</p>
        @endif
    </div>
</section>



