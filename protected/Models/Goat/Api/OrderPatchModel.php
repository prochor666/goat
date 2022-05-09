<?php
namespace Goat\Api;

use GoatCore\GoatCore;
use GoatCore\DbAssets\DbAssets;
use Goat\BasicAssetModel;

/**
* OrderPatchModel - Ordering items in collections
*
* @author Jan Prochazka aka prochor <prochor666@gmail.com>
* @version 1.0
*/
class OrderPatchModel extends BasicAssetModel
{
    protected $allowed;

    public function __construct(GoatCore $app, DbAssets $assets)
    {
        parent::__construct($app, $assets);

        $this->predefined = [
            'collection' => [
                'validation_method'    => 'array',
                'default'              => [],
                'options'              => [
                    'validation_method' => 'array',
                    'empty_valid'       => false
                ]
            ],
            'type' => [
                'validation_method'    => 'string',
                'default'              => 0,
                'required'             => true,
                'options'              => false,
            ],
            'domains_id' => [
                'validation_method'    => 'int',
                'default'              => 0,
                'required'             => true,
                'options'              => ['min' => 1],
            ],
            'orderColumn' => [
                'validation_method'    => 'string',
                'default'              => 'order',
                'options'              => false,
            ]
        ];

        $this->allowed = [
            'navs',
            'pages',
            'meta',
        ];
    }


    /**
    * Partial update for existing collection
    * @param int $id
    * @param array $input
    * @return array
    */
    public function patch($input): array
    {
        $input = $this->normalize($input, $setDefaults = true);

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

        if (!in_array($input['type'], $this->allowed)) {

            return [
                'error' => "Invalid order type",
                'input' => $input,
            ];

        } else {

            $result = [];

            $temporaryType = $this->assets->swapType($input['type']);

            foreach ($input['collection'] as $key => $obj) {

                if (ark($obj, 'id', 0) > 0 && is_int(ark($obj, $input['orderColumn'], false))) {

                    $asset = $this->assets->one($obj['id']);

                    foreach($obj as $col => $val) {

                        if ($col !== 'id') {

                            $asset[$col] = $val;
                        }
                    }

                    $result[] = $this->assets->save($asset);
                }
            }
        }

        return [
            'patched' => $result,
        ];
    }


    protected function getDomain($id)
    {
        $temporaryType = $this->assets->swapType('domains');
        $exists = $this->existsWithData(' id = ? ', [$id]);
        $this->assets->swapType($temporaryType);
        return $exists;
    }
}