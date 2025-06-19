<?php

use App\Enums\GlobalConstant;
use App\Utils\FileManagerLogic;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (!function_exists('productImagePath')) {
    function productImagePath(string $type): string
    {
        return asset(GlobalConstant::FILE_PATH['product'][$type]);
    }
}

if (!function_exists('getStorageImages')) {
    function getStorageImages($path, $type = null, $source = null): string
    {
        if ($source && base_path($source)) {
            if ($type == 'payment-banner' && DOMAIN_POINTED_DIRECTORY == 'public') {
                return asset(str_replace('app/public/', '', $source));
            }
            return (!empty($path) && $path['status'] == 200) ? $path['path'] : dynamicAsset($source);
        }
        if ($source && file_exists($source)) {
            return (!empty($path) && $path['status'] == 200) ? $path['path'] : $source;
        }
        $placeholderMap = [
            'backend-basic' => 'back-end/img/placeholder/placeholder-1-1.png',
            'backend-brand' => 'back-end/img/placeholder/brand.png',
            'backend-banner' => 'back-end/img/placeholder/placeholder-4-1.png',
            'backend-category' => 'back-end/img/placeholder/category.png',
            'backend-logo' => 'back-end/img/placeholder/placeholder-4-1.png',
            'backend-product' => 'back-end/img/placeholder/product.png',
            'backend-profile' => 'back-end/img/placeholder/user.png',
            'backend-payment' => 'back-end/img/placeholder/placeholder-4-1.png',
            'product' => [
                'theme_aster' => 'assets/img/placeholder/placeholder-1-1.png',
                'theme_fashion' => 'assets/img/placeholder/placeholder-1-1.png',
                'default' => 'public/assets/front-end/img/placeholder/placeholder-1-1.png',
            ],
            'avatar' => [
                'theme_aster' => 'assets/img/placeholder/user.png',
                'theme_fashion' => 'assets/img/placeholder/user.png',
                'default' => 'public/assets/front-end/img/placeholder/user.png',
            ],
            'banner' => [
                'theme_aster' => 'assets/img/placeholder/placeholder-2-1.png',
                'theme_fashion' => 'assets/img/placeholder/placeholder-2-1.png',
                'default' => 'public/assets/front-end/img/placeholder/placeholder-2-1.png',
            ],
            'wide-banner' => [
                'theme_aster' => 'assets/img/placeholder/placeholder-4-1.png',
                'theme_fashion' => 'assets/img/placeholder/placeholder-4-1.png',
                'default' => 'public/assets/front-end/img/placeholder/placeholder-4-1.png',
            ],
            'brand' => [
                'theme_aster' => 'assets/img/placeholder/placeholder-1-1.png',
                'theme_fashion' => 'assets/img/placeholder/placeholder-2-1.png',
                'default' => 'public/assets/front-end/img/placeholder/placeholder-1-1.png',
            ],
            'category' => [
                'theme_aster' => 'assets/img/placeholder/placeholder-1-1.png',
                'theme_fashion' => 'assets/img/placeholder/placeholder-1-1.png',
                'default' => 'public/assets/front-end/img/placeholder/placeholder-1-1.png',
            ],
            'logo' => [
                'theme_aster' => 'assets/img/placeholder/placeholder-4-1.png',
                'theme_fashion' => 'assets/img/placeholder/placeholder-4-1.png',
                'default' => 'public/assets/front-end/img/placeholder/placeholder-4-1.png',
            ],
            'shop' => [
                'theme_aster' => 'assets/img/placeholder/shop.png',
                'theme_fashion' => 'assets/img/placeholder/shop.png',
                'default' => 'public/assets/front-end/img/placeholder/shop.png',
            ],
            'shop-banner' => [
                'theme_aster' => 'assets/img/placeholder/placeholder-4-1.png',
                'theme_fashion' => 'assets/img/placeholder/placeholder-4-1.png',
                'default' => 'public/assets/front-end/img/placeholder/seller-banner.png',
            ],
        ];

        if (isset($placeholderMap[$type])) {
            if (is_array($placeholderMap[$type])) {
                $theme = theme_root_path();
                $placeholderPath = theme_asset(path: $placeholderMap[$type][$theme]);
                if ($theme == 'default') {
                    $placeholderPath = theme_asset(path: $placeholderMap[$type][$theme]);
                }
                return (!empty($path) && $path['status'] == 200) ? $path['path'] : $placeholderPath;
            } else {
                return (!empty($path) && $path['status'] == 200) ? $path['path'] : dynamicAsset(path: 'public/assets/' . $placeholderMap[$type]);
            }
        }

        return (!empty($path) && $path['status'] == 200) ? $path['path'] : dynamicStorage(path: 'public/assets/front-end/img/placeholder/placeholder-2-1.png');
    }
}

if (!function_exists('dynamicAsset')) {
    function dynamicAsset(string $path): string
    {
        if (DOMAIN_POINTED_DIRECTORY == 'public') {
            $position = strpos($path, 'public/');
            $result = $path;
            if ($position === 0) {
                $result = preg_replace('/public/', '', $path, 1);
            }
        } else {
            $result = $path;
        }
        return asset($result);
    }
}

if (!function_exists('dynamicStorage')) {
    function dynamicStorage(string $path): string
    {
        if (DOMAIN_POINTED_DIRECTORY == 'public') {
            $result = str_replace('storage/app/public', 'storage', $path);
        } else {
            $result = $path;
        }
        return asset($result);
    }
}

if (!function_exists('getValidImage')) {
    function getValidImage($path, $type = null, $source = null): string
    {
        if (DOMAIN_POINTED_DIRECTORY == 'public') {
            $path = str_replace('storage/app/public', 'storage', $path);
        }

        $givenPath = dynamicStorage($path);

        if ($source) {
            return is_file($path) ? $givenPath : $source;
        }

        $placeholderMap = [
            'backend-basic' => 'back-end/img/placeholder/placeholder-1-1.png',
            'backend-brand' => 'back-end/img/placeholder/brand.png',
            'backend-banner' => 'back-end/img/placeholder/placeholder-4-1.png',
            'backend-category' => 'back-end/img/placeholder/category.png',
            'backend-logo' => 'back-end/img/placeholder/placeholder-4-1.png',
            'backend-product' => 'back-end/img/placeholder/product.png',
            'backend-profile' => 'back-end/img/placeholder/user.png',
            'backend-payment' => 'back-end/img/placeholder/placeholder-4-1.png',
            'product' => [
                'theme_aster' => 'assets/img/placeholder/placeholder-1-1.png',
                'theme_fashion' => 'assets/img/placeholder/placeholder-1-1.png',
                'default' => 'public/assets/front-end/img/placeholder/placeholder-1-1.png',
            ],
            'avatar' => [
                'theme_aster' => 'assets/img/placeholder/user.png',
                'theme_fashion' => 'assets/img/placeholder/user.png',
                'default' => 'public/assets/front-end/img/placeholder/user.png',
            ],
            'banner' => [
                'theme_aster' => 'assets/img/placeholder/placeholder-2-1.png',
                'theme_fashion' => 'assets/img/placeholder/placeholder-2-1.png',
                'default' => 'public/assets/front-end/img/placeholder/placeholder-2-1.png',
            ],
            'wide-banner' => [
                'theme_aster' => 'assets/img/placeholder/placeholder-4-1.png',
                'theme_fashion' => 'assets/img/placeholder/placeholder-4-1.png',
                'default' => 'public/assets/front-end/img/placeholder/placeholder-4-1.png',
            ],
            'brand' => [
                'theme_aster' => 'assets/img/placeholder/placeholder-2-1.png',
                'theme_fashion' => 'assets/img/placeholder/placeholder-2-1.png',
                'default' => 'public/assets/front-end/img/placeholder/placeholder-1-1.png',
            ],
            'category' => [
                'theme_aster' => 'assets/img/placeholder/placeholder-1-1.png',
                'theme_fashion' => 'assets/img/placeholder/placeholder-1-1.png',
                'default' => 'public/assets/front-end/img/placeholder/placeholder-1-1.png',
            ],
            'logo' => [
                'theme_aster' => 'assets/img/placeholder/placeholder-4-1.png',
                'theme_fashion' => 'assets/img/placeholder/placeholder-4-1.png',
                'default' => 'public/assets/front-end/img/placeholder/placeholder-4-1.png',
            ],
            'shop' => [
                'theme_aster' => 'assets/img/placeholder/shop.png',
                'theme_fashion' => 'assets/img/placeholder/shop.png',
                'default' => 'public/assets/front-end/img/placeholder/shop.png',
            ],
            'shop-banner' => [
                'theme_aster' => 'assets/img/placeholder/placeholder-4-1.png',
                'theme_fashion' => 'assets/img/placeholder/placeholder-4-1.png',
                'default' => 'public/assets/front-end/img/placeholder/seller-banner.png',
            ],
        ];

        if (isset($placeholderMap[$type])) {
            if (is_array($placeholderMap[$type])) {
                $theme = theme_root_path();
                $placeholderPath = theme_asset(path: $placeholderMap[$type][$theme]);
                if ($theme == 'default') {
                    $placeholderPath = theme_asset(path: $placeholderMap[$type][$theme]);
                }

                return is_file($path) ? $givenPath : $placeholderPath;
            } else {
                return is_file($path) ? $givenPath : dynamicAsset(path: 'public/assets/' . $placeholderMap[$type]);
            }
        }

        return is_file($path) ? $givenPath : dynamicStorage(path: 'public/assets/front-end/img/placeholder/placeholder-2-1.png');
    }
}

if (!function_exists('validFileCheck')) {
    function validFileCheck($path)
    {
        $headers = get_headers($path);
        if (stripos($headers[0], "200 OK")) {
            return $path;
        } else {
            return null;
        }
    }
}

if (!function_exists('getTemporaryImageForExport')) {
    function getTemporaryImageForExport($imagePath)
    {
        $imageData = file_get_contents($imagePath);
        return imagecreatefromstring($imageData);
    }
}

if (!function_exists('getImageForExport')) {
    function getImageForExport($imagePath)
    {
        $temporaryImage = getTemporaryImageForExport($imagePath);
        $pngImage = imagecreatetruecolor(imagesx($temporaryImage), imagesy($temporaryImage));
        imagealphablending($pngImage, false);
        imagesavealpha($pngImage, true);
        imagecopy($pngImage, $temporaryImage, 0, 0, 0, 0, imagesx($temporaryImage), imagesy($temporaryImage));
        return $pngImage;
    }
}

if (!function_exists('getFileInfoFromURL')) {
    function getFileInfoFromURL($url = null): array
    {
        if ($url) {
            $headers = get_headers($url, 1);
            $fileMimeType = isset($headers['Content-Type']) && is_array($headers['Content-Type']) ? end($headers['Content-Type']) : ($headers['Content-Type'] ?? 'unknown');

            if ($fileMimeType === 'unknown') {
                $filePath = '/tmp/' . basename($url);
                file_put_contents($filePath, file_get_contents($url));
                $fileMimeType = mime_content_type($filePath);
                unlink($filePath);
            }
            $fileExtension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            $fileSize = $headers['Content-Length'] ?? strlen(file_get_contents($url));
        }

        return [
            'extension' => $fileExtension ?? '',
            'mime_type' => $fileMimeType ?? '',
            'mime_category' => getCategorizeFileMimeType(mimeType: $fileMimeType ?? ''),
            'size' => $fileSize ?? 0,
            'sizeReadable' => FileManagerLogic::formatBytes($fileSize ?? 0),
        ];
    }
}

if (!function_exists('getCategorizeFileMimeType')) {
    function getCategorizeFileMimeType($mimeType = ''): string
    {
        $mimeCategories = [
            'image' => [
                'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/bmp', 'image/tiff',
                'image/x-icon', 'image/heif', 'image/heic'
            ],
            'video' => [
                'video/mp4', 'video/mpeg', 'video/ogg', 'video/webm', 'video/avi', 'video/quicktime',
                'video/x-msvideo', 'video/x-ms-wmv', 'video/x-flv', 'video/3gpp', 'video/3gpp2'
            ],
            'audio' => [
                'audio/mpeg', 'audio/ogg', 'audio/wav', 'audio/webm', 'audio/aac', 'audio/x-aac', 'audio/flac',
                'audio/mp4', 'audio/3gpp', 'audio/3gpp2', 'audio/midi', 'audio/x-midi', 'audio/x-wav',
                'audio/x-ms-wma', 'audio/x-ms-wmv', 'audio/x-realaudio'
            ],
            'document' => [
                'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.template', 'application/rtf', 'text/plain',
                'text/html', 'application/vnd.oasis.opendocument.text', 'application/vnd.oasis.opendocument.presentation',
                'application/vnd.oasis.opendocument.spreadsheet', 'application/vnd.oasis.opendocument.graphics',
                'application/x-abiword', 'application/x-appleworks', 'application/x-iwork-keynote-sffkey',
                'application/x-iwork-pages-sffpages', 'application/x-iwork-numbers-sffnumbers'
            ],
            'spreadsheet' => [
                'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.oasis.opendocument.spreadsheet', 'application/vnd.sun.xml.calc', 'application/vnd.lotus-1-2-3'
            ],
            'presentation' => [
                'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.openxmlformats-officedocument.presentationml.slideshow', 'application/vnd.oasis.opendocument.presentation'
            ],
            'application' => [
                'application/zip', 'application/x-rar-compressed', 'application/x-tar', 'application/x-7z-compressed',
                'application/x-bzip', 'application/x-bzip2', 'application/x-gzip', 'application/x-httpd-php',
                'application/x-shockwave-flash', 'application/x-java-archive', 'application/x-msdownload',
                'application/vnd.android.package-archive', 'application/x-msaccess', 'application/x-cab',
                'application/x-debian-package', 'application/x-redhat-package-manager'
            ],
            'archive' => [
                'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed', 'application/x-tar',
                'application/gzip', 'application/x-bzip2', 'application/x-lzma', 'application/x-lzip',
                'application/x-xz'
            ],
            'font' => [
                'font/otf', 'font/ttf', 'font/woff', 'font/woff2', 'application/x-font-ttf',
                'application/x-font-woff', 'application/font-woff'
            ],
            'code' => [
                'text/css', 'text/javascript', 'application/javascript', 'application/json', 'application/xml',
                'text/xml', 'text/x-python', 'text/x-php', 'text/x-csrc', 'text/x-c++src', 'text/x-java-source',
                'application/x-sh', 'application/x-perl', 'text/x-ruby'
            ]
            // Add more categories and MIME types as needed
        ];

        foreach ($mimeCategories as $category => $types) {
            if (in_array($mimeType, $types)) {
                return $category;
            }
        }

        // Fallback if MIME type doesn't match any category
        if (strpos($mimeType, 'image/') === 0) {
            return 'image';
        } elseif (strpos($mimeType, 'video/') === 0) {
            return 'video';
        } elseif (strpos($mimeType, 'audio/') === 0) {
            return 'audio';
        } elseif (strpos($mimeType, 'text/') === 0) {
            return 'document';
        } elseif (strpos($mimeType, 'application/') === 0) {
            return 'application';
        }

        return 'unknown';
    }
}

if (!function_exists('checkUploadMaxFileSizeLimit')) {
    function checkUploadMaxFileSizeLimit($limit = 0): bool
    {
        return convertIniSizeToMB(ini_get('upload_max_filesize')) >= $limit;
    }
}

if (!function_exists('checkPostMaxFileSizeLimit')) {
    function checkPostMaxFileSizeLimit($limit = 0): bool
    {
        return convertIniSizeToMB(ini_get('post_max_size')) >= $limit;
    }
}

if (!function_exists('convertIniSizeToMB')) {
    function convertIniSizeToMB($config): int
    {
        $unit = Str::upper(Str::substr($config, -1));
        $value = (int) $config;
        if ($unit === 'G') {
            return $value * 1024;
        } elseif ($unit === 'K') {
            return $value / 1024;
        } elseif ($unit === 'M' || $unit === '') {
            return $value;
        }
        return $value;
    }
}

