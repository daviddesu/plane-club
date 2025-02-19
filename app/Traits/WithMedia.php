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
        $mimeRule = 'mimetypes:image/*';

        if ($user->isPro()) {
            // Pro plan: up to 500 MB
            $maxSizeKB = 512000;
        }

        return [
            'media' => ['nullable', 'file', 'max:'.$maxSizeKB, $mimeRule],
        ];
    }

    public function messages()
    {
        return [
            'media.max' => 'Your file may not be larger than :max KB.',
        ];
    }

    protected function validateAndProcessMedia(?int $id = null): bool
    {
        if (!$this->media) {
            return true;
        }

        $mediaService = app(MediaService::class);
        /** @var User $user */
        $user = Auth::user();
        $mediaFilePath = $this->media->getRealPath();
        $fileSizeInBytes = filesize($mediaFilePath);
        $mimeType = $mediaService->getMimeType($mediaFilePath);

        // Plan-Specific Rules:
            if ($user->isPro()) {
                $maxImageBytes = 524288000; // 500MB
                if ($fileSizeInBytes > $maxImageBytes) {
                    $this->warning('Image exceeds the 500MB limit for your plan. Please choose a smaller Image.');
                    return false;
                }
            } else {
                $maxImageBytes = 4194304; // 4MB
                $this->warning('Image exceeds the 4MB limit for your plan. Please choose a smaller Image.');
                return false;
            }


        $newTotalStorageInBytes = $user->used_disk + $fileSizeInBytes;

        if ($user->hasExceededUploadLimit()) {
            $this->warning('You have reached your upload limit for this month. <a href="/checkout">Please upgrade to Plane ClubPro</a> for unlimited video and image uploads.');
            return false;
        }

        if (!$mimeType) {
            $this->warning('Unable to determine the MIME type of the uploaded file.');
            return false;
        }

        if (!str_contains($mimeType, 'image')) {
            $this->warning('Unsupported media type uploaded.');
            return false;
        }

        $mediaService->createImage($mediaFilePath, $id);

        // Update user's used_disk
        $user->used_disk = $newTotalStorageInBytes;
        $user->save();

        return true;
    }

    protected function checkStorageLimits(): void
    {
        $this->storageLimitExceeded = Auth::user()->hasExceededUploadLimit();
    }
}
