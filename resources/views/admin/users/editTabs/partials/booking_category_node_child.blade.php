{{--
    resources/views/admin/users/editTabs/partials/booking_category_node_child.blade.php
    Child row — recursive for grand-children.
--}}
@php
    $node          = $node          ?? [];
    $children      = $node['children'] ?? [];
    $parentEnabled = $parentEnabled ?? true;
    $enabled       = !empty($node['enabled']);
    $isLocked      = !$parentEnabled;          // parent OFF → this is locked
    $isOn          = $enabled && !$isLocked;
@endphp

<div class="booking-node" data-category-id="{{ $node['id'] }}">

    <div class="booking-node-row">
        <div class="booking-node-label">{{ $node['title'] }}</div>

        <label class="mb-0" style="cursor:{{ $isLocked ? 'not-allowed' : 'pointer' }}; line-height:1">
            <input type="checkbox"
                   class="booking-category-checkbox d-none"
                   data-locked="{{ $isLocked ? '1' : '0' }}"
                   {{ $isOn ? 'checked' : '' }}>
            <span class="booking-toggle
                {{ $isOn     ? 'is-on'       : '' }}
                {{ $isLocked ? 'is-disabled' : '' }}">
            </span>
        </label>
    </div>

    {{-- Grand-children (3rd level) --}}
    @if(!empty($children))
        <div class="booking-children">
            @foreach($children as $grandchild)
                @include('admin.users.editTabs.partials.booking_category_node_child', [
                    'node'          => $grandchild,
                    'parentEnabled' => $isOn,
                ])
            @endforeach
        </div>
    @endif

</div>