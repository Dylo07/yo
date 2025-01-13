<span class="badge badge-{{ $status == 'present' ? 'success' : ($status == 'half' ? 'warning' : 'danger') }}">
    {{ ucfirst($status) }}
</span>