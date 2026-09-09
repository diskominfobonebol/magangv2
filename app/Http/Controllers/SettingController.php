<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function index()
    {
        // Ambil pengaturan token WA jika sudah ada
        $waToken = Setting::where('key', 'wa_token')->value('value');
        $waDevice = Setting::where('key', 'wa_device')->value('value');

        return view('admin.settings.index', compact('waToken', 'waDevice'));
    }

    public function update(Request $request)
    {
        // Simpan atau update token ke database
        Setting::updateOrCreate(
            ['key' => 'wa_token'],
            ['value' => $request->wa_token]
        );

        Setting::updateOrCreate(
            ['key' => 'wa_device'],
            ['value' => $request->wa_device]
        );

        return back()->with('success', 'Konfigurasi WhatsApp berhasil disimpan!');
    }
}