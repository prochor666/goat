<?php
namespace GoatCore\Traits;

trait Disk {




    /* ********************************
    |
    | File operations
    |
    ********************************* */


    /**
    * Copy file
    * @param string $pathFrom
    * @param string $pathTo
    * @return bool
    */
    public function copyFile($pathFrom, $pathTo): bool
    {
        if (@is_writable(dirname($pathTo)) && $this->isFile($pathFrom)) {

            if (function_exists('copy')) {

                return copy($pathFrom, $pathTo);
            }else{

                return (bool)file_put_contents($pathTo, file_get_contents($pathFrom));
            }
        }

        return false;
    }


    /**
    * Rename/move file
    * @param string $pathFrom
    * @param string $pathTo
    * @return bool
    */
    public function moveFile($pathFrom, $pathTo): bool
    {
        if (@is_writable(dirname($pathTo)) && $this->isFile($pathFrom)) {

            return rename($pathFrom, $pathTo);
        }

        return false;
    }


    /**
    * Delete file
    * @param string $path
    * @return bool
    */
    public function deleteFile($path): bool
    {
        if (@is_writable($path) && $this->isFile($path)) {

            return unlink($path);
        }

        return false;
    }


    /**
    * Read file content
    * @param string $path
    * @return string
    */
    public function readFile($path): string
    {
        return @is_readable($path) && ($this->isFile($path) || $this->isLink($path)) ? file_get_contents($path): '';
    }


    /**
    * Create file with content defined in $data
    * @param string $path
    * @param string $data
    * @return bool
    */
    public function saveFile($path, $data = null): bool
    {
        if (@is_writable(dirname($path)) && !$this->isDir($path)) {

            $result = (bool)file_put_contents($path, $data);
            $this->permission($path, $this->defaultFilePermission());
            return $result;
        }

        return false;
    }


    /**
    * Append existing file with content defined in $data
    * @param string $path
    * @param string $data
    * @return bool
    */
    public function appendFile($path, $data = null): bool
    {
        if ($this->isFile($path) && @is_writable($path)) {

            return (bool)file_put_contents($path, $data, FILE_APPEND);
        }

        return false;
    }


    /**
    * Create symlink if it is possible
    * @param string $target
    * @param string $symlink
    * @return bool
    */
    public function saveSymlink($target, $symlink): bool
    {
        if (($this->isFile($target) || $this->isDir($target))
            && @is_writable(dirname($symlink))
            && !$this->isSymlink($symlink)
            && !$this->isDir($symlink)
            && !$this->isFile($symlink)) {

            return symlink($target, $symlink);
        }

        return false;
    }








    /* ********************************
    |
    | File/dir checkers and information
    |
    ********************************* */


    /**
    * Check file
    * @param string $path
    * @return bool
    */
    public function isFile($path): bool
    {
        return @is_readable($path) && file_exists($path) && is_file($path);
    }


    /**
    * Check symlink
    * @param string $path
    * @return bool
    */
    public function isSymlink($path): bool
    {
        return @is_readable($path) && file_exists($path) && is_link($path);
    }


    /**
    * Directory test
    * @param string $path
    * @return bool
    */
    public function isDir($path): bool
    {
        return @is_readable($path) && file_exists($path) && is_dir($path);
    }


    /**
    * Given path .dot extension
    * This method is not checking if filesystem target already exists
    * @param string $path
    * @return string
    */
    public function extension($path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }


    /**
    * Alias of extension method
    * @param string $path
    * @return string
    */
    public function ext($path): string
    {
        return $this->extension($path);
    }


    /**
    * Given path basename without .dot extension
    * This method is not checking if filesystem target already exists
    * @param string $path
    * @return string
    */
    public function filename($path): string
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }


    /**
    * Given path basename without .dot extension
    * This method is not checking if filesystem target already exists
    * @param string $path
    * @return string
    */
    public function basename($path): string
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }


    /**
    * Given path basename without .dot extension
    * This method is not checking if filesystem target already exists
    * @param string $path
    * @return string
    */
    public function dirname($path): string
    {
        return pathinfo($path, PATHINFO_DIRNAME);
    }


    /**
    * Filesystem target information (file/dir/symlink supported)
    * @param string $path
    * @return array
    */
    public function info($path): array
    {
        if ($this->isSymlink($path)) {

            return lstat($path);
        }

        if ($this->isFile($path)) {

            return lstat($path);
        }

        if ($this->isDir($path)) {

            return stat($path);
        }

        return [];
    }


    /**
    * Get the symllink target path
    * @param string $path
    * @return string
    */
    public function symlinkTarget($path): string
    {
        return readlink($path);
    }


    /**
    * Check directory emptiness
    * @param string $path
    * @return bool
    */
    public function isEmptyDir($path): bool
    {
        return ($this->isDir($path) && ($files = @scandir($path)) && count($files) <= 2);
    }








    /* ********************************
    |
    | Dir operations
    |
    ********************************* */


    /**
    * Create directory
    * @param string $path
    * @return bool
    */
    public function makeDir($path): bool
    {
        if (!$this->isDir($path)) {

            umask(0000);
            return mkdir($path, $this->defaultDirPermission(), true);
        }

        return false;
    }


    /**
    * Rename/move directory
    * @param string $pathFrom
    * @param string $pathTo
    * @return bool
    */
    public function moveDir($pathFrom, $pathTo): bool
    {
        if ($this->isDir($pathFrom)) {

            return rename($pathFrom, $pathTo);
        }

        return false;
    }


    /**
    * Delete directory, recursive
    * @param string $path
    * @return bool
    */
    public function deleteDir($path): bool
    {
        if ($this->isDir($path) && $this->isEmptyDir($path)) {

            return rmdir($path);

        } elseif ($this->isDir($path)) {

            $handle = opendir($path);

            while (false !== ($o = readdir($handle))) {

                if (( $o !== '.' ) && ( $o !== '..' )) {

                    $fsObj = $path . DIRECTORY_SEPARATOR . $o;

                    if ($this->isDir($fsObj)) {

                        $this->deleteDir($fsObj);

                    } elseif ($this->isFile($fsObj) || $this->isSymlink($fsObj)) {

                        $this->deleteFile($fsObj);
                    }
                }
            }

            closedir($handle);
            return rmdir($path);
        }

        return false;
    }



    /**
    * List directory, recursive, with object stats
    * @param string $path
    * @param bool $recursive
    * @return array
    */
    public function listDirInfo($path, $recursive = false): array
    {
        $dirList = [];

        if ($this->isDir($path)) {

            $handle = opendir($path);

            while (false !== ($o = readdir($handle))) {

                if (( $o != '.' ) && ( $o != '..' )) {

                    $fsObj = $path . DIRECTORY_SEPARATOR . $o;

                    if ($this->isDir($fsObj)) {

                        $dirList[$o] = [
                            'path' => $fsObj,
                            'list' => $recursive === true ? $this->listDir($fsObj, $recursive): false,
                            'type' => 'dir',
                            'name' => dirname($fsObj),
                            'info' => $this->info($fsObj),
                        ];

                    } elseif ($this->isSymlink($fsObj)) {

                        $dirList[$o] = [
                            'path' => $fsObj,
                            'type' => 'symlink',
                            'name' => basename($fsObj),
                            'info' => $this->info($fsObj),
                            'mime' => mime_content_type($fsObj),
                        ];

                    } else {

                        $dirList[$o] = [
                            'path' => $fsObj,
                            'type' => 'file',
                            'name' => basename($fsObj),
                            'info' => $this->info($fsObj),
                            'mime' => mime_content_type($fsObj),
                        ];
                    }
                }
            }
            closedir($handle);
        }

        ksort($dirList, SORT_LOCALE_STRING);

        return $dirList;
    }




    /**
    * List directory, recursive
    * @param string $path
    * @param bool $recursive
    * @return array
    */
    public function listDir($path, $recursive = false): array
    {
        $dirList = [];

        if ($this->isDir($path)) {

            $handle = opendir($path);

            while (false !== ($o = readdir($handle))) {

                if (( $o != '.' ) && ( $o != '..' )) {

                    $fsObj = $path . DIRECTORY_SEPARATOR . $o;

                    if ($this->isDir($fsObj)) {

                        $dirList[$o] = [
                            'path' => $fsObj,
                            'list' => $recursive === true ? $this->listDir($fsObj, $recursive): false,
                            'type' => 'dir',
                            'name' => dirname($fsObj),
                        ];

                    } elseif ($this->isSymlink($fsObj)) {

                        $dirList[$o] = [
                            'path' => $fsObj,
                            'type' => 'symlink',
                            'name' => basename($fsObj),
                        ];

                    } else {

                        $dirList[$o] = [
                            'path' => $fsObj,
                            'type' => 'file',
                            'name' => basename($fsObj),
                        ];
                    }
                }
            }
            closedir($handle);

        }

        ksort($dirList, SORT_LOCALE_STRING && SORT_FLAG_CASE && SORT_FLAG_CASE);

        return $dirList;
    }


    /**
    * Copy directory, recursive
    * @param string $pathFrom
    * @param string $pathTo
    * @return bool
    */
    public function copyDir($pathFrom, $pathTo): bool
    {
        $stat = $this->isEmptyDir($pathFrom);

        if ($this->isDir($pathFrom)) {

            $handle = opendir($pathFrom);
            $this->makeDir($pathTo);

            while (false !== ($o = readdir($handle))) {

                if (( $o != '.' ) && ( $o != '..' )) {

                    //$fsObj = "{$pathFrom}/{$o}";
                    //$fsObjTarget = "{$pathTo}/{$o}";

                    $fsObj = $pathFrom . DIRECTORY_SEPARATOR . $o;
                    $fsObjTarget = $pathTo . DIRECTORY_SEPARATOR . $o;

                    if ($this->isDir($fsObj)) {

                        $stat = $this->copyDir($fsObj, $fsObjTarget);

                    } elseif ($this->isSymlink($fsObj)) {

                        $stat = $this->copyFile($fsObj, $fsObjTarget);

                    } else {

                        $stat = $this->copyFile($fsObj, $fsObjTarget);
                    }
                }
            }
            closedir($handle);
        }

        return $stat;
    }









    /* ********************************
    |
    | Permissions
    |
    ********************************* */


    /**
    * Get/set permission $perm is octal int (0XXX NOT decimal XXX)
    * @param string $path
    * @param bool|int octal $perm [optional]
    * @return int octal
    */
    public function permission($path, $perm = 0): int
    {
        if (@is_readable($path)) {

            if ($perm !== 0 && @is_writable($path)) {

                if (function_exists('chmod')) {

                    umask(0000);
                    chmod($path, $perm);
                }

            }
        }

        return substr(sprintf('%o', fileperms($path)), -4);
    }


    /**
    * Returns directory permission
    * @param void
    * @return int octal
    */
    protected static function defaultDirPermission(): int
    {
        return 0755;
    }


    /**
    * Returns file permission
    * @param void
    * @return int octal
    */
    protected static function defaultFilePermission(): int
    {
        return 0644;
    }



    /* ********************************
    |
    | Path utils
    |
    ********************************* */


    /**
    * Returns native OS UNC path
    * @param string $path
    * @return string
    */
    public function s2unc($path): string
    {
        return implode(DIRECTORY_SEPARATOR, explode('/' ,$path));
    }


    /**
    * Returns UNC path with slash delimiter
    * @param string $path
    * @return string
    */
    public function unc2s($path)
    {
        return implode('/', explode(DIRECTORY_SEPARATOR, $path));
    }
}