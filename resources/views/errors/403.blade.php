@extends('layouts.admin')

@section('content')
<div class="row">
    <div class="col-12 col-md-8 mx-auto">
        <div class="card">
            <div class="card-body text-center py-5">
                <h1 class="display-5 mb-3">Samahani, hauna ruhusa</h1>
                <p class="text-muted mb-4">Huenda huna ruhusa ya kufikia ukurasa huu (403). Tafadhali wasiliana nasi ikiwa unadhani hii ni kosa.</p>
                <div class="mb-4">
                    <a href="{{ route('dashboard') }}" class="btn btn-primary me-2">Rudi Dashboard</a>
                    <a href="https://wa.me/255676994832" class="btn btn-success me-2" target="_blank">Wasiliana WhatsApp: +255 676 994 832</a>
                    <a href="mailto:eportsolution@gmail.com" class="btn btn-outline-secondary">Tutumie Barua Pepe: eportsolution@gmail.com</a>
                </div>
                <div class="text-muted">
                    ZAKA MANAGEMENT SYSTEM - BOMBAMBILI PARISH
                </div>
            </div>
        </div>
    </div>
@endsection
