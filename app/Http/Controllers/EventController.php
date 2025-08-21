<?php

namespace App\Http\Controllers;
//
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $events = Event::all();
            return response()->json([
                'success' => true,
                'message' => "Berhasil menampilkan semua kegiatan",
                'data' => $events
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Gagal menampilkan semua kegiatan",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $event = Event::create($request->all());
            return response()->json([
                'success' => true,
                'message' => 'Kegiatan berhasil disimpan',
                'data' => $event
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $event = Event::find($id);
            
            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kegiatan tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Berhasil menampilkan kegiatan',
                'data' => $event
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menampilkan kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'date' => 'sometimes|required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $event = Event::find($id);
            
            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kegiatan tidak ditemukan'
                ], 404);
            }

            $event->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Kegiatan berhasil diperbarui',
                'data' => $event->fresh()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $event = Event::find($id);
            
            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kegiatan tidak ditemukan'
                ], 404);
            }

            // Simpan data sebelum dihapus untuk response
            $deletedEvent = $event->toArray();
            
            // Hapus event
            $event->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kegiatan berhasil dihapus',
                'data' => $deletedEvent
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete multiple events
     */
    public function destroyMultiple(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|exists:events,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $deletedCount = Event::whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$deletedCount} kegiatan",
                'deleted_count' => $deletedCount
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soft delete event (jika menggunakan SoftDeletes trait)
     */
    public function softDestroy(string $id)
    {
        try {
            $event = Event::find($id);
            
            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kegiatan tidak ditemukan'
                ], 404);
            }

            // Menggunakan soft delete (perlu tambahkan SoftDeletes trait di Model)
            $event->delete();

            return response()->json([
                'success' => true,
                'message' => 'Kegiatan berhasil dihapus (soft delete)',
                'data' => $event
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore soft deleted event
     */
    public function restore(string $id)
    {
        try {
            $event = Event::withTrashed()->find($id);
            
            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kegiatan tidak ditemukan'
                ], 404);
            }

            if (!$event->trashed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kegiatan tidak dalam kondisi terhapus'
                ], 400);
            }

            $event->restore();

            return response()->json([
                'success' => true,
                'message' => 'Kegiatan berhasil dipulihkan',
                'data' => $event
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan kegiatan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Force delete (permanent delete)
     */
    public function forceDestroy(string $id)
    {
        try {
            $event = Event::withTrashed()->find($id);
            
            if (!$event) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kegiatan tidak ditemukan'
                ], 404);
            }

            $deletedEvent = $event->toArray();
            $event->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Kegiatan berhasil dihapus permanen',
                'data' => $deletedEvent
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus kegiatan secara permanen',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}