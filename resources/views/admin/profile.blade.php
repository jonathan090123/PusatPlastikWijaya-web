@extends('layouts.admin')

@section('title', 'Profil Saya - Admin')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-user-cog"></i> Profil Admin</h1>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
    {{-- Info Profil --}}
    <div class="card">
        <div class="card-header"><span><i class="fas fa-user"></i> Informasi Profil</span></div>
        <div class="card-body">
            <form action="{{ route('profile.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label><i class="fas fa-user"></i> Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-phone"></i> No. Handphone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx">
                    @error('phone')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Alamat</label>
                    <textarea name="address" rows="3" placeholder="Masukkan alamat lengkap...">{{ old('address', $user->address) }}</textarea>
                    @error('address')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary" style="margin-top:0.5rem;">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
            </form>
        </div>
    </div>

    {{-- Ganti Password --}}
    <div class="card">
        <div class="card-header"><span><i class="fas fa-lock"></i> Ganti Password</span></div>
        <div class="card-body">
            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Password Saat Ini</label>
                    <input type="password" name="current_password" required>
                    @error('current_password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-key"></i> Password Baru</label>
                    <input type="password" name="password" required>
                    @error('password')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label><i class="fas fa-key"></i> Konfirmasi Password Baru</label>
                    <input type="password" name="password_confirmation" required>
                </div>

                <button type="submit" class="btn btn-warning" style="margin-top:0.5rem;">
                    <i class="fas fa-lock"></i> Perbarui Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
@media (max-width: 768px) {
    .admin-content > div[style*="grid-template-columns"] {
        grid-template-columns: 1fr !important;
    }
}
</style>
@endpush
