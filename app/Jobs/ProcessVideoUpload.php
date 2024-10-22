<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class ProcessVideoUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $mediaItem;
    public $mediaFilePath;
    /**
     * Create a new job instance.
     */
    public function __construct(Media $mediaItem, $mediaFilePath)
    {
        $this->mediaItem = $mediaItem;
        $this->mediaFilePath = $mediaFilePath;
    }


    public function handle()
    {
        // Compress Video
        $compressedPath = $this->compressVideo($this->mediaFilePath);

        if (!$compressedPath) {
            // Handle failure (e.g., log error, update media item status)
            return;
        }

        // Extract Thumbnail
        $thumbnailPath = $this->extractThumbnail($this->mediaFilePath);

        if (!$thumbnailPath) {
            // Handle failure
            return;
        }

        // Upload Files to S3
        $storedFilePath = Storage::disk('s3')
            ->putFile(
                'aircraft',
                new \Illuminate\Http\File($compressedPath),
                [
                    'CacheControl' => 'public, max-age=31536000, immutable',
                ]
            );

        $storedThumbnailFilePath = Storage::disk('s3')
            ->putFile(
                'aircraft/thumbnails',
                new \Illuminate\Http\File($thumbnailPath),
                [
                    'CacheControl' => 'public, max-age=31536000, immutable',
                ]
            );

        // Update MediaItem with paths
        $this->mediaItem->update([
            'path' => $storedFilePath,
            'thumbnail_path' => $storedThumbnailFilePath,
            'status' => 'processed',
        ]);

        // Clean up local files
        if (file_exists($compressedPath)) {
            unlink($compressedPath);
        }
        if (file_exists($thumbnailPath)) {
            unlink($thumbnailPath);
        }
        if (file_exists($this->mediaFilePath)) {
            unlink($this->mediaFilePath);
        }

    }

    /**
     * Compresses a video using FFmpeg.
     */
    public function compressVideo($videoPath)
    {
        $compressedPath = sys_get_temp_dir() . '/' . uniqid('video_', true) . '.mp4';

        // Compress video using FFmpeg
        try {
            $filterChain = "scale=1280:-2,setsar=1/1";

            $cmd = "ffmpeg -i " . escapeshellarg($videoPath) . " -c:v libx264 -b:v 1000k -c:a aac -vf " . escapeshellarg($filterChain) . " " . escapeshellarg($compressedPath) . " 2>&1";
            exec($cmd, $output, $returnVar);

            if ($returnVar !== 0) {
                // Command failed
                return false;
            }

            return $compressedPath;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Extracts a thumbnail from a video file.
     */
    public function extractThumbnail($videoPath)
    {
        $thumbnailPath = sys_get_temp_dir() . '/' . uniqid('thumb_', true) . '.jpg';

        try {
            $cmd = "ffmpeg -i " . escapeshellarg($videoPath) . " -ss 1 -vframes 1 " . escapeshellarg($thumbnailPath) . " 2>&1";
            exec($cmd, $output, $returnVar);

            if ($returnVar !== 0) {
                // Command failed
                return false;
            }

            return $thumbnailPath;

        } catch (\Exception $e) {
            return false;
        }
    }
}
