<?php
namespace GoatCore\DbAssets;

use GoatCore\GoatCore;
use \RedBeanPHP\R;

class DbAssets implements \GoatCore\Interfaces\IDbAssets
{
    protected $type;

    protected $collection;

    use \GoatCore\Traits\FilterComposer;

    public function __construct($type)
    {
        $this->type = (string)$type;
        $this->collection = [];
    }


    public function setType($type): void
    {
        $this->type = (string)$type;
    }


    public function getType(): string
    {
        return $this->type;
    }


    public function swapType($type): string
    {
        $r = $this->type;
        $this->type = (string)$type;
        return $r;
    }


    public function create($data = []): int
    {
        $asset = $this->assoc(
            $this->prepare(),
            $data
        );

        return R::store($asset);
    }


    public function save($asset): int
    {
        return R::store($asset);
    }


    public function oneToMany($relation, $prop, $data)
    {
        $listKey = ucfirst($prop);
        $listName = "own{$listKey}List";

        if (!is_array($data)) {

            $data = [$data];
        }

        foreach($data as $item) {

            $asset = $this->assoc(
                $this->prepare($prop),
                $item
            );

            $relation->$listName[] = $asset;
        }

        return R::store($relation);
    }


    public function getRelated($relation, $prop)
    {
        $prop = ucfirst($prop);
        $prop = "own{$prop}List";

        return $relation->$prop;
    }


    public function update($id, $data): int
    {
        $asset = R::loadForUpdate($this->type, $id);

        if ((int)ark($asset, 'id', 0) > 0) {

            $asset = $this->assoc($asset, $data);
            return R::store($asset);
        }

        return 0;
    }


    public function delete($id): int
    {
        $asset = R::loadForUpdate($this->type, $id);

        if ((int)ark($asset, 'id', 0) > 0) {

            return R::trash($asset);
        }

        return 0;
    }


    public function one($id): object
    {
        return R::load($this->type, $id);
    }


    public function find($input): array
    {
        $filterData = $this->convertInputFilter($input);

        $filter = ark($filterData, 'filter', []);
        $order = implode(', ', ark($filterData, 'order', []));
        $page = ark($filterData, 'page', 0);
        $limit = ark($filterData, 'limit', 0);

        $limitStr = "";
        if ($limit > 0 && $page >= 0) {

            $from = $limit*$page;
            $limitStr = " LIMIT {$from}, {$limit} ";
        }

        $orderStr = " ORDER BY {$order} {$limitStr} ";

        if (count($filter['slots']) > 0) {

            $this->collection = R::find($this->type, implode(' AND ', $filter['slots']), $filter['values'], $orderStr);
        } else {

            $this->collection = R::find($this->type, $orderStr);
        }

        return $this->list();
    }


    public function findOne($cols, $values)
    {
        return R::findOne($this->type, $cols, $values);
    }


    public function findOneWithDefault($cols, $values)
    {
        return R::findOneOrDispense($this->type, $cols, $values);
    }


    public function list()
    {
        return array_values($this->collection);
    }


    public function wipe($type = false)
    {
        R::wipe(!$type ? $this->type: $type);
    }


    public function isoDateTime()
    {
        return R::isoDateTime();
    }


    public function clearProp($obj, $prop)
    {
        $clearProp = ucfirst($prop);
        $clearProp = "own{$clearProp}";

        $obj->$prop = $obj->$clearProp;
        unset($obj->$clearProp);

        return $obj;
    }


    protected function prepare($type = false)
    {
        return R::dispense(!$type ? $this->type: $type);
    }


    protected function assoc($asset, $data)
    {
        if (is_array($data) || is_object($data)) {

            foreach($data as $prop => $value) {

                $asset->$prop = $value;
            }
        }

        return $asset;
    }
}
