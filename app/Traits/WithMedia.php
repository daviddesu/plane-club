<?php

namespace App\Traits;

use App\Services\MediaService;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Auth;

trait WithMedia
{
    use Toast;

    public $media = null;
    public bool $storageLimitExceeded = false;

    public function rules()
    {
        /** @var User $user */
        $user = Auth::user();

        // Default plan: up to 4 MB
        $maxSizeKB = 4096;
        // Default plan: images only
        $mimeRule = 'mimetypes:image/*';

        if ($user->isPro()) {
            // Pro plan: up to 500 MB
            $maxSizeKB = 512000;
            // Pro plan: images or videos
            $mimeRule = 'mimetypes:image/*,video/*';
        }

        return [
            'media' => ['nullable', 'file', 'max:'.$maxSizeKB, $mimeRule],
        ];
    }

    public function messages()
    {
        return [
            'media.max' => 'Your file may not be larger than :max KB.',
            'media.mimetypes' => 'Free users can only upload images. Pro users can upload images and videos.',
        ];
    }

    protected function validateAndProcessMedia(?int $id = null): bool
    {
        if (!$this->media) {
            return true;
        }

        $mediaService = app(MediaService::class);
        $user = Auth::user();
        $mediaFilePath = $this->media->getRealPath();
        $fileSizeInBytes = filesize($mediaFilePath);
        $mimeType = $mediaService->getMimeType($mediaFilePath);

        // Plan-Specific Rules:
        if (str_contains($mimeType, 'video')) {
            if ($user->isPro()) {
                $maxVideoBytes = 524288000; // 500 MB
                if ($fileSizeInBytes > $maxVideoBytes) {
                    $this->warning('Video exceeds the 500MB limit for your plan. Please choose a smaller video.');
                    return false;
                }
            } else {
                $this->warning('Your current plan does not allow video uploads. Please upgrade.');
                return false;
            }
        }

        $newTotalStorageInBytes = $user->used_disk + $fileSizeInBytes;
        $newTotalStorageInGB = $newTotalStorageInBytes / (1024 * 1024 * 1024);

        if ($newTotalStorageInGB > $user->getStorageLimitInGBAttribute()) {
            $this->warning('You have reached your storage limit. Please upgrade your subscription.');
            return false;
        }

        if (!$mimeType) {
            $this->warning('Unable to determine the MIME type of the uploaded file.');
            return false;
        }

        if (str_contains($mimeType, 'image')) {
            $mediaService->createImage($mediaFilePath, $id);
        } elseif (str_contains($mimeType, 'video')) {
            $mediaService->createVideo($mediaFilePath, $id);
        } else {
            $this->warning('Unsupported media type uploaded.');
            return false;
        }

        // Update user's used_disk
        $user->used_disk = $newTotalStorageInBytes;
        $user->save();

        return true;
    }

    protected function checkStorageLimits(): void
    {
        $this->storageLimitExceeded = auth()->user()->hasExceededStorageLimit();
    }
}
