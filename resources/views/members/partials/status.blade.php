@php
    $isMember = $member->isCurrentMember();
    $isWaiting = ! $isMember && in_array($member->status, [\App\Models\Member::STATUS_PENDING, \App\Models\Member::STATUS_ACTIVE], true);
@endphp
<span @class([
    'inline-block px-2 py-0.5 text-xs font-semibold rounded-full',
    'bg-green-100 text-green-800' => $isMember,
    'bg-yellow-100 text-yellow-800' => $isWaiting,
    'bg-gray-200 text-gray-700' => ! $isMember && ! $isWaiting,
])>{{ $member->statusLabel() }}</span>
