<?php
namespace Goat;

use GoatCore\DbAssets\DbAssets;

class Meta
{
    protected $assets;

    public $canSave;

    use \GoatCore\Traits\Validator;

    public function __construct()
    {
        $this->assets = new DbAssets('metavalues');
        $this->canSave = true;
    }


    public function validate($domains_id, $target, $metaInput)
    {
        $metaTags = $this->tags(
            $domains_id,
            $target,
        );

        $items = [];
        $this->canSave = true;
        //return ['input' => $input, 'metaTags' => $metaTags];

        foreach($metaInput as $item) {

            $tagExists = $this->filterByOrigin($item, $metaTags);

            if ($tagExists !== false) {

                $item = $this->validateRealValue($item);

                if ($item['realvalue']['value'] === false) {

                    $this->canSave = false;
                }

                array_push($items, $item);
            }
        }

        return $items;
    }


    public function save($targetid, $items): array
    {
        if ($this->canSave === true) {

            return $this->toDb($targetid, $items);
        }

        return $items;
    }


    protected function toDb($targetid, $items)
    {
        $saveStatus = [
            'created' => [],
            'updated' => []
        ];

        foreach($items as $item) {

            $item['realvalue']['targetid'] = $targetid;

            $itemSaved = $this->assets->findOneWithDefault(' tag LIKE ? AND target LIKE ? AND targetid = ? ', [$item['tag'], $item['target'], (int)$item['realvalue']['targetid']]);

            $fullItem = array_merge($item['realvalue'], [
                'tag' => $item['tag'],
                'target' => $item['target'],
            ]);

            unset($fullItem['_from'], $fullItem['id']);

            if ($itemSaved->id === 0) {

                $saveStatus['created'][$item['tag']] = $this->assets->create($fullItem);
            } else {

                $saveStatus['updated'][$item['tag']] = $this->assets->update($itemSaved->id, $fullItem);
            }
        }

        return $saveStatus;
    }


    protected function validateRealValue($item)
    {
        $options = ark($item, 'validate_options', []);

        if ($item['validate_as'] === 'int' && ctype_digit($item['realvalue']['value'])) {

            $item['realvalue']['value'] =  (int)$item['realvalue']['value'];
        }

        // This will accept nnn,nnn float value and replace the , with .
        if ($item['validate_as'] === 'float' && preg_match_all('/^[0-9,.]+$/i', $item['realvalue']['value'])) {

            $item['realvalue']['value'] = (float)str_replace(',', '.', $item['realvalue']['value']);
        }

        // This will accept nnn,nnn float value and replace the , with .
        if ($item['validate_as'] === 'numeric' && preg_match_all('/^[0-9,.\-\+]+$/i', $item['realvalue']['value'])) {

            $item['realvalue']['value'] = str_replace(',', '.', $item['realvalue']['value']);
        }

        $item['realvalue']['value'] = call_user_func_array(
            [$this, $item['validate_as']],
            [$item['realvalue']['value'], $options]
        ) ? $item['realvalue']['value']: false;

        if ($item['realvalue']['value'] === false) {

            return $item;
        }

        if ($item['type'] === 'multiple') {

            $item['realvalue']['value'] = json_encode($item['realvalue']['value']);
        }else{

            $item['realvalue']['value'] = (string)$item['realvalue']['value'];
        }

        return $item;
    }


    protected function filterByOrigin($item, $metaTags)
    {
        $exists = array_filter($metaTags, function($metaTag) use ($item)
        {
            return $item['tag'] === $metaTag->tag;
        });

        // Todo
        return count($exists) === 1 ? $item: false;
    }


    public function attach($models, $target, $tags = [])
    {
        if (!is_array($models)) {

            $models = [$models];
        }

        $tagFilter = [];

        if (count($tags) > 0) {

            $tagFilter = array_map( function(string $tag): string
            {
                return "tag|{$tag}";
            }, $tags);
        }

        if (count($models) === 0) {

            return $models;
        }

        $_domains_id = (int)$models[0]->domains_id;

        if ($_domains_id === 0) {

            return $models;
        }

        $metaTags = $this->tags(
            $_domains_id,
            $target,
            $tagFilter,
        );

        foreach ($models as $key => $model) {

            if (!is_array($models[$key]->metatags)) {

                $models[$key]->metatags = [];
            }

            foreach($metaTags as $tagObj) {

                if (gettype($tagObj->default) === 'string') {

                    $tagObj->default = json_decode($tagObj->default, true);
                }

                $tagObj->realvalue = $this->value($model, $target, $tagObj);
                $models[$key]->metatags[$tagObj->tag] = $tagObj;
            }
        }

        return $models;
    }


    protected function tags($domains_id, $target, $tagFilter = [])
    {
        $temporaryType = $this->assets->swapType('meta');

        $result = $this->assets->find([
            '_wfilter' => array_merge([
                "domains_id|{$domains_id}",
                "target|{$target}",
            ], $tagFilter),
            '_worder' => [
                "order|ASC",
                "tag|ASC",
                "id|ASC"
            ],
        ]);

        $this->assets->swapType($temporaryType);

        return $result;
    }


    protected function value($model, $target, $tagObj)
    {
        $realValue = $this->assets->findOneWithDefault(' targetid = ? AND target LIKE ? AND tag LIKE ? ', [$model->id, $target, $tagObj->tag]);
        return $this->realValuePass($realValue, $tagObj->type);
    }


    protected function realValuePass($realValue, $type)
    {
        $realValue->_from = 'default';
        $realValue->type = $type;

        if ($realValue->id > 0) {

            $realValue->_from = 'db';
        }

        switch ($realValue->type) {

            case 'multiple':

                if ($realValue->id < 1) {

                    $realValue->value = '[]';
                }

                $realValue->value = json_decode($realValue->value);
                break;

            default:

                if ($realValue->id < 1) {

                    $realValue->value = null;
                }
        }

        return $realValue;
    }
}
