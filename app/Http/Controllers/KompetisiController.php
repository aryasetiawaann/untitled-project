<?php

namespace App\Http\Controllers;

use App\Models\Kompetisi;
use App\Models\Atlet;
use App\Exports\KompetisiExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;


class KompetisiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kompetisi = Kompetisi::all()->sortByDesc("tutup_pendaftaran");

        return view('pages.dashboard-kompetisi')->with(['kompetisi'=>$kompetisi]);
    }

    public function kompetisiSaya(){
        $acara_ids = Atlet::where('user_id', auth()->user()->id) // or auth()->user()->id
        ->with('acara') // eager load acara
        ->get()
        ->flatMap(function ($atlet) {
            return $atlet->acara->pluck('id');
        })
        ->unique();

        $kompetisis = Kompetisi::whereHas('acara', function ($query) use ($acara_ids) {
            $query->whereIn('id', $acara_ids);
        })->get();

        return view('pages.dashboard-kompetisi-saya')->with(['kompetisis' => $kompetisis]);
    }

    public function adminIndex(){

        $kompetisis = Kompetisi::all();

        return view('admin.admin-tambahkompetisi', compact('kompetisis'));
    }

    public function showKompetisiAdmin(){
        $kompetisi = Kompetisi::all()->sortByDesc("tutup_pendaftaran");

        return view('admin.admin-tambahacara', compact('kompetisi'));
    }


    public function tambahKompetisi(Request $request)
    {
        $data = [ "nama"=> $request->nama,
        "lokasi"=> $request->lokasi,
        "deskripsi"=> $request->deskripsi,
        "buka_pendaftaran"=> $request->openreg,
        "tutup_pendaftaran"=> $request->closereg,
        "kategori"=> $request->kategori,
        "waktu_techmeeting"=> $request->techmeet,
        "waktu_kompetisi"=> $request->datekompe,
        ];

        $validation = Validator::make($data, [
            "nama" => "required",
            "lokasi" => "required",
            "buka_pendaftaran" => "required|date",
            "tutup_pendaftaran" => "required|date|after:buka_pendaftaran",
            "kategori" => "required",
            "waktu_techmeeting" => "required|date|after:tutup_pendaftaran",
            "waktu_kompetisi" => "required|date|after:waktu_techmeeting",
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'lokasi.required' => 'Lokasi wajib diisi.',
            'buka_pendaftaran.required' => 'Tanggal buka pendaftaran wajib diisi.',
            'tutup_pendaftaran.required' => 'Tanggal tutup pendaftaran wajib diisi.',
            'tutup_pendaftaran.after' => 'Tanggal tutup pendaftaran harus setelah tanggal buka pendaftaran.',
            'waktu_techmeeting.required' => 'Waktu technical meeting wajib diisi.',
            'waktu_techmeeting.after' => 'Waktu technical meeting harus setelah tanggal tutup pendaftaran.',
            'waktu_kompetisi.required' => 'Waktu kompetisi wajib diisi.',
            'waktu_kompetisi.after' => 'Waktu kompetisi harus setelah waktu technical meeting.',
        ]);

        $validation->after(function($validator) use ($data) {
            if (isset($data['waktu_kompetisi']) && isset($data['waktu_techmeeting']) && isset($data['tutup_pendaftaran'])) {
                $waktuKompetisi = strtotime($data['waktu_kompetisi']);
                $waktuTechMeeting = strtotime($data['waktu_techmeeting']);
                $tutupPendaftaran = strtotime($data['tutup_pendaftaran']);
                
                if ($waktuKompetisi <= $tutupPendaftaran || $waktuKompetisi <= $waktuTechMeeting) {
                    $validator->errors()->add('waktu_kompetisi', 'Waktu kompetisi harus setelah waktu technical meeting dan tanggal tutup pendaftaran.');
                }
            }
        });
        
        if ($validation->fails()) {
            return redirect()->back()
                ->withErrors($validation)
                ->withInput()
                ->with('error', 'Validasi gagal, silakan periksa kembali input Anda.');
        }

        Kompetisi::create($data);

        return redirect()->back()->with('success', 'Data berhasil disimpan.');

    }

    public function editKompetisi($id){
        $kompetisi = Kompetisi::find($id);

        return view('admin.admin-editkompetisi', compact('kompetisi'));
    }
    
    public function uploadHasilKompetisi(Request $request)
    {

        $kompetisi = Kompetisi::find($request->kompetisi);

        if ($request->hasFile('file')) {

            if ($kompetisi->file_hasil && File::exists(public_path($kompetisi->file_hasil))) {
                File::delete(public_path($kompetisi->file_hasil));
            }

            $fileName = time() . '.' . $request->file->extension();
            $request->file->move(public_path('assets/file_hasil'), $fileName);
            $kompetisi->file_hasil = 'assets/file_hasil/' . $fileName;
        }

        $kompetisi->save();

        return redirect()->back()->with('success','Upload berhasil');
        
    }

    public function deleteHasilKompetisi($id)
    {
        $kompetisi = Kompetisi::find($id);

        if ($kompetisi->file_hasil && File::exists(public_path($kompetisi->file_hasil))) {
            File::delete(public_path($kompetisi->file_hasil));
            $kompetisi->file_hasil = null;
            $kompetisi->save();
        }

        return redirect()->back()->with('success','File berhasil dihapus');
    }

    public function editHasilKompetisi($id)
    {
        $kompetisi = Kompetisi::find($id);

        return view('admin.admin-editfile', compact('kompetisi'));
    }

    public function updateHasilKompetisi(Request $request)
    {
        $kompetisi = Kompetisi::find($request->id);

        if ($request->hasFile('file')) {

            if ($kompetisi->file_hasil && File::exists(public_path($kompetisi->file_hasil))) {
                File::delete(public_path($kompetisi->file_hasil));
            }

            $fileName = time() . '.' . $request->file->extension();
            $request->file->move(public_path('assets/file_hasil'), $fileName);
            $kompetisi->file_hasil = 'assets/file_hasil/' . $fileName;
        }

        $kompetisi->save();

        return redirect()->route('admin.dashboard')->with('success','File berhasil diperbaharui');
    }

    public function downloadHasilKompetisi($id)
    {
        $kompetisi = Kompetisi::find($id);

        $path = public_path($kompetisi->file_hasil);

        if (!File::exists($path)) {
            abort(404);
        }

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = Response::make($file, 200);
        $response->header("Content-Type", $type);

        $response->header("Content-Disposition", 'attachment; filename='.$kompetisi->nama.".pdf");

        return $response;

    }

    public function downloadExcel($kompetisiId)
    {
        $kompetisi = Kompetisi::find($kompetisiId);

        return Excel::download(new KompetisiExport($kompetisiId), $kompetisi->nama . '.xlsx');
    }

    public function update(Request $request)
    {
        $data = [ "nama"=> $request->nama,
        "lokasi"=> $request->lokasi,
        "deskripsi"=> $request->deskripsi,
        "buka_pendaftaran"=> $request->openreg,
        "tutup_pendaftaran"=> $request->closereg,
        "kategori"=> $request->kategori,
        "waktu_techmeeting"=> $request->techmeet,
        "waktu_kompetisi"=> $request->datekompe,
        ];

        $validation = Validator::make($data, [
            "nama" => "required",
            "lokasi" => "required",
            "buka_pendaftaran" => "required|date",
            "tutup_pendaftaran" => "required|date|after:buka_pendaftaran",
            "kategori" => "required",
            "waktu_techmeeting" => "required|date|after:tutup_pendaftaran",
            "waktu_kompetisi" => "required|date|after:waktu_techmeeting",
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'lokasi.required' => 'Lokasi wajib diisi.',
            'buka_pendaftaran.required' => 'Tanggal buka pendaftaran wajib diisi.',
            'tutup_pendaftaran.required' => 'Tanggal tutup pendaftaran wajib diisi.',
            'tutup_pendaftaran.after' => 'Tanggal tutup pendaftaran harus setelah tanggal buka pendaftaran.',
            'waktu_techmeeting.required' => 'Waktu technical meeting wajib diisi.',
            'waktu_techmeeting.after' => 'Waktu technical meeting harus setelah tanggal tutup pendaftaran.',
            'waktu_kompetisi.required' => 'Waktu kompetisi wajib diisi.',
            'waktu_kompetisi.after' => 'Waktu kompetisi harus setelah waktu technical meeting.',
        ]);

        $validation->after(function($validator) use ($data) {
            if (isset($data['waktu_kompetisi']) && isset($data['waktu_techmeeting']) && isset($data['tutup_pendaftaran'])) {
                $waktuKompetisi = strtotime($data['waktu_kompetisi']);
                $waktuTechMeeting = strtotime($data['waktu_techmeeting']);
                $tutupPendaftaran = strtotime($data['tutup_pendaftaran']);
                
                if ($waktuKompetisi <= $tutupPendaftaran || $waktuKompetisi <= $waktuTechMeeting) {
                    $validator->errors()->add('waktu_kompetisi', 'Waktu kompetisi harus setelah waktu technical meeting dan tanggal tutup pendaftaran.');
                }
            }
        });
        
        if ($validation->fails()) {
            return redirect()->back()
                ->withErrors($validation)
                ->withInput()
                ->with('error', 'Validasi gagal, silakan periksa kembali input Anda.');
        }

        $kompetisi = Kompetisi::find($request->id);
        $kompetisi->update($data);

        return redirect()->route('dashboard.admin.acara')->with('success', 'Data berhasil diperbaharui.');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Kompetisi::find($id)->delete();
        return redirect()->route('dashboard.admin.acara')->with('success','Kompetisi berhasil dihapus');
    }
}
