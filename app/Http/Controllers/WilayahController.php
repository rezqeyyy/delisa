<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WilayahController extends Controller
{
    public function provinces()
    {
        $res = Http::timeout(10)->get('https://www.emsifa.com/api-wilayah-indonesia/api/provinces.json');
        if ($res->failed()) return response()->json(['message' => 'Upstream error'], 502);
        return response()->json($res->json());
    }

    public function regencies($provId)
    {
        $url = "https://www.emsifa.com/api-wilayah-indonesia/api/regencies/{$provId}.json";
        $res = Http::timeout(10)->get($url);
        if ($res->failed()) return response()->json(['message' => 'Upstream error'], 502);
        return response()->json($res->json());
    }

    public function districts($kabId)
    {
        $url = "https://www.emsifa.com/api-wilayah-indonesia/api/districts/{$kabId}.json";
        $res = Http::timeout(10)->get($url);
        if ($res->failed()) return response()->json(['message' => 'Upstream error'], 502);
        return response()->json($res->json());
    }

    public function villages($kecId)
    {
        $url = "https://www.emsifa.com/api-wilayah-indonesia/api/villages/{$kecId}.json";
        $res = Http::timeout(10)->get($url);
        if ($res->failed()) return response()->json(['message' => 'Upstream error'], 502);
        return response()->json($res->json());
    }
}