<?php

namespace App\Utils;

use Illuminate\Support\Facades\Storage;

class FileManagerLogic
{
    private static function get_file_name($path)
    {
        $temp = explode('/',$path);
        return end($temp);
    }

    private static function get_file_ext($name)
    {
        $temp = explode('.',$name);
        return end($temp);
    }

    private static function get_path_for_db($full_path)
    {
        $temp = explode('/',$full_path, 3);
        return end($temp);
    }

    public static function format_file_and_folders($files, $type)
    {
        $data = [];
        foreach($files as $file)
        {
            $name = self::get_file_name($file);
            $ext = self::get_file_ext($name);
            $path = '';
            if($type == 'file')
            {
                $path = $file;
            }
            if($type == 'folder')
            {
                $path = $file;
            }
            if(in_array($ext, ['jpg', 'png', 'jpeg', 'gif', 'bmp', 'tif', 'tiff', 'webp']) || $type=='folder')
            $data[] = [
                'name'=> $name,
                'path'=>  $path,
                'db_path'=>  self::get_path_for_db($file),
                'type'=>$type
            ];
        }
        return $data;
    }

    public static function getFileSize($path): string
    {
        if (!is_null($path)) {
            try {
                $headers = get_headers($path, 1);
                $decimals = 2;
                $bytes = isset($headers['Content-Length']) ? $headers['Content-Length'] : $headers['content-length'];
                $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
                $factor = floor((strlen($bytes) - 1) / 3);
                return sprintf("%.{$decimals}f", $bytes / (1024 ** $factor)) . @$size[$factor];
            } catch(\Exception $exception) {
            }

            try {
                $ch = curl_init($path);
                curl_setopt($ch, CURLOPT_NOBODY, true);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects if necessary
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignore SSL verification if needed
                curl_exec($ch);

                $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
                curl_close($ch);

                if ($contentLength <= 0) {
                    return 'Unknown Size';
                }

                $decimals = 2;
                $sizeUnits = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
                $factor = floor((strlen($contentLength) - 1) / 3);

                return sprintf("%.{$decimals}f", $contentLength / (1024 ** $factor)) . ' ' . $sizeUnits[$factor];
            } catch (\Exception $exception) {
                return 'Error: ' . $exception->getMessage();
            }
        }
        return 'Invalid File';
    }

    public static function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }
}
