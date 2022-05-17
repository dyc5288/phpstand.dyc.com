<?php

/**
 * Description: 上传
 * User: duanyc@infogo.com.cn
 * Date: 2021/07/5 15:53
 * Version: $Id: lib_upload.php 159388 2021-10-20 02:26:27Z duanyc $
 */

!defined('IN_INIT') && exit('Access Denied');

class lib_upload
{
    //文件变量
    public static $files = [];
    //严禁保存的文件名
    public static $filter_filename = '/\.(php|pl|sh|js)$/i';
    // 允许上传的文件名后缀
    public static $shortnames = ['zip', '7z', 'jpg', 'ico', 'torrent'];

    /**
     * 过滤文件相关
     *
     * @param $files
     * @return array
     */
    public static function filter_files(&$files): array
    {
        foreach ($files as $k => $v) {
            self::$files[$k] = $v;
        }
        unset($_FILES);

        return self::$files;
    }

    /**
     * 移动上传的文件
     *
     * @param $formname
     * @param $filename
     *
     * @return bool
     */
    public static function move_upload_file($formname, $filename)
    {
        if (self::is_upload_file($formname)) {
            if (preg_match(self::$filter_filename, $filename)) {
                return false;
            } else {
                return move_uploaded_file(self::$files[$formname]['tmp_name'], $filename);
            }
        }
        return false;
    }

    /**
     * 获得文件的扩展名
     *
     * @param $formname
     *
     * @return string
     */
    public static function get_shortname($formname)
    {
        $filetype = strtolower(isset(self::$files[$formname]['type']) ? self::$files[$formname]['type'] : '');
        $shortname = '';
        switch ($filetype) {
            case 'image/jpeg':
                $shortname = 'jpg';
                break;
            case 'image/pjpeg':
                $shortname = 'jpg';
                break;
            case 'image/gif':
                $shortname = 'gif';
                break;
            case 'image/png':
                $shortname = 'png';
                break;
            case 'image/xpng':
                $shortname = 'png';
                break;
            case 'image/wbmp':
                $shortname = 'bmp';
                break;
            default:
                $filename = isset(self::$files[$formname]['name']) ? self::$files[$formname]['name'] : '';
                if (preg_match("/\./", $filename)) {
                    $fs = explode('.', $filename);
                    $shortname = strtolower($fs[ count($fs)-1 ]);
                }
                break;
        }
        return $shortname;
    }

    /**
     * 获得指定文件表单的文件详细信息
     *
     * @param $formname
     * @param string $item
     *
     * @return bool|mixed|string
     */
    public static function get_file_info($formname, $item = '')
    {
        if (!isset(self::$files[$formname]['tmp_name'])) {
            return false;
        } else {
            if ($item=='') {
                return self::$files[$formname];
            } else {
                return (isset(self::$files[$formname][$item]) ? self::$files[$formname][$item] : '');
            }
        }
    }

    /**
     * 判断是否存在上传的文件
     *
     * @param $formname
     *
     * @return bool
     */
    public static function is_upload_file($formname)
    {
        $shortname = self::get_shortname($formname);
        if (!in_array($shortname, self::$shortnames)) {
            cutil_php_log("{$formname} forbid upload", 'upload');
            return false;
        }
        if (!isset(self::$files[$formname]['tmp_name'])) {
            return false;
        } else {
            return is_uploaded_file(self::$files[$formname]['tmp_name']);
        }
    }
}
