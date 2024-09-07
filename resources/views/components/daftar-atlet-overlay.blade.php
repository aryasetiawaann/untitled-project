<div id="overlay" class="overlay">
    <div class="all-container all-card overlay-container w100">
        <header class="flex divider">
            <h2>Tambah Atlet</h2>
            <span id="closeOverlay" class="bx bx-md bx-x"></span>
        </header>
        <section>
            <form class="atlet" method="POST" action="{{ route('dashboard.atlet.store') }}" enctype="multipart/form-data">
                @csrf

                <label for="nama">Nama Atlet *</label>
                <input type="text" id="nama" name="nama" placeholder="Nama Atlet">
                <label for="umur">Tanggal lahir *</label>
                <input type="date" id="umur" name="umur" placeholder="Umur">
                <label for="jenisKelamin">Jenis Kelamin</label>
                <select id="jenisKelamin" name="jenisKelamin">
                    <option value="pria">Pria</option>
                    <option value="wanita">Wanita</option>
                </select>
                <label for="record">Track Record</label>
                <p><i style="font-size: 12px">(Tulis 0 jika tidak ada)</i></p>
                    <div class="record">
                        <input type="number" id="record_minute" name="record_minute" placeholder="Menit" min="0" step="1" style="width: 30%;">
                        <input type="number" id="record_second" name="record_second" placeholder="Detik" min="0" max="59" step="1" style="width: 30%;">
                        <input type="number" id="record_millisecond" name="record_millisecond" placeholder="Milidetik" min="0" max="99" step="1" style="width: 30%;">
                    </div>
                <label for="dokumen">Dokumen *</label>
                <p><i style="font-size: 12px">(Akte / KTP *.pdf)</i></p>
                <input type="file" name="dokumen" id="dokumen" accept=".pdf">
                <div class="flex center" style="margin-top: 20px;">   
                    <button type="submit" class="submit-button">Kirim</button>
                </div>
            </form>
        </section>
    </div>
</div>