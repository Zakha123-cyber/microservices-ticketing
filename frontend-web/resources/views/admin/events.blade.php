@extends('layouts.admin')

@section('content')
<section class="hero flex-between">
    <div>
        <p class="eyebrow">Admin</p>
        <h1 style="margin-bottom:4px;">Manajemen Event</h1>
        <p class="muted" style="margin:0;">Kelola event yang kamu buat dan lihat jumlah tiket terjual.</p>
    </div>
    <a class="btn" href="{{ route('events.create') }}">+ Create Event</a>
</section>

@if(empty($events))
    <div class="panel" style="padding:48px; text-align:center;">
        <p style="color:var(--text-muted);">Belum ada event. Yuk buat event pertama!</p>
        <a class="btn" href="{{ route('events.create') }}" style="margin-top:16px;">+ Create Event</a>
    </div>
@else
    <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(300px, 1fr)); gap:16px;">
        @foreach($events as $event)
            <div class="panel" style="padding:0; overflow:hidden; display:flex; flex-direction:column;">
                @if(!empty($event['image_url']))
                    <div style="height:160px; overflow:hidden;">
                        <img src="{{ $event['image_url'] }}" style="width:100%; height:100%; object-fit:cover;">
                    </div>
                @else
                    <div style="height:160px; background:var(--bg-card-hover); display:flex; align-items:center; justify-content:center;">
                        <span style="color:var(--text-muted); font-size:13px;">No Image</span>
                    </div>
                @endif
                <div style="padding:20px; flex:1; display:flex; flex-direction:column;">
                    <p style="margin:0 0 4px; font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:700;">
                        {{ $event['category_name'] ?? 'General' }}
                    </p>
                    <h3 style="margin:0 0 8px; font-size:18px; font-weight:700; color:#fff;">{{ $event['title'] }}</h3>
                    <p style="margin:0 0 16px; font-size:13px; color:var(--text-muted); line-height:1.5;">{{ Str::limit($event['description'] ?? 'No description', 100) }}</p>

                    <div style="margin-top:auto;">
                        <div style="display:flex; justify-content:space-between; padding:12px 0; border-top:1px solid var(--line); font-size:13px;">
                            <span style="color:var(--text-muted);">Harga</span>
                            <span style="color:#fff; font-weight:700;">Rp {{ number_format($event['price'], 0, ',', '.') }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; padding:8px 0; border-top:1px solid var(--line); font-size:13px;">
                            <span style="color:var(--text-muted);">Kuota</span>
                            <span style="color:#fff;">{{ $event['available_tickets'] }}/{{ $event['quota'] }}</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; padding:8px 0; border-top:1px solid var(--line); font-size:13px;">
                            <span style="color:var(--text-muted);">Tiket Terjual</span>
                            <span style="color:var(--brand); font-weight:700;">{{ $event['paid_bookings'] }} tiket</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; padding:8px 0 0; border-top:1px solid var(--line); font-size:13px;">
                            <span style="color:var(--text-muted);">Total Booking</span>
                            <span style="color:#fff;">{{ $event['total_bookings'] }} transaksi</span>
                        </div>
                    </div>

                    <div style="display:flex; gap:8px; margin-top:16px;">
                        <a class="btn btn-muted" href="{{ route('events.show', $event['id']) }}" style="flex:1; text-align:center;">View</a>
                        <a class="btn btn-muted" href="{{ route('events.edit', $event['id']) }}" style="flex:1; text-align:center;">Edit</a>
                        <form method="POST" action="{{ route('events.destroy', $event['id']) }}" style="flex:1;" class="delete-form">
                            @csrf
                            @method('DELETE')
                            <button class="btn delete-btn" style="width:100%; background:var(--text-negative); color:#fff;" type="button">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection

@push('scripts')
<script>
document.querySelectorAll('.delete-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        var form = this.closest('.delete-form');
        Swal.fire({
            title: 'Hapus event?',
            text: 'Data tidak bisa dikembalikan setelah dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e91429',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            background: '#181818',
            color: '#fff'
        }).then(function(result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endpush
