<?php

namespace App\Http\Controllers;

use App\Services\GoogleDriveGallery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function home(GoogleDriveGallery $drive): View
    {
        $viewData = $this->portalViewData();
        $data = $viewData['data'];
        $homePhotos = [];
        if ($drive->configured()) {
            try {
                $homePhotos = array_slice($drive->photos(), 0, 8);
            } catch (\Throwable $error) {
                report($error);
            }
        }

        return view('home', [...$viewData, 'homePhotos' => $homePhotos]);
    }
    public function cash(): View { return view('cash', $this->portalViewData()); }
    public function events(): View { return view('events', $this->portalViewData()); }
    public function moments(GoogleDriveGallery $drive): View
    {
        $driveError = null; $driveAlbums = [];
        if ($drive->configured()) {
            try { $driveAlbums = $drive->albums(); } catch (\Throwable $error) { report($error); $driveError = 'Galeri Google Drive belum dapat dimuat.'; }
        }
        return view('moments', [...$this->portalViewData(), 'driveAlbums'=>$driveAlbums, 'driveConfigured'=>$drive->configured(), 'driveError'=>$driveError]);
    }

    public function driveImage(string $fileId, GoogleDriveGallery $drive): RedirectResponse
    {
        abort_unless($drive->configured(), 404);
        return redirect()->away('https://drive.google.com/thumbnail?'.http_build_query([
            'id' => $fileId,
            'sz' => 'w1200',
        ]));
    }
    public function loginForm(): View { return view('login'); }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate(['username' => 'required', 'password' => 'required']);
        $username = (string) config('portal.admin_username');
        $passwordHash = (string) config('portal.admin_password_hash');
        if (! hash_equals($username, $credentials['username']) || ! $passwordHash || ! password_verify($credentials['password'], $passwordHash)) {
            return back()->withErrors(['username' => 'Nama pengguna atau kata sandi tidak sesuai.'])->onlyInput('username');
        }
        $request->session()->put('admin', true);
        return redirect()->route('home')->with('success', 'Mode admin aktif.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('admin');
        return redirect()->route('home');
    }

    public function store(Request $request, string $section): RedirectResponse
    {
        abort_unless($request->session()->get('admin'), 403);
        $data = $this->data(); $id = (int) (microtime(true) * 1000);
        if ($section === 'cash') {
            $v = $request->validate(['date'=>'required|date','description'=>'required|max:120','type'=>'required|in:Masuk,Keluar','amount'=>'required|numeric|min:1']);
            $data['cash'][] = ['id'=>$id, ...$v];
        } elseif ($section === 'events') {
            $v = $request->validate(['date'=>'required|date','title'=>'required|max:120','location'=>'required|max:120','category'=>'required|max:50','description'=>'required|max:500']);
            $data['events'][] = ['id'=>$id, ...$v];
        } elseif ($section === 'moments') {
            $v = $request->validate(['date'=>'required|max:80','title'=>'required|max:120','description'=>'required|max:500','media_type'=>'nullable|in:photo,video','media_url'=>'nullable|url|max:1000']);
            $data['moments'][] = ['id'=>$id, 'icon'=>'✦', ...$v];
        } elseif ($section === 'announcement') {
            $data['announcement'] = $request->validate(['announcement'=>'required|max:500'])['announcement'];
        } elseif ($section === 'officials') {
            $v = $request->validate(['name'=>'required|max:120','position'=>'required|max:120']);
            $data['officials'][] = ['id'=>$id, ...$v];
        } elseif ($section === 'home-content') {
            $validated = $request->validate(['home'=>'required|array', 'home.*'=>'required|string|max:500']);
            $data['home_content'] = array_replace($this->defaultHomeContent(), $validated['home']);
        } else abort(404);
        $this->save($data);
        return back()->with('success', 'Perubahan berhasil disimpan.');
    }

    public function destroy(Request $request, string $section, int $id): RedirectResponse
    {
        abort_unless($request->session()->get('admin'), 403);
        abort_unless(in_array($section, ['cash','events','moments','officials']), 404);
        $data = $this->data();
        $data[$section] = array_values(array_filter($data[$section], fn ($item) => (int) $item['id'] !== $id));
        $this->save($data);
        return back()->with('success', 'Data berhasil dihapus.');
    }

    private function portalViewData(): array
    {
        $data = $this->data();
        $nextEvent = collect($data['events'] ?? [])
            ->filter(fn (array $event) => ($event['date'] ?? '') >= date('Y-m-d'))
            ->sortBy('date')
            ->first();
        $agendaTicker = $nextEvent ? sprintf(
            'AGENDA TERDEKAT: %s - %s | %s',
            $nextEvent['title'],
            date('d/m/Y', strtotime($nextEvent['date'])),
            $nextEvent['location'],
        ) : null;

        return ['data' => $data, 'tickerAnnouncement' => $data['announcement'], 'agendaTicker' => $agendaTicker];
    }

    private function data(): array
    {
        $payload = DB::table('portal_data')->where('key', 'main')->value('payload');
        if ($payload) {
            $data = json_decode($payload, true);
            $data['officials'] ??= [];
            $data['home_content'] = array_replace($this->defaultHomeContent(), $data['home_content'] ?? []);
            return $data;
        }
        $data = [
            'announcement'=>'Kerja bakti lingkungan dilaksanakan Minggu pukul 07.00. Mohon membawa alat kebersihan masing-masing.',
            'cash'=>[
                ['id'=>1,'date'=>'2026-08-01','description'=>'Iuran warga Agustus','type'=>'Masuk','amount'=>4250000],
                ['id'=>2,'date'=>'2026-08-04','description'=>'Perbaikan lampu gang','type'=>'Keluar','amount'=>750000],
            ],
            'events'=>[
                ['id'=>1,'date'=>'2026-08-18','title'=>'Kerja Bakti Lingkungan','location'=>'Pos RT','category'=>'Lingkungan','description'=>'Bersih selokan dan pangkas tanaman bersama.'],
                ['id'=>2,'date'=>'2026-08-24','title'=>'Malam Puncak HUT RI','location'=>'Lapangan warga','category'=>'Kebersamaan','description'=>'Pentas seni, pembagian hadiah, dan makan bersama.'],
            ],
            'moments'=>[
                ['id'=>1,'date'=>'2 Agustus 2026','title'=>'Penghijauan Gang Melati','description'=>'32 bibit baru ditanam bersama di sepanjang gang.','icon'=>'✦','media_type'=>null,'media_url'=>null],
                ['id'=>2,'date'=>'28 Juli 2026','title'=>'Juara Lomba Kebersihan','description'=>'RT 04 meraih juara dua tingkat kelurahan.','icon'=>'◆','media_type'=>null,'media_url'=>null],
            ],
            'officials'=>[],
            'home_content'=>$this->defaultHomeContent(),
        ]; $this->save($data); return $data;
    }

    private function defaultHomeContent(): array
    {
        return [
            'hero_kicker'=>'PORTAL KOMUNITAS / SA’AR KLECO',
            'hero_title_line_1'=>'Tetangga dekat,',
            'hero_title_emphasis'=>'kebersamaan',
            'hero_title_line_3'=>'erat.',
            'hero_lead'=>'Informasi warga yang tertata, transparan, dan mudah dijangkau dalam satu ruang bersama.',
            'agenda_button'=>'Lihat agenda',
            'cash_button'=>'Buka laporan kas',
            'panel_label'=>'RT 002 / RW 003',
            'panel_title'=>'Ruang hidup yang kita rawat bersama.',
            'panel_tagline'=>'GUYUP · RUKUN · BEBARENGAN',
            'moments_kicker'=>'MOMEN TERBARU',
            'moments_title'=>'Kabar dalam gambar.',
            'moments_link'=>'LIHAT SEMUA',
            'portal_kicker'=>'JELAJAHI PORTAL',
            'portal_title'=>'Informasi penting, di halaman yang tepat.',
            'cash_label'=>'01 / TRANSPARANSI',
            'cash_title'=>'Kas Warga',
            'cash_description'=>'Lihat saldo dan seluruh riwayat transaksi warga.',
            'agenda_label'=>'02 / KEBERSAMAAN',
            'agenda_title'=>'Agenda',
            'agenda_description'=>'Jadwal acara, kerja bakti, dan pelayanan warga.',
            'moments_label'=>'03 / DOKUMENTASI',
            'moments_card_title'=>'Momen',
            'moments_description'=>'Cerita, foto, dan video dari lingkungan kita.',
            'card_action'=>'BUKA HALAMAN',
            'officials_kicker'=>'PENGURUS LINGKUNGAN',
            'officials_title'=>'Nama pengurus dan jabatan.',
        ];
    }

    private function save(array $data): void
    {
        DB::table('portal_data')->updateOrInsert(
            ['key' => 'main'],
            ['payload' => json_encode($data, JSON_UNESCAPED_UNICODE), 'updated_at' => now()],
        );
    }
}
