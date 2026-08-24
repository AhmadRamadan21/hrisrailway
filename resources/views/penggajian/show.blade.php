@extends('layouts.app')

@section('content')
<style>
  .slip-box-wrapper {
    background: #ffffff;
    border-radius: 20px;
    border: none;
    box-shadow: 0 10px 40px -10px rgba(0,0,0,0.06);
    padding: 36px 48px;
    max-width: 850px;
    margin: 0 auto;
  }
  
  .btn-blue {
    background-color: #6366f1;
    border-color: #6366f1;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
    transition: all 0.2s;
  }
  .btn-blue:hover, .btn-blue:focus {
    background-color: #4f46e5;
    border-color: #4f46e5;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(99, 102, 241, 0.3);
  }
  .btn-outline-blue {
    color: #6366f1;
    border-color: #e0e7ff;
    background-color: #e0e7ff;
    transition: all 0.2s;
  }
  .btn-outline-blue:hover, .btn-outline-blue:focus {
    background-color: #c7d2fe;
    border-color: #c7d2fe;
    color: #4f46e5;
  }
  
  @media print {
    body * {
      visibility: hidden;
    }
    .slip-box-wrapper, .slip-box-wrapper * {
      visibility: visible;
    }
    .slip-box-wrapper {
      position: absolute;
      left: 0;
      top: 0;
      width: 100%;
      max-width: 100%;
      border: none;
      box-shadow: none;
      padding: 0;
    }
    .no-print {
      display: none !important;
    }
  }
</style>

{{-- TOP BAR ACTIONS --}}
<div class="d-flex justify-content-between align-items-center mb-4 mt-2 mx-auto no-print" style="max-width: 850px;">
  <h3 class="fw-semibold mb-0" style="color: #334155;">Lihat Slip Gaji</h3>
  <div class="d-flex gap-2">
    <a href="{{ route('penggajian.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-medium">
      <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
    <a href="{{ route('penggajian.pdf', $penggajian->id) }}" class="btn btn-blue btn-sm rounded-pill px-3 fw-medium">
      <i class="bi bi-download me-1"></i> Download PDF
    </a>
    <button onclick="window.print()" class="btn btn-outline-blue btn-sm rounded-pill px-3 fw-medium">
      <i class="bi bi-printer me-1"></i> Cetak
    </button>
  </div>
</div>

{{-- SLIP GAJI CARD CONTAINER --}}
<div class="slip-box-wrapper">
  @include('penggajian.print_slip_content', ['penggajian' => $penggajian])
</div>

@endsection
