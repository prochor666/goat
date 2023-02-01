<?php
namespace Goat\Api;

use GoatCore\GoatCore;
use GoatCore\DbAssets\DbAssets;
use Goat\BasicAssetModel;

/**
* DirectoryModel - directory upload API model
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/
class DirectoryModel extends BasicAssetModel
{
    use \GoatCore\Traits\Disk;

    protected $data;

    protected $storageService, $thumbService;

    public function __construct(GoatCore $app, DbAssets $assets)
    {
        parent::__construct($app, $assets);
        $this->data = [];
        $this->storageService = $this->app->store->entry('Goat\Storage');
        $this->thumbService = $this->app->store->entry('Goat\Thumbnail');
    }


    /**
    * List directory
    * @param int $domains_id
    * @param array $input
    * @return array
    */
    public function list($domains_id, $input): array
    {
        // Check domain id
        $domain = $this->getDomain($domains_id);

        if ($domain->id === 0) {

            return [
                'error' => "Specified domain does not exist",
                'data'  => [],
            ];
        }

        $path = ark($input, 'path', '');
        $check = (int)ark($input, 'check', 0);
        $dir = $this->storageService->dir($domains_id, $path, 'content');

        $result = [
            'status' => $dir['status'],
            'dirs' => [],
            'files' => [],
        ];

        $cacheDir = str_replace(
            $this->storageService->endpoint($domain->id, 'content'),
            $this->storageService->endpoint($domain->id, 'cache'),
            $dir['path']);

        $this->makeDir($cacheDir);

        foreach ($dir['dir'] as $k => $item) {

            $item['rel'] = $path !== '' ? $path.'/'.basename($item['path']): basename($item['path']);
            $item['basename'] = basename($item['path']);
            $item['http'] = $this->storageService->httpPath($item['path']);
            $item['pathOrigin'] = $dir['path'];

            if ($item['type'] === 'dir') {

                $result['dirs'][] = $item;
            } else {

                $dbItem = $this->getFile($item['basename'], $path, $domain->id);

                if ($dbItem->id === 0) {

                    $newDbFile = $this->extend([
                        'origin' => $item['basename'],
                        'dmoains_id' => $domains_id,
                        'public' => 1,
                        'basename' => $item['basename'],
                        'path' => $path,
                    ], 'create');
                    $created = $this->assets->oneToMany($domain, $this->assets->getType(), [$newDbFile]);

                    $dbItem = $this->getFile($item['basename'], $path, $domain->id);
                }

                if ($dbItem->id > 0) {

                    if ($check === 1) {

                        $this->cacheAction($item, $cacheDir);
                    }

                    $item['id'] = $dbItem->id;
                    $item['variants'] = @$this->thumbService->load($item['path'], $cacheDir);
                    $result['files'][] = $item;
                }
            }
        }

        unset($dir);
        return $result;
    }


    /**
    * Create new directory
    * @param array $input
    * @return array
    */
    public function create($input): array
    {
        $input['domains_id'] = (int)ark($input, 'domains_id', 0);

        // Check domain id
        $domain = $this->getDomain((int)ark($input, 'domains_id', 0));

        if ($domain->id === 0) {

            return [
                'error' => "Specified domain does not exist",
                'data'  => [],
            ];
        }

        $dir = $this->storageService->endpoint($input['domains_id'], 'content') . '/' . $this->storageService->enumeratePath(ark($input, 'path', ''));

        $result = $this->makeDir($dir);

        return [
            'status' => $result,
        ];
    }


    /**
    * Update(rename/move) existing directory
    * @param int $domains_id
    * @param array $input
    * @return array
    */
    public function update($domains_id, $input): array
    {
        // Check domain id
        $domain = $this->getDomain($domains_id);

        if ($domain->id === 0) {

            return [
                'error' => "Specified domain does not exist",
                'data'  => [],
            ];
        }

        $from = $this->storageService->endpoint($domains_id, 'public') . '/' . $this->storageService->enumeratePath(ark($input, 'from', ''));

        $to = $this->storageService->endpoint($domains_id, 'public') . '/' . $this->storageService->enumeratePath(ark($input, 'to', ''));

        $result = $this->moveDir($from, $to);

        return [
            'status' => $result
        ];
    }


    /**
    * Directory bulk action
    * @param int $domains_id
    * @param array $input
    * @return array
    */
    public function bulkAction($domains_id, $input): array
    {
        // Check domain id
        $domain = $this->getDomain($domains_id);
        $result = [];
        $status = true;
        $message = 'Everything ok';
        $list = [];

        if ($domain->id === 0) {

            return [
                'error' => "Specified domain does not exist " . $domains_id,
                'data'  => [],
            ];
        }

        $target = $this->storageService->endpoint($domains_id, 'content') . '/' . $this->storageService->enumeratePath(ark($input, 'target', ''));

        $files = ark($input, 'files', []);
        $dirs = ark($input, 'dirs', []);
        $action = ark($input, 'action', 'none');

        if ($this->allowBulkAction($action)) {

            if (($action === 'copy' || $action === 'move') && !$this->isDir($target)) {

                $status = false;
                $message = 'Target directory does not exist ' .$target;

            } else {

                if (is_array($dirs)) {

                    foreach ($dirs as $dir) {

                        $src = $this->storageService->endpoint($domains_id, 'content') . '/' . $this->storageService->enumeratePath($dir);

                        switch ($action) {
                            case 'copy':
                                $result[$dir] = $this->copyDir($src, $target . '/' . basename($src));
                                break;

                            case 'move':
                                $result[$dir] = $this->moveDir($src, $target . '/' . basename($src));
                                break;

                            case 'delete':
                                $result[$dir] = $this->deleteDir($src);
                                break;
                        }
                    }
                }

                if (is_array($files)) {

                    foreach ($files as $file) {

                        $src = $this->storageService->endpoint($domains_id, 'content') . '/' . $this->storageService->enumeratePath($file);

                        switch ($action) {
                            case 'copy':
                                $result[$file] = $this->copyFile($src, $target . '/' . basename($file));
                                break;

                            case 'move':
                                $result[$file] = $this->moveFile($src, $target . '/' . basename($file));
                                break;

                            case 'delete':
                                $result[$file] = $this->deleteFile($src);
                                break;
                        }
                    }
                }

                $list = $this->dirCheck($domains_id, [
                    'path' => ark($input, 'target', ''),
                    'check' => true,
                ]);
            }

        } else {

            $message = 'Unknown action' . $action;
            $status = false;
        }

        return [
            'status' => $status,
            'message' => $message,
            'result' => $result,
            'list' => $list,
        ];
    }


    protected function dirCheck($domains_id, $input): array
    {
        $list = $this->list($domains_id, $input);

        foreach ($list['dirs'] as $dir) {

            $this->list($domains_id, [
                'path' => $dir['rel'],
                'check' => true,
            ]);
        }

        return $list;
    }


    /**
    * Validate action
    * @param string $file
    * @param string $cacheDir
    * @return bool
    */
    protected function allowBulkAction($action): bool
    {
        return $action === 'copy' || $action === 'move' || $action === 'delete';
    }


    /**
    * Post action on uploaded file
    * @param string $file
    * @param string $cacheDir
    * @return array
    */
    protected function cacheAction($item, $cacheDir): void
    {
        if ($this->isImageByMime($item['mime'])) {

            $this->thumbService->create($item['path'], $cacheDir);
        }
    }


    /**
    * Delete existing directory
    * @param array $input
    * @return array
    */
    public function delete($domains_id, $input): array
    {
        // Check domain id
        $domain = $this->getDomain($domains_id);

        if ($domain->id === 0) {

            return [
                'error' => "Specified domain does not exist",
                'data'  => [],
            ];
        }

        $dir = $this->storageService->endpoint($domains_id, 'public') . '/' . $this->storageService->enumeratePath(ark($input, 'path', ''));

        $result = false; //$this->deleteDir($dir);

        return [
            'status' => $result,
        ];
    }


    protected function getFile($basename, $path, $domains_id): object
    {
        $temporaryType = $this->assets->swapType('files');
        $exists = $this->existsWithData(' basename = ? AND path = ? AND domains_id = ? ', [$basename, $path, $domains_id]);
        $this->assets->swapType($temporaryType);
        return $exists;
    }


    protected function isImageByMime($mime): bool
    {
        $allowed = [
            'image/png',
            'image/jpg',
            'image/jpeg',
            'image/gif',
        ];

        return in_array($mime, $allowed);
    }


    protected function getDomain($id): object
    {
        $temporaryType = $this->assets->swapType('domains');
        $exists = $this->existsWithData(' id = ? ', [$id]);
        $this->assets->swapType($temporaryType);
        return $exists;
    }
}