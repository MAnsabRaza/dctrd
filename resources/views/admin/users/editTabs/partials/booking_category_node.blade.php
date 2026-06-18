{{--
    resources/views/admin/users/editTabs/partials/booking_category_node.blade.php
    Root / parent card.
--}}
@php
    $node     = $node ?? [];
    $children = $node['children'] ?? [];
    $enabled  = !empty($node['enabled']);
@endphp

<div class="booking-group booking-node" data-category-id="{{ $node['id'] }}">

    {{-- Parent header --}}
    <div class="booking-group-head">
        <div class="booking-group-title">{{ $node['title'] }}</div>

        <label class="mb-0" style="cursor:pointer; line-height:1">
            <input type="checkbox"
                   class="booking-category-checkbox d-none"
                   data-locked="0"
                   {{ $enabled ? 'checked' : '' }}>
            <span class="booking-toggle {{ $enabled ? 'is-on' : '' }}"></span>
        </label>
    </div>

    {{-- Children list --}}
    @if(!empty($children))
        <div class="booking-tree">
            @foreach($children as $child)
                @include('admin.users.editTabs.partials.booking_category_node_child', [
                    'node'          => $child,
                    'parentEnabled' => $enabled,
                ])
            @endforeach
        </div>
    @endif

</div>