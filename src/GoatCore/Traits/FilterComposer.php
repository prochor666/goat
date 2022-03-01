<?php
namespace GoatCore\Traits;

use \RedBeanPHP\R;

/*
| $_GET request|custom array to SQL pattern filter composer
|
| => Example for basic filter: /api/find-some/?_wfilter[]=key|value
| For this example, the PDO pattern `key = ?` and array `[value]` will be generated.
|
| You can use multiple values
| ---------------------------
| => Example for multivalue filter: /api/find-some/?_wfilter[]=key|value|anothervalue|justanotherone
| For this example, the PDO pattern `key IN(?, ?, ?)` and array `[value,anothervalue,justanotherone]` will be generated.
| Thr eq(=) or like(LIKE) operator will be replaced with IN(...) operator.
|
| Adding custom operator
| ----------------------
| You can use _wop parameter for every _wfilter parameter.
|
| => Example: ... ?_wfilter=key|test&_wop=key|like
| For this example, the PDO pattern `key LIKE ?` and array `[test]` will be generated.
|
| => Example: ... ?_wfilter=key|56&_wop=key|gte
| For this example, the PDO pattern `key >= ?` and array `[56]` will be generated.
|
| => Example: ... ?_wfilter=key|10|100&_wop=key|range
| For this example, the PDO pattern `key BETWEEN ? AND ?` and array `[10,100]` will be generated.
| Remember, the range(BETWEEN) can by used for strings(CHAR, VARCHAR ...), datetimes (DATETIME ...) and numbers(INt, FLOAT, DECIMAL ...)
|
| Ordering results with _worder
| -----------------------------
|
| Simple order
| ------------
| Hint: second part is optional [ASC,DESC], ASC will be autofilled, it's default
| Example: ... &_worder=id|DESC
| For this example the snippet `ORDER BY id DESC` will be generated.
|
| Multiple order
| --------------
| Example: ... &_worder[]=id&_worder[]=name|DESC
| For this example the snippet `ORDER BY id ASC, name DESC` will be generated
|
| Limits
| ------
| For limits use _wpage and _wlimit
|
| Warning: using the limit from $_GET may be a little bit unsafe/dangerous.
|          Consider to setup some hard limits or default value in your application.
|
| => Example: ... &_wpage=3&_wlimit=10
| For this example the snippet `LIMIT 20, 10` will be added to order snippet.
|
| => Example: ... &_wlimit=15
| For this example the snippet `LIMIT 0, 15` will be added to order snippet,
| because page 1 is set by default.
| Internal default limit is 0. So, by default, there will be no limit snippet.
|
*/
trait FilterComposer {

    use Validator;

    protected $operators = [
            'eq' => '=',
            'like' => 'LIKE',
            'gt' => '>',
            'lt' => '<',
            'gte' => '>=',
            'lte' => '<=',
            'range' => ['BETWEEN', 'AND'],
        ];

    /**
    * Parse $_GET params for filtering
    * @param array $input
    * @return array
    */
    public function convertInputFilter($input): array
    {
        $filter = ark($input, '_wfilter', []);
        $order = ark($input, '_worder', ['id|DESC']);
        $page = $this->numeric(ark($input, '_wpage', false), ['min' => 1]) ? (int)$input['_wpage']: 1;
        $limit = $this->numeric(ark($input, '_wlimit', false)) ? (int)$input['_wlimit']: 0;
        $operators = $this->parseOperators(ark($input, '_wop', []));

        //echo dump($page, $limit);

        return [
            'filter' => $this->parseFilter($filter, $operators),
            'order' => $this->parseOrder($order),
            'page' => $page - 1,
            'limit' => $limit,
        ];
    }


    /**
    * Create SQL pattern from array
    * @param array $filter
    * @param array $operators
    * @return array
    */
    protected function parseFilter($filter, $operators): array
    {
        $filterCols = [];
        $filterVals = [];

        if (!is_array($filter)) {

            $filter = [$filter];
        }

        foreach ($filter as $f) {

            $f = explode('|', $f);
            $col = $f[0];

            if (mb_strlen($col)>0 && count($f) > 1) {

                unset($f[0]);
                // Rebase array with values
                $f = array_values($f);

                $operator = ark($operators, $col, 'eq');

                switch ($operator) {
                    case 'eq': case "like":

                        $SQLoperator = $this->operators[$operator];

                        // If we have multiple values
                        // insert IN operator instead of =/LIKE
                        if (count($f) > 1) {

                            $SQLSlot = " {$col} ".R::genSlots( $f, ' IN( %s ) ' );

                            foreach($f as $x) {

                                $filterVals[] = $x;
                            }

                        } else {

                            $SQLSlot = " {$col} {$SQLoperator} ?";
                            $filterVals[] = $f[0];
                        }

                        array_push($filterCols, $SQLSlot);

                        break;
                    case "gt": case "lt": case "gte": case "lte":

                        $SQLoperator = $this->operators[$operator];

                        $SQLSlot = " {$col} {$SQLoperator} ?";
                        array_push($filterCols, $SQLSlot);
                        $filterVals[] = $f[0];

                        break;

                    case "range":

                        $SQLoperator = $this->operators[$operator];

                        $SQLSlot = " {$col} {$SQLoperator[0]} ? {$SQLoperator[1]} ? ";
                        array_push($filterCols, $SQLSlot);
                        $filterVals[] = count($f) > 1 ? $f[0]: 0;
                        $filterVals[] = $f[1];

                        break;
                    default:

                        // No operator, no find option
                }
            }
        }

        return ['slots' => $filterCols, 'values' => $filterVals];
    }


    protected function parseOperators($operators): array
    {
        $operatorCols = [];

        if (!is_array($operators)) {

            $operators = [$operators];
        }

        foreach ($operators as $f) {

            $f = explode('|', $f);
            $col = $f[0];

            if (mb_strlen($col)>0) {

                $op = count($f) > 1 ? $f[1]: 'eq';
                $operatorCols[$col] = $op;
            }
        }

        return $operatorCols;
    }


    protected function parseOrder($order): array
    {
        $orderCols = [];

        if (!is_array($order)) {

            $order = [$order];
        }

        foreach ($order as $f) {

            $f = explode('|', $f);
            $col = $f[0];

            if (mb_strlen($col)>0) {

                $direction = count($f) > 1 ? $f[1]: 'ASC';
                $orderCols[$col] = " `{$col}` {$direction} ";
            }
        }

        return $orderCols;
    }

}