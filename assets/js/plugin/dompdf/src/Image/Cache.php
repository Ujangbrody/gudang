<?php

namespace Dompdf\Image;

use Dompdf\Dompdf;
use Dompdf\Helpers;
use Dompdf\Exception\ImageException;


class Cache
{
    
    protected static $_cache = [];

    
    public static $broken_image = "data:image/svg+xml;charset=utf8,%3C?xml version='1.0'?%3E%3Csvg width='64' height='64' xmlns='http:

    public static $error_message = "Image not found or type unknown";
    
    
    protected static $_dompdf;

    
    static function resolve_url($url, $protocol, $host, $base_path, Dompdf $dompdf)
    {
        self::$_dompdf = $dompdf;
        
        $protocol = mb_strtolower($protocol);
        $parsed_url = Helpers::explode_url($url);
        $message = null;

        $remote = ($protocol && $protocol !== "file:

        $data_uri = strpos($parsed_url['protocol'], "data:") === 0;
        $full_url = null;
        $enable_remote = $dompdf->getOptions()->getIsRemoteEnabled();

        try {

            
            if (!$enable_remote && $remote && !$data_uri) {
                throw new ImageException("Remote file access is disabled.", E_WARNING);
            }
            
            
            if (($enable_remote && $remote) || $data_uri) {
                
                $full_url = Helpers::build_url($protocol, $host, $base_path, $url);

                
                if (isset(self::$_cache[$full_url])) {
                    $resolved_url = self::$_cache[$full_url];
                } 
                else {
                    $tmp_dir = $dompdf->getOptions()->getTempDir();
                    if (($resolved_url = @tempnam($tmp_dir, "ca_dompdf_img_")) === false) {
                        throw new ImageException("Unable to create temporary image in " . $tmp_dir, E_WARNING);
                    }
                    $image = "";

                    if ($data_uri) {
                        if ($parsed_data_uri = Helpers::parse_data_uri($url)) {
                            $image = $parsed_data_uri['data'];
                        }
                    } else {
                        list($image, $http_response_header) = Helpers::getFileContent($full_url, $dompdf->getHttpContext());
                    }

                    
                    if (empty($image)) {
                        $msg = ($data_uri ? "Data-URI could not be parsed" : "Image not found");
                        throw new ImageException($msg, E_WARNING);
                    } 
                    else {
                        
                        
                        
                        
                        
                        if (@file_put_contents($resolved_url, $image) === false) {
                            throw new ImageException("Unable to create temporary image in " . $tmp_dir, E_WARNING);
                        }
                    }
                }
            } 
            else {
                $resolved_url = Helpers::build_url($protocol, $host, $base_path, $url);

                if ($protocol == "" || $protocol === "file:
                    $realfile = realpath($resolved_url);
        
                    $rootDir = realpath($dompdf->getOptions()->getRootDir());
                    if (strpos($realfile, $rootDir) !== 0) {
                        $chroot = $dompdf->getOptions()->getChroot();
                        $chrootValid = false;
                        foreach($chroot as $chrootPath) {
                            $chrootPath = realpath($chrootPath);
                            if ($chrootPath !== false && strpos($realfile, $chrootPath) === 0) {
                                $chrootValid = true;
                                break;
                            }
                        }
                        if ($chrootValid !== true) {
                            throw new ImageException("Permission denied on $resolved_url. The file could not be found under the paths specified by Options::chroot.", E_WARNING);
                        }
                    }
        
                    if (!$realfile) {
                        throw new ImageException("File '$realfile' not found.", E_WARNING);
                    }
        
                    $resolved_url = $realfile;
                }
            }

            
            if (!is_readable($resolved_url) || !filesize($resolved_url)) {
                throw new ImageException("Image not readable or empty", E_WARNING);
            } 
            else {
                list($width, $height, $type) = Helpers::dompdf_getimagesize($resolved_url, $dompdf->getHttpContext());

                
                if ($width && $height && in_array($type, ["gif", "png", "jpeg", "bmp", "svg"])) {
                    
                    
                    if ($enable_remote && $remote || $data_uri) {
                        self::$_cache[$full_url] = $resolved_url;
                    }
                } 
                else {
                    throw new ImageException("Image type unknown", E_WARNING);
                }
            }
        } catch (ImageException $e) {
            $resolved_url = self::$broken_image;
            $type = "png";
            $message = self::$error_message;
            Helpers::record_warnings($e->getCode(), $e->getMessage() . " \n $url", $e->getFile(), $e->getLine());
            self::$_cache[$full_url] = $resolved_url;
        }

        return [$resolved_url, $type, $message];
    }

    
    static function clear()
    {
        if (empty(self::$_cache) || self::$_dompdf->getOptions()->getDebugKeepTemp()) {
            return;
        }

        foreach (self::$_cache as $file) {
            if ($file === self::$broken_image) {
                continue;
            }
            if (self::$_dompdf->getOptions()->getDebugPng()) {
                print "[clear unlink $file]";
            }
            unlink($file);
        }

        self::$_cache = [];
    }

    static function detect_type($file, $context = null)
    {
        list(, , $type) = Helpers::dompdf_getimagesize($file, $context);

        return $type;
    }

    static function is_broken($url)
    {
        return $url === self::$broken_image;
    }
}

if (file_exists(realpath(__DIR__ . "/../../lib/res/broken_image.svg"))) {
    Cache::$broken_image = realpath(__DIR__ . "/../../lib/res/broken_image.svg");
}