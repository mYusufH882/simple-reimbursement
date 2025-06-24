<?php

namespace App\Services;

use App\Models\Proof;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    protected $allowedMimes = ['pdf', 'jpg', 'jpeg', 'png'];
    protected $maxFileSize = 2048; // 2MB in KB
    protected $uploadPath = 'reimbursement-proofs';

    /**
     * Upload proof file for reimbursement
     */
    public function uploadProof(UploadedFile $file, int $reimbursementId): Proof
    {
        $this->validateFile($file);

        // Generate unique filename
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $filename = $this->generateUniqueFilename($originalName, $extension);

        // Store file
        $filePath = $file->storeAs($this->uploadPath, $filename, 'public');

        if (!$filePath) {
            throw new \Exception('Failed to upload file');
        }

        // Save to database
        return Proof::create([
            'file_path' => $filePath,
            'file_name' => $originalName,
            'file_type' => $extension,
            'reimbursement_id' => $reimbursementId,
        ]);
    }

    /**
     * Delete proof file
     */
    public function deleteProof(Proof $proof): bool
    {
        try {
            // Delete file from storage
            if (Storage::disk('public')->exists($proof->file_path)) {
                Storage::disk('public')->delete($proof->file_path);
            }

            // Delete from database
            return $proof->delete();
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete proof file: ' . $e->getMessage());
        }
    }

    /**
     * Delete multiple proof files
     */
    public function deleteMultipleProofs(array $proofIds): bool
    {
        try {
            $proofs = Proof::whereIn('id', $proofIds)->get();

            foreach ($proofs as $proof) {
                $this->deleteProof($proof);
            }

            return true;
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete proof files: ' . $e->getMessage());
        }
    }

    /**
     * Get proof file URL
     */
    public function getProofUrl(Proof $proof): string
    {
        if (!Storage::disk('public')->exists($proof->file_path)) {
            throw new \Exception('Proof file not found');
        }

        return Storage::disk('public')->url($proof->file_path);
    }

    /**
     * Download proof file
     */
    public function downloadProof(Proof $proof)
    {
        if (!Storage::disk('public')->exists($proof->file_path)) {
            throw new \Exception('Proof file not found');
        }

        return Storage::disk('public')->download($proof->file_path, $proof->file_name);
    }

    /**
     * Check if file exists
     */
    public function proofExists(Proof $proof): bool
    {
        return Storage::disk('public')->exists($proof->file_path);
    }

    /**
     * Get file size in KB
     */
    public function getProofSize(Proof $proof): int
    {
        if (!$this->proofExists($proof)) {
            return 0;
        }

        return Storage::disk('public')->size($proof->file_path) / 1024; // Convert to KB
    }

    /**
     * Validate uploaded file
     */
    protected function validateFile(UploadedFile $file): void
    {
        // Check file size
        if ($file->getSize() > ($this->maxFileSize * 1024)) {
            throw new \Exception("File size exceeds maximum limit of {$this->maxFileSize}KB");
        }

        // Check mime type
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedMimes)) {
            $allowedTypes = implode(', ', $this->allowedMimes);
            throw new \Exception("File type not allowed. Allowed types: {$allowedTypes}");
        }

        // Check if file is actually an image/pdf (additional security)
        $mimeType = $file->getMimeType();
        $allowedMimeTypes = [
            'application/pdf',
            'image/jpeg',
            'image/jpg',
            'image/png'
        ];

        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new \Exception('Invalid file type detected');
        }
    }

    /**
     * Generate unique filename
     */
    protected function generateUniqueFilename(string $originalName, string $extension): string
    {
        $nameWithoutExtension = pathinfo($originalName, PATHINFO_FILENAME);
        $sanitizedName = Str::slug($nameWithoutExtension);
        $timestamp = now()->format('YmdHis');
        $randomString = Str::random(8);

        return "{$timestamp}_{$randomString}_{$sanitizedName}.{$extension}";
    }

    /**
     * Clean up orphaned files (files without database records)
     */
    public function cleanupOrphanedFiles(): array
    {
        $allFiles = Storage::disk('public')->files($this->uploadPath);
        $dbFiles = Proof::pluck('file_path')->toArray();

        $orphanedFiles = array_diff($allFiles, $dbFiles);
        $deletedCount = 0;
        $errors = [];

        foreach ($orphanedFiles as $file) {
            try {
                Storage::disk('public')->delete($file);
                $deletedCount++;
            } catch (\Exception $e) {
                $errors[] = "Failed to delete {$file}: " . $e->getMessage();
            }
        }

        return [
            'deleted_count' => $deletedCount,
            'total_orphaned' => count($orphanedFiles),
            'errors' => $errors
        ];
    }

    /**
     * Get storage statistics
     */
    public function getStorageStats(): array
    {
        $allFiles = Storage::disk('public')->files($this->uploadPath);
        $totalSize = 0;
        $fileCount = count($allFiles);

        foreach ($allFiles as $file) {
            $totalSize += Storage::disk('public')->size($file);
        }

        return [
            'total_files' => $fileCount,
            'total_size_kb' => round($totalSize / 1024, 2),
            'total_size_mb' => round($totalSize / (1024 * 1024), 2),
            'average_file_size_kb' => $fileCount > 0 ? round(($totalSize / 1024) / $fileCount, 2) : 0
        ];
    }

    /**
     * Batch upload multiple files
     */
    public function uploadMultipleProofs(array $files, int $reimbursementId): array
    {
        $uploadedProofs = [];
        $errors = [];

        foreach ($files as $index => $file) {
            try {
                $proof = $this->uploadProof($file, $reimbursementId);
                $uploadedProofs[] = $proof;
            } catch (\Exception $e) {
                $errors["file_{$index}"] = $e->getMessage();
            }
        }

        return [
            'uploaded_proofs' => $uploadedProofs,
            'errors' => $errors,
            'success_count' => count($uploadedProofs),
            'error_count' => count($errors)
        ];
    }
}
