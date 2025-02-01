<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    /**
     * Tampilkan semua video (GET /api/videos)
     */
    public function index()
    {
        $videos = Video::latest()->get();

        return response()->json([
            'status' => 'success',
            'data' => $videos
        ], 200);
    }

    /**
     * Simpan video yang diunggah (POST /api/videos)
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'video' => 'required|mimes:mp4,mov,avi|max:20480', // 20MB max
        ]);

        // Simpan video ke storage (storage/app/public/videos)
        $videoPath = $request->file('video')->store('videos', 'public');

        // Simpan ke database
        $video = Video::create([
            'title' => $request->title,
            'video_path' => $videoPath,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Video berhasil diunggah!',
            'data' => $video
        ], 201);
    }

    /**
     * Hapus video dari database dan storage (DELETE /api/videos/{id})
     */
    public function destroy(Video $video)
    {
        // Hapus file dari penyimpanan
        Storage::disk('public')->delete($video->video_path);

        // Hapus data dari database
        $video->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Video berhasil dihapus!',
        ], 200);
    }
}
