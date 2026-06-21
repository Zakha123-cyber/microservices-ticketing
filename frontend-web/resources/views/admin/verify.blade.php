@extends('layouts.admin')

@section('content')
<section class="hero">
    <p class="eyebrow">Admin</p>
    <h1 style="margin-bottom:4px;">Verifikasi Tiket</h1>
    <p class="muted" style="margin:0;">Scan QR code tiket untuk verifikasi kehadiran.</p>
</section>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:32px;">
    {{-- QR Scanner --}}
    <div class="panel" style="padding:24px;">
        <h3 style="font-size:16px; font-weight:700; color:#fff; margin:0 0 16px;">Scan QR Code</h3>
        <div id="scanner-container" style="width:100%; aspect-ratio:1; background:#000; border-radius:8px; overflow:hidden; position:relative;">
            <div id="reader" style="width:100%; height:100%;"></div>
        </div>
        <p id="scan-status" style="margin:12px 0 0; font-size:13px; color:var(--text-muted); text-align:center;">Kamera siap digunakan</p>
        <button id="btn-toggle-camera" class="btn btn-muted" style="width:100%; margin-top:8px;" onclick="toggleCamera()">Matikan Kamera</button>
        <hr style="border:none; border-top:1px solid var(--line); margin:16px 0;">
        <p style="font-size:12px; color:var(--text-muted); font-weight:700; text-transform:uppercase; letter-spacing:1px; margin:0 0 8px;">Upload Gambar QR</p>
        <input id="qr-upload" type="file" accept="image/*" style="width:100%; margin-bottom:8px;">
        <button id="btn-upload" class="btn btn-muted" style="width:100%;" onclick="uploadQR()">Scan dari Gambar</button>
        <p id="upload-status" style="margin:8px 0 0; font-size:12px; color:var(--text-muted); text-align:center;"></p>
    </div>

    {{-- Manual Input --}}
    <div class="panel" style="padding:24px;">
        <h3 style="font-size:16px; font-weight:700; color:#fff; margin:0 0 16px;">Input Manual</h3>
        <form id="verify-form" method="POST" action="{{ route('admin.verify.post') }}">
            @csrf
            <input name="booking_code" type="text" placeholder="Masukkan kode booking" value="{{ old('booking_code', request('booking_code')) }}" style="width:100%; margin-bottom:12px;" required>
            <button type="submit" class="btn" style="width:100%;">Verifikasi</button>
        </form>

        {{-- Result --}}
        @if($result && $booking)
            <hr style="border:none; border-top:1px solid var(--line); margin:20px 0;">
            @if($result['success'] ?? false)
                <div style="background:rgba(30,215,96,0.1); border:1px solid var(--brand); border-radius:8px; padding:16px; text-align:center;">
                    <svg viewBox="0 0 24 24" width="40" height="40" fill="var(--brand)" style="margin-bottom:8px;"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/></svg>
                    <h4 style="color:var(--brand); margin:0 0 4px; font-size:16px;">Tiket Terverifikasi!</h4>
                    <p style="color:var(--text-muted); font-size:13px; margin:0;">Tiket telah berhasil diverifikasi dan ditandai sebagai sudah digunakan.</p>
                </div>
            @else
                <div style="background:rgba(243,114,127,0.1); border:1px solid var(--text-negative); border-radius:8px; padding:16px; text-align:center;">
                    <svg viewBox="0 0 24 24" width="40" height="40" fill="var(--text-negative)" style="margin-bottom:8px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/></svg>
                    <h4 style="color:var(--text-negative); margin:0 0 4px; font-size:16px;">Verifikasi Gagal</h4>
                    <p style="color:var(--text-muted); font-size:13px; margin:0;">{{ $result['message'] ?? 'Tidak dapat memverifikasi tiket.' }}</p>
                </div>
            @endif

            {{-- Booking Detail --}}
            <div style="margin-top:16px; padding:16px; background:var(--bg-card-hover); border-radius:8px;">
                <p style="margin:0 0 8px; font-size:11px; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:700;">Detail Tiket</p>
                <div style="display:grid; gap:6px; font-size:13px;">
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text-muted);">Kode</span>
                        <span style="color:#fff; font-family:monospace;">{{ $booking['booking_code'] ?? '-' }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text-muted);">Event</span>
                        <span style="color:#fff;">{{ $booking['event_title'] ?? '-' }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text-muted);">User ID</span>
                        <span style="color:#fff;">#{{ $booking['user_id'] ?? '-' }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text-muted);">Qty</span>
                        <span style="color:#fff;">{{ $booking['quantity'] ?? '-' }}</span>
                    </div>
                    <div style="display:flex; justify-content:space-between;">
                        <span style="color:var(--text-muted);">Status</span>
                        <span><span class="status-badge status-{{ $booking['status'] ?? 'pending' }}">{{ $booking['status'] ?? '-' }}</span></span>
                    </div>
                    @if($booking['used_at'] ?? false)
                        <div style="display:flex; justify-content:space-between;">
                            <span style="color:var(--text-muted);">Digunakan</span>
                            <span style="color:var(--brand); font-weight:700;">{{ \Illuminate\Support\Carbon::parse($booking['used_at'])->format('d M Y H:i') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @elseif($result && !$result['success'] && !$booking)
            <hr style="border:none; border-top:1px solid var(--line); margin:20px 0;">
            <div style="background:rgba(243,114,127,0.1); border:1px solid var(--text-negative); border-radius:8px; padding:16px; text-align:center;">
                <p style="color:var(--text-negative); font-weight:700; margin:0;">{{ $result['message'] ?? 'Tiket tidak ditemukan' }}</p>
            </div>
        @endif
    </div>
</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
var html5QrCode = null;
var isScanning = false;

function startCamera() {
    if (html5QrCode) {
        html5QrCode.stop().then(function() {
            startReader();
        }).catch(function(err) {
            console.error(err);
        });
    } else {
        startReader();
    }
}

function startReader() {
    html5QrCode = new Html5Qrcode('reader');

    var config = {
        fps: 10,
        qrbox: { width: 250, height: 250 },
    };

    html5QrCode.start(
        { facingMode: 'environment' },
        config,
        onScanSuccess,
        onScanFailure,
    ).then(function() {
        isScanning = true;
        document.getElementById('scan-status').textContent = 'Kamera aktif — arahkan QR code ke kamera';
        document.getElementById('btn-toggle-camera').textContent = 'Matikan Kamera';
    }).catch(function(err) {
        document.getElementById('scan-status').textContent = 'Gagal mengakses kamera: ' + err;
        document.getElementById('btn-toggle-camera').textContent = 'Mulai Kamera';
    });
}

function stopCamera() {
    if (html5QrCode && isScanning) {
        html5QrCode.stop().then(function() {
            isScanning = false;
            document.getElementById('scan-status').textContent = 'Kamera dimatikan';
            document.getElementById('btn-toggle-camera').textContent = 'Mulai Kamera';
        }).catch(function(err) {
            console.error(err);
        });
    }
}

function toggleCamera() {
    if (isScanning) {
        stopCamera();
    } else {
        startCamera();
    }
}

function onScanSuccess(decodedText, decodedResult) {
    stopCamera();

    document.getElementById('scan-status').textContent = 'QR terdeteksi: ' + decodedText;

    // Isi form dan submit
    var form = document.getElementById('verify-form');
    var input = form.querySelector('input[name="booking_code"]');
    input.value = decodedText;
    form.submit();
}

function onScanFailure(error) {
    // ignore scan failures - they're continuous
}

function uploadQR() {
    var fileInput = document.getElementById('qr-upload');
    var statusEl = document.getElementById('upload-status');

    if (!fileInput.files || fileInput.files.length === 0) {
        statusEl.textContent = 'Pilih file gambar QR terlebih dahulu';
        statusEl.style.color = 'var(--text-negative)';
        return;
    }

    var file = fileInput.files[0];
    statusEl.textContent = 'Memindai gambar...';
    statusEl.style.color = 'var(--text-muted)';

    var scanner = new Html5Qrcode('reader');

    scanner.scanFile(file, true)
        .then(function(decodedText) {
            statusEl.textContent = 'QR terdeteksi: ' + decodedText;
            statusEl.style.color = 'var(--brand)';

            // Isi form dan submit
            var form = document.getElementById('verify-form');
            var input = form.querySelector('input[name="booking_code"]');
            input.value = decodedText;
            form.submit();
        })
        .catch(function(err) {
            statusEl.textContent = 'Tidak dapat membaca QR dari gambar: ' + err;
            statusEl.style.color = 'var(--text-negative)';
        });
}

// Auto start camera on page load
document.addEventListener('DOMContentLoaded', function() {
    startCamera();
});
</script>
<style>
#reader video {
    border-radius: 8px;
}
#reader__scan_region {
    background: #000;
    border-radius: 8px;
    min-height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
}
#reader__dashboard_section {
    display: none !important;
}
</style>
@endsection
