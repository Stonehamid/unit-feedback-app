<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ResponseTrait
{
    protected function success($data = null, string $message = null, int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        return response()->json($response, $code);
    }

    protected function error(string $message = null, $errors = null, int $code = 400): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    protected function created($data = null, string $message = 'Data berhasil dibuat'): JsonResponse
    {
        return $this->success($data, $message, 201);
    }

    protected function updated($data = null, string $message = 'Data berhasil diperbarui'): JsonResponse
    {
        return $this->success($data, $message, 200);
    }

    protected function deleted(string $message = 'Data berhasil dihapus'): JsonResponse
    {
        return $this->success(null, $message, 200);
    }

    protected function notFound(string $message = 'Data tidak ditemukan'): JsonResponse
    {
        return $this->error($message, null, 404);
    }

    protected function unauthorized(string $message = 'Tidak diizinkan'): JsonResponse
    {
        return $this->error($message, null, 401);
    }

    protected function forbidden(string $message = 'Akses ditolak'): JsonResponse
    {
        return $this->error($message, null, 403);
    }

    protected function validationError($errors, string $message = 'Validasi gagal'): JsonResponse
    {
        return $this->error($message, $errors, 422);
    }

    protected function paginated($data, $paginator): JsonResponse
    {
        return $this->success([
            'items' => $data,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ]
        ]);
    }
}