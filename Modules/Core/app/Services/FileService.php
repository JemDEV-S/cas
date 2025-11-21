<?php

namespace Modules\Core\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * File Service
 *
 * Servicio para manejo de archivos.
 */
class FileService
{
    /**
     * Almacena un archivo.
     *
     * @param UploadedFile $file
     * @param string $path
     * @param string $disk
     * @return array
     */
    public function store(UploadedFile $file, string $path = 'uploads', string $disk = 'public'): array
    {
        $filename = $this->generateUniqueFilename($file);
        $filePath = $file->storeAs($path, $filename, $disk);

        return [
            'path' => $filePath,
            'url' => Storage::disk($disk)->url($filePath),
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
    }

    /**
     * Elimina un archivo.
     *
     * @param string $path
     * @param string $disk
     * @return bool
     */
    public function delete(string $path, string $disk = 'public'): bool
    {
        return Storage::disk($disk)->delete($path);
    }

    /**
     * Verifica si un archivo existe.
     *
     * @param string $path
     * @param string $disk
     * @return bool
     */
    public function exists(string $path, string $disk = 'public'): bool
    {
        return Storage::disk($disk)->exists($path);
    }

    /**
     * Obtiene el contenido de un archivo.
     *
     * @param string $path
     * @param string $disk
     * @return string|null
     */
    public function get(string $path, string $disk = 'public'): ?string
    {
        if (!$this->exists($path, $disk)) {
            return null;
        }

        return Storage::disk($disk)->get($path);
    }

    /**
     * Obtiene la URL de un archivo.
     *
     * @param string $path
     * @param string $disk
     * @return string|null
     */
    public function url(string $path, string $disk = 'public'): ?string
    {
        if (!$this->exists($path, $disk)) {
            return null;
        }

        return Storage::disk($disk)->url($path);
    }

    /**
     * Mueve un archivo.
     *
     * @param string $from
     * @param string $to
     * @param string $disk
     * @return bool
     */
    public function move(string $from, string $to, string $disk = 'public'): bool
    {
        return Storage::disk($disk)->move($from, $to);
    }

    /**
     * Copia un archivo.
     *
     * @param string $from
     * @param string $to
     * @param string $disk
     * @return bool
     */
    public function copy(string $from, string $to, string $disk = 'public'): bool
    {
        return Storage::disk($disk)->copy($from, $to);
    }

    /**
     * Obtiene el tamaño de un archivo.
     *
     * @param string $path
     * @param string $disk
     * @return int|null
     */
    public function size(string $path, string $disk = 'public'): ?int
    {
        if (!$this->exists($path, $disk)) {
            return null;
        }

        return Storage::disk($disk)->size($path);
    }

    /**
     * Genera un nombre de archivo único.
     *
     * @param UploadedFile $file
     * @return string
     */
    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;

        return $filename;
    }

    /**
     * Valida el tipo MIME de un archivo.
     *
     * @param UploadedFile $file
     * @param array $allowedMimes
     * @return bool
     */
    public function validateMimeType(UploadedFile $file, array $allowedMimes): bool
    {
        return in_array($file->getMimeType(), $allowedMimes);
    }

    /**
     * Valida el tamaño de un archivo.
     *
     * @param UploadedFile $file
     * @param int $maxSizeInKb
     * @return bool
     */
    public function validateSize(UploadedFile $file, int $maxSizeInKb): bool
    {
        return $file->getSize() <= ($maxSizeInKb * 1024);
    }

    /**
     * Obtiene la extensión de un archivo.
     *
     * @param string $path
     * @return string
     */
    public function getExtension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Formatea el tamaño de un archivo en formato legible.
     *
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    public function formatSize(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
