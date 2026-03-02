@extends('layouts.admin')

@section('title', 'Dashboard - Admin')

@section('content')
<div class="page-header">
    <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-shopping-bag"></i></div>
        <div class="stat-info">
            <h3>0</h3>
            <p>Total Pesanan</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-money-bill-wave"></i></div>
        <div class="stat-info">
            <h3>Rp 0</h3>
            <p>Total Pendapatan</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow"><i class="fas fa-box"></i></div>
        <div class="stat-info">
            <h3>0</h3>
            <p>Total Produk</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fas fa-users"></i></div>
        <div class="stat-info">
            <h3>0</h3>
            <p>Total Pelanggan</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span><i class="fas fa-clock"></i> Pesanan Terbaru</span>
    </div>
    <div class="card-body">
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>Belum ada pesanan</h3>
            <p>Pesanan baru akan muncul di sini</p>
        </div>
    </div>
</div>
@endsection
