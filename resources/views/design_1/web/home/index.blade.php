@extends('landingBuilder.front.landing.index', [
    'landingItem' => $homeLanding
])

@section('content')
    @if(!empty($landingItem))
        @foreach($landingItem->components as $component)
            @includeIf("landingBuilder.front.components.{$component->landingBuilderComponent->name}.index", ['landingComponent' => $component])
        @endforeach
    @else
        <section class="container py-24 text-center">
            <h1 class="font-26 font-weight-bold">Homepage content is not configured</h1>
            <p class="mt-10 text-muted">Please configure a home landing or activate a theme in the admin panel.</p>
        </section>
    @endif
@endsection

