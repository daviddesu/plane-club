<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Media;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


class ProcessVideoUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $mediaItemId;

    /**
     * Create a new job instance.
     */
    public function __construct($mediaItemId)
    {
        $this->mediaItemId = $mediaItemId;
    }

    public function handle()
    {
        try {
            // Reload the media item from the database
            $mediaItem = Media::find($this->mediaItemId);

            if (!$mediaItem) {
                throw new \Exception('Media item not found with ID: ' . $this->mediaItemId);
            }

            Log::info('Processing video for Media Item ID: ' . $this->mediaItemId);

            // Determine the storage disk
            $storageDisk = Storage::disk(getenv('FILESYSTEM_DISK'));

            // Download raw video from the uploads disk to a local temporary file
            $rawVideoLocalPath = sys_get_temp_dir() . '/' . uniqid('raw_video_', true);

            // Get the file extension
            $extension = pathinfo($mediaItem->raw_video_path, PATHINFO_EXTENSION);
            $rawVideoLocalPath .= '.' . $extension;

            // Fetch the file content
            $rawVideoStream = $storageDisk->get($mediaItem->raw_video_path);
            Log::info('Length of raw video stream: ' . strlen($rawVideoStream));

            file_put_contents($rawVideoLocalPath, $rawVideoStream);

            Log::info('Raw video downloaded to: ' . $rawVideoLocalPath);

            // Compress Video
            $compressedPath = $this->compressVideo($rawVideoLocalPath);

            Log::info('Video compressed successfully for Media Item ID: ' . $this->mediaItemId);

            // Extract Thumbnail
            $thumbnailPath = $this->extractThumbnail($compressedPath);

            Log::info('Thumbnail extracted successfully for Media Item ID: ' . $this->mediaItemId);

            // Upload Files to the uploads disk
            // Upload compressed video
            $storedFilePath = $storageDisk
                ->putFile(
                    'aircraft',
                    new \Illuminate\Http\File($compressedPath),
                    [
                        'CacheControl' => 'public, max-age=31536000, immutable',
                        'ACL' => 'public-read',
                    ]
                );

            // Upload thumbnail
            $storedThumbnailFilePath = $storageDisk
                ->putFile(
                    'aircraft/thumbnails',
                    new \Illuminate\Http\File($thumbnailPath),
                    [
                        'CacheControl' => 'public, max-age=31536000, immutable',
                        'ACL' => 'public-read',
                    ]
                );

            Log::info('Files uploaded to storage for Media Item ID: ' . $this->mediaItemId);

            $storageDisk->delete($mediaItem->raw_video_path);
            Log::info('Deleted raw video from storage for Media Item ID: ' . $this->mediaItemId);

            // Update MediaItem with paths
            $mediaItem->update([
                'path' => $storedFilePath,
                'thumbnail_path' => $storedThumbnailFilePath,
                'raw_video_path' => null,
                'status' => 'processed',
            ]);

            Log::info('Media Item updated to processed for ID: ' . $this->mediaItemId);

            // Clean up local files
            $this->cleanUpFiles([$compressedPath, $thumbnailPath, $rawVideoLocalPath]);

            Log::info('ProcessVideoUpload job completed for Media Item ID: ' . $this->mediaItemId);



        } catch (\Exception $e) {
            Log::error('ProcessVideoUpload job failed for Media Item ID: ' . $this->mediaItemId . '. Error: ' . $e->getMessage());
            if (isset($mediaItem)) {
                $mediaItem->update(['status' => 'failed']);
            }
            // Optionally, rethrow the exception
            // throw $e;
        }
    }

    private function cleanUpFiles(array $filePaths)
    {
        foreach ($filePaths as $path) {
            if (file_exists($path)) {
                unlink($path);
                Log::info('Deleted temporary file: ' . $path);
            }
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

            $cmd = "ffmpeg -i " . escapeshellarg($videoPath) . " -c:v libx264 -crf 0 -preset medium -c:a copy " . escapeshellarg($compressedPath) . " 2>&1";

            // Initialize output and return variables
            $output = [];
            $returnVar = 0;

            Log::info('FFmpeg command:', ['cmd' => $cmd]);

            // Execute the command and capture the output
            exec($cmd, $output, $returnVar);

            // Log the command and output for debugging
            Log::info('FFmpeg output:', ['output' => $output]);

            if ($returnVar !== 0) {
                // Command failed
                $errorMessage = implode("\n", $output);
                Log::error('FFmpeg failed with return code ' . $returnVar . ': ' . $errorMessage);
                return false;
            }

            return $compressedPath;
        } catch (\Exception $e) {
            Log::error('Exception in compressVideo: ' . $e->getMessage());
            return false;
        }
    }

    public function extractThumbnail($videoPath)
    {
        $thumbnailPath = sys_get_temp_dir() . '/' . uniqid('thumb_', true) . '.jpg';

        try {
            $cmd = "ffmpeg -i " . escapeshellarg($videoPath) . " -ss 1 -vframes 1 " . escapeshellarg($thumbnailPath) . " 2>&1";

            // Initialize output and return variables
            $output = [];
            $returnVar = 0;

            // Execute the command and capture the output
            exec($cmd, $output, $returnVar);

            // Log the command and output for debugging
            Log::info('FFmpeg command for thumbnail:', ['cmd' => $cmd]);
            Log::info('FFmpeg output for thumbnail:', ['output' => $output]);

            if ($returnVar !== 0) {
                // Command failed
                $errorMessage = implode("\n", $output);
                Log::error('FFmpeg failed to extract thumbnail with return code ' . $returnVar . ': ' . $errorMessage);
                return false;
            }

            return $thumbnailPath;

        } catch (\Exception $e) {
            Log::error('Exception in extractThumbnail: ' . $e->getMessage());
            return false;
        }
    }
}
