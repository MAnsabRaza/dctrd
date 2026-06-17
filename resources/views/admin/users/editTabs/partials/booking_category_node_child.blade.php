@php
    $node = $node ?? [];
    $children = $node['children'] ?? [];
@endphp

<div class="booking-node" data-category-id="{{ $node['id'] }}">
    <div class="booking-node-row">
        <div class="booking-node-label booking-child-label">{{ $node['title'] }}</div>
        <label class="mb-0">
            <input type="checkbox" class="booking-category-checkbox d-none" {{ !empty($node['enabled']) ? 'checked' : '' }}>
            <span class="booking-toggle {{ !empty($node['enabled']) ? 'is-on' : '' }}"></span>
        </label>
    </div>

    @if(!empty($children))
        <div class="booking-children">
            @foreach($children as $child)
                @include('admin.users.editTabs.partials.booking_category_node_child', ['node' => $child])
            @endforeach
        </div>
    @endif
</div>
