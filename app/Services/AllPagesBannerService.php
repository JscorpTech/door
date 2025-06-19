<?php

namespace App\Services;

use App\Traits\FileManagerTrait;

class AllPagesBannerService
{
    use FileManagerTrait;

    public function getProcessedData(object $request, object $banner = null): array
    {
        if ($banner) {
            $imageArray = json_decode($banner['value'], true);
            if (isset($imageArray['image'])) {
                $oldImage = is_array($imageArray['image']) && isset($imageArray['image']['image_name']) ? $imageArray['image']['image_name'] : $imageArray['image'];
                $imageName = $request->file('image') ? $this->update(dir: 'banner/', oldImage: $oldImage, format: 'webp', image: $request->file('image')) : $oldImage;
            }
        } else {
            $imageName = $this->upload(dir: 'banner/', format: 'webp', image: $request->file('image'));
        }
        $imageArray = [
            'image_name' => $imageName,
            'storage' => config('filesystems.disks.default') ?? 'public',
        ];

        return [
            'type' => $request['type'],
            'value' => json_encode([
                'status' => 0,
                'image' => $imageArray,
            ]),
            'created_at' => now()
        ];
    }

    public function deleteImage(string|array $image): bool
    {
        $this->delete(filePath: "/banner/" . ($image['image_name'] ?? $image));
        return true;
    }

}
