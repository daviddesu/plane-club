<?php

namespace App\Services;

use App\Enums\Media;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MediaService {

    public function convertImagetoJPEG($imagePath)
    {
        // Check if the image is already a JPEG
        $mimeType = $this->getMimeType($imagePath);

        if ($mimeType === 'image/jpeg' || $mimeType === 'image/jpg') {
            // Image is already JPEG, return the original path
            return $imagePath;
        }

        $convertedFilePath = sys_get_temp_dir() . '/' . uniqid('image_', true) . '.jpg';

        // Initialize variables to capture output and status
        $output = [];
        $returnVar = 0;

        // Use 'convert' instead of 'magick convert'
        exec("convert " . escapeshellarg($imagePath) . " " . escapeshellarg($convertedFilePath) . " 2>&1", $output, $returnVar);

        if ($returnVar !== 0) {
            // Conversion failed
            $errorMessage = implode("\n", $output);
            return false;
        }

        // Check if the output file exists
        if (!file_exists($convertedFilePath)) {
            return false;
        }

        return $convertedFilePath; // Return the converted file path
    }


    public function getMimeType($filePath)
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            return $mimeType;
        } else {
            // Fallback to getimagesize for images
            $imageInfo = getimagesize($filePath);
            if ($imageInfo && isset($imageInfo['mime'])) {
                return $imageInfo['mime'];
            }
            // Unable to determine MIME type
            return null;
        }
    }

    public function createImage($mediaFilePath, $sightingId)
    {
        $filePath = $this->convertImagetoJPEG($mediaFilePath);
        if (!$filePath) {
            throw new \RuntimeException("The uploaded image could not be processed and converted to JPG.");
        }

        $fileSizeInBytes = filesize($mediaFilePath);

        // Upload processed file to b2
        $storedFilePath = Storage::disk(getenv('FILESYSTEM_DISK'))
            ->putFile(
                'aircraft',
                new \Illuminate\Http\File($filePath),
                [
                    'CacheControl' => 'public, max-age=31536000, immutable',
                    'ACL' => 'public-read',
                ]
            );

        // Clean up the local temporary files
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        if ($filePath !== $mediaFilePath && file_exists($mediaFilePath)) {
            unlink($mediaFilePath);
        }

        // Save media record
        $mediaItem = Auth::user()->mediaItems()->create([
            "path" => $storedFilePath,
            "sighting_id" => $sightingId,
            "type" => Media::IMAGE->value,
            "thumbnail_path" => null,
            'status' => 'processed',
            'size' => $fileSizeInBytes,
        ]);
    }
}
