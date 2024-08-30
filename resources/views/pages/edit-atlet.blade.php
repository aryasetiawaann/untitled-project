@extends('layouts.dashboard-layout')
@section('title', 'Atlet Saya')
@section('content')
@include('components.daftar-atlet-overlay')
    <div class="main-content">
        <div class="bottom-container center">
            <section class="all-container all-card w100 maxwd">
                <header class="divider flex">
                    <h1>Edit {{ $atlet->name}}</h1>
                </header>
                @if ($errors->any())
                <x-error-list>
                    @foreach ($errors->all() as $error)
                        <x-error-item>{{ $error }}</x-error-item>
                    @endforeach
                </x-error-list>
                @endif
                <div>
                    <form class="edit-atlet" method="POST" action="{{ route('dashboard.atlet.update', $atlet->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('put')

                        <label for="nama">Nama Atlet</label>
                        <input type="text" id="nama" name="nama" placeholder="Nama Atlet" value="{{ $atlet->name }}">
                        <label for="umur">Umur</label>
                        <input type="date" id="umur" name="umur" placeholder="Umur" value="{{ $atlet->umur }}">
                        <label for="jenisKelamin">Jenis Kelamin</label>
                        <select id="jenisKelamin" name="jenisKelamin">
                            <option value="pria" {{ $atlet->jenis_kelamin === "Pria" ? "selected" : "" }}>Pria</option>
                            <option value="wanita" {{ $atlet->jenis_kelamin === "Wanita" ? "selected" : "" }}>Wanita</option>
                        </select>
                        <label for="record">Track Record</label>
                        <p><i style="font-size: 12px">(Tulis 0 Jika tidak ada)</i></p>
                        <input type="number" id="record" name="record" placeholder="contoh: 3,25 (Menit)" value="{{ $atlet->track_record }}" step="0.01">
                        <label for="dokumen">Dokumen</label>
                        <input type="file" name="dokumen" id="dokumen" accept=".pdf" value="{{ $atlet->dokumen }}">
                        <input type="hidden" name="atlet_id" value="{{ $atlet->id }}">
                        <div class="flex center">   
                            <button type="submit" class="w50">Simpan</button>
                        </div>
                        
                    </form>
                </div>
            </section>
        </div>
    </div>
@endsection