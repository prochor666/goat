<?php
namespace Goat\Api;

use GoatCore\GoatCore;
use GoatCore\DbAssets\DbAssets;
use Goat\BasicAssetModel;

/**
* FileUploadModel - File upload API model
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/
class FileUploadModel extends BasicAssetModel
{
    use \GoatCore\Traits\Disk;

    protected $file, $storageService, $thumbService;

    public function __construct(GoatCore $app, DbAssets $assets)
    {
        parent::__construct($app, $assets);

        $this->file = ark($_FILES, 'data', []);

        $this->storageService = $this->app->store->entry('Goat\Storage');
        $this->thumbService = $this->app->store->entry('Goat\Thumbnail');


        // Native PHP upload error code translation
        // See: https://www.php.net/manual/en/features.file-upload.errors.php
        $this->nativeErrorMessages = [
            0 => 'There is no error, the file uploaded with success',
            1 => 'The uploaded file(chunk) exceeds the upload_max_filesize directive',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the request',
            3 => 'The file was only partially uploaded',
            4 => 'No file was uploaded, E.g. zero size',
            6 => 'Missing a temporary folder (can not write?)',
            7 => 'Failed to write file to disk',
            8 => 'A PHP extension stopped the file upload',
        ];

        $this->predefined = [
            'origin' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              => ['min' => 1],
            ],
            'basename' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              => ['min' => 1],
            ],
            'path' => [
                'validation_method'    => 'string',
                'default'              => '',
                'options'              => false,
            ],
            'domains_id' => [
                'validation_method'    => 'int',
                'default'              => 0,
                'options'              => ['min' => 1],
            ],
            'public' => [
                'validation_method'    => 'int',
                'default'              => 1,
                'options'              => ['min' => 0, 'max' => 1],
            ],
        ];
    }


    /**
    * Create new file
    * @param array $input
    * @return array
    */
    public function create($input): array
    {
        $input['domains_id'] = (int)ark($input, 'domains_id', 0);
        $input['public'] = (int)ark($input, 'public', 0);
        $input['basename'] = urlSafe(ark($input, 'origin', ''), '-');

        $input = $this->normalize($input, $setDefaults = true);

        // Check data completition
        $invalid = $this->invalid($input);

        if ($invalid === true) {

            return [
                'error' => "Invalid dataset",
                'input' => $input,
            ];
        }

        // Check domain id
        $domain = $this->getDomain($input['domains_id']);

        if ($domain->id === 0) {

            return [
                'error' => "Specified domain does not exist",
            ];
        }

        // Check same file path
        if ($this->exists(' basename LIKE ? AND path LIKE ? AND domains_id = ? ', [$input['basename'], $input['path'], $input['domains_id']]) === true) {

            return [
                'error' => "File {$input['path']}/{$input['basename']} already exists in database",
                'input' => $input,
            ];
        }

        $upload = $this->upload($input);

        if (ark($upload, 'error', false) !== false) {

            return [
                'error' => $upload['error'],
            ];
        }

        if (ark($upload, 'finalChunk', false) === true) {

            unset(
                $input['token'],
                $input['finalChunk'],
            );

            $input = $this->extend($input, 'create');
            $created = $this->assets->oneToMany($domain, $this->assets->getType(), [$input]);

            if ($created > 0) {

                // Save finalized file into db
                return [
                    'created' => $created,
                    'uploaded' => $upload,
                ];
            }

        } else {

            // Chunk uploaded
            return [
                'uploaded' => $upload,
            ];
        }

        return [
            'error' => 'Some SQL error'
        ];
    }


    /**
    * Update existing file
    *
    * @param int $id
    * @param array $input
    * @return array
    */
    public function update($id, $input): array
    {
        $input['domains_id'] = (int)ark($input, 'domains_id', 0);
        $input['public'] = (int)ark($input, 'public', 0);
        $input['basename'] = urlSafe(ark($input, 'origin', ''), '-');

        $input = $this->normalize($input, $setDefaults = true);

        // Check data completition
        $invalid = $this->invalid($input);

        if ($invalid === true) {

            return [
                'error' => "Invalid dataset",
                'input' => $input,
            ];
        }

        // Check domain id
        $domain = $this->getDomain($input['domains_id']);

        if ($domain->id === 0) {

            return [
                'error' => "Specified domain does not exist",
            ];
        }

        // Check same file path
        if ($this->exists(' basename LIKE ? AND path LIKE ? AND domains_id = ? AND id != ? ', [$input['basename'], $input['path'], $input['domains_id'], $id]) === true) {

            return [
                'error' => "File {$input['path']}/{$input['basename']} already exists in database",
            ];
        }

        // Upload file chunks
        $upload = $this->upload($input);

        if (ark($upload, 'error', false) !== false) {

            return [
                'error' => $upload['error'],
            ];
        }

        if (ark($upload, 'finalChunk', false) === true) {

            unset(
                $input['token'],
                $input['finalChunk'],
            );

            $input = $this->extend($input, 'update');
            $updated = $this->assets->update($id, $input);

            if ($updated > 0) {

                // Save finilaized file into db
                return [
                    'updated' => $updated,
                    'uploaded' => $upload,
                ];
            }

        } else {

            // Chunk uploaded
            return [
                'uploaded' => $upload,
            ];
        }

        return [
            'error' => 'Some SQL error'
        ];
    }


    /**
    * Partial update for existing file
    * @param int $id
    * @param array $input
    * @return array
    */
    public function patch($id, $input): array
    {
        $input = $this->normalize($input);

        // Check data completition
        $invalid = $this->invalid($input);

        if ($invalid === true) {

            return [
                'error' => "Invalid dataset",
                'input' => $input,
            ];
        }

        if (ark($input, 'domains_id', false) !== false) {

            // Check domain id
            $domain = $this->getDomain($input['domains_id']);

            if ($domain->id === 0) {

                return [
                    'error' => "Specified domain does not exist",
                ];
            }
        }

        if (ark($input, 'name', false) !== false) {

            // Check same nav name and exclude updated id
            $exists = $this->exists(' name LIKE ? AND id != ? ', [$input['name'], $id]);

            if ($exists === true) {

                return [
                    'error' => "File with name {$input['name']} already exists",
                ];
            }
        }

        $input = $this->extend($input, 'update');

        return [
            'patched' => $this->assets->update($id, $input),
        ];
    }


    /**
    * Upload new file
    * @param array $input
    * @return array
    */
    public function upload($input): array
    {
        // POST params
        $finalChunk = ark($input, 'finalChunk', 1) ;
        $basename = ark($input, 'basename', '');
        $origin = ark($input, 'origin', '');
        $path = $this->s2unc(ark($input, 'path', '')); // Domain relative path
        $token = ark($input, 'token', '');
        $domains_id = ark($input, 'domains_id', '');
        $fsRoot = $this->app->config('fsRoot');

        // FILE params
        $chunkFileName = ark($this->file, 'name', '');
        $tmpFilePath = ark($this->file, 'tmp_name', '');
        $errorCode = ark($this->file, 'error', -1);
        $chunkSize = (int)ark($this->file, 'size', 0);

        // Native error
        $error = $this->getNativeUploadErrors($errorCode);

        if (count($this->file) >= 5
            && mb_strlen($chunkFileName) > 0
            && $errorCode === 0
            && $this->isFile($tmpFilePath)
            && $chunkSize > 0
        ) {

            $uploadDir = $this->storageService->domainPublicDir($domains_id) . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR . $path;
            $tempDir = $this->storageService->domainTempDir($domains_id) . DIRECTORY_SEPARATOR . 'uploads';
            $cacheDir = $this->storageService->domainCacheDir($domains_id) . DIRECTORY_SEPARATOR . $path;

            $uploadFile = $uploadDir. DIRECTORY_SEPARATOR . $basename;
            $tempFile = $tempDir. DIRECTORY_SEPARATOR . $basename . '.' . $token . '.temporary';

            $this->makeDir($uploadDir);
            $this->makeDir($tempDir);
            $this->makeDir($cacheDir);

            if ($this->isDir($uploadDir) && $this->isDir($tempDir)) {

                if ($this->isFile($tempFile)) {

                    // This requires rework, memory load on large chunks!!!!
                    $errror = $this->appendFile($tempFile, $this->readFile($tmpFilePath)) ? '': 'Error appending file chunk from temporary directory to upload directory';
                } else {

                    $errror = $this->copyFile($tmpFilePath, $tempFile) ? '': 'Error moving file chunk from temporary directory to upload directory';
                }

                if ($finalChunk == 1) {

                    $error = $this->moveFile($tempFile, $uploadFile) ? '': 'Error moving final file from temporary directory to upload directory';
                }

                return [
                    'uploaded' => true,
                    'file' => $this->file,
                    'basename' => $basename,
                    'uploadDir' => $uploadDir,
                    'input' => $input,
                    'finalChunk' => (bool)$finalChunk,
                ];
            } else {

                $error = $this->isDir($uploadDir) ? 'Temp directory error': 'Upload directory error';
            }
        }

        return [
            'error' => $error,
            'file' => $this->file,
            'finalChunk' => (bool)$finalChunk,
         ];
    }


    /**
    * Clear file cache files
    * @param string $file
    * @param string $cacheDir
    * @return array
    */
    protected function fileClearCache($file, $cacheDir): void
    {
        $this->thumbService->delete($file, $cacheDir);
    }


    /**
    * Post action on uploaded file
    * @param string $file
    * @param string $cacheDir
    * @return array
    */
    protected function filePostAction($file, $cacheDir): void
    {
        $this->thumbService->create($file, $cacheDir);
    }


    /**
    * Delete existing file
    * @param int $id
    * @return array
    */
    public function delete($id): array
    {
        // Check file id
        $exists = $this->getFile($id);

        if ($exists->id === 0) {

            return [
                'error' => "Specified file does not exist",
                'status' => false,
            ];
        }

        // Check domain id
        $domain = $this->getDomain($exists->domains_id);

        if ($domain->id === 0) {

            return [
                'error' => "Specified domain does not exist",
                'status' => false,
            ];
        }

        $fullPath = $this->storageService->domainContentDir($exists->domains_id) . DIRECTORY_SEPARATOR . $exists->path . DIRECTORY_SEPARATOR . $exists->basename;

        $cacheDir = $this->storageService->domainCacheDir($exists->domains_id) . DIRECTORY_SEPARATOR . $exists->path;


        $this->fileClearCache($fullPath, $cacheDir);

        $storageDel = $this->deleteFile($fullPath);

        if ($storageDel === false) {

            return [
                'error' => "Storage file {$fullPath} deletion error",
                'status' => false,
            ];
        }

        $dbDel = $this->assets->delete($id);

        return [
            'deleted' => $dbDel,
            'status' => $dbDel > 0 ? true: false,
        ];
    }


    /**
    * Native error code translation
    * @param int $code
    * @return string
    */
    protected function getNativeUploadErrors($code): string
    {
        return ark($this->nativeErrorMessages, $code, 'A very very very strange flying cow');
    }


    protected function getFile($id)
    {
        $exists = $this->existsWithData(' id = ? ', [$id]);
        return $exists;
    }


    protected function getDomain($id)
    {
        $temporaryType = $this->assets->swapType('domains');
        $exists = $this->existsWithData(' id = ? ', [$id]);
        $this->assets->swapType($temporaryType);
        return $exists;
    }
}