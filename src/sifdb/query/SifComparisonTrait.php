<?php
/**
 * Created by PhpStorm.
 * User: Sebastian
 * Date: 24.03.2018
 * Time: 21:30
 */

namespace sifdb\query;


trait SifComparisonTrait
{
    protected $compareAliases = [
        'ex' => 'Exists',
        'exists' => 'Exists',

        'nex' => 'NotExists',
        '!ex' => 'NotExists',
        '!exists' => 'NotExists',

        '=' => 'Equals',
        'eq' => 'Equals',

        '==' => 'StrictEquals',
        '===' => 'StrictEquals',

        '!=' => 'NotEquals',
        '<>' => 'NotEquals',
        'ne' => 'NotEquals',

        '>' => 'GreaterThan',
        'gt' => 'GreaterThan',

        '>=' => 'GreaterThanEquals',
        'gte' => 'GreaterThanEquals',

        '<' => 'LessThan',
        'lt' => 'LessThan',

        '<=' => 'LessThanEquals',
        'lte' => 'LessThanEquals',

        'like' => 'Like',
        '%' => 'Like',

        'nlike' => 'NotLike',
        '!like' => 'NotLike',
        '!%' => 'NotLike',

        'regex' => 'Regex',
        'r' => 'Regex',

        'in' => 'In',
        'nin' => 'NotIn',
        '!in' => 'NotIn',

        'fn' => 'Custom',
        'function' => 'Custom',
        'custom' => 'Custom',
        'where' => 'Custom',
    ];

    protected function conditionsRight($conditions = [], $data = [])
    {
        $fulfilled = $conditions;
        for ($i = 0; $i < count($conditions); $i++) {
            if (!is_array($conditions[$i][0])) {
                $fulfilled[$i] = $this->callCompareFunction($conditions[$i][0], $conditions[$i][1], $conditions[$i][2], $data);
            } else {
                for ($j = 0; $j < count($conditions[$i]); $j++) {
                    $fulfilled[$i] = $this->callCompareFunction($conditions[$i][$j][0], $conditions[$i][$j][1], $conditions[$i][$j][2], $data);
                    if ($fulfilled[$i]) break;
                }
            }
            if (!$fulfilled[$i]) break;
        }
        return count($conditions) == count(array_filter($fulfilled));
    }

    protected function callCompareFunction($attrName = '', $attrConditionVal = null, $attrCompare = null, $data = [])
    {
        if (method_exists($this, "compare{$this->compareAliases[$attrCompare]}"))
            return (boolean)call_user_func_array(
                [$this, "compare{$this->compareAliases[$attrCompare]}"],
                [
                    $data[$attrName],
                    $attrConditionVal,
                    $attrCompare
                ]
            );
        else return false;
    }

    /**
     * Compare Functions
     *
     * @param $val
     * Value of input data
     *
     * @param $comp
     * Value to compare with
     *
     * @param $fn
     * Compare function alias or comparing callable that takes 2 params ($val, $comp)
     *
     * @return bool
     */

    protected function compareExists($val, $comp, $fn) {return !empty($val);}

    protected function compareNotExists($val, $comp, $fn) {return empty($val);}

    protected function compareEquals($val, $comp, $fn)
    {
        if (is_string($val) && is_int($comp)) return strlen($val) == $comp;
        return $val == $comp;
    }

    protected function compareStrictEquals($val, $comp, $fn) {return $val === $comp;}

    protected function compareNotEquals($val, $comp, $fn)
    {
        if (is_string($val) && is_int($comp)) return strlen($val) != $comp;
        return $val != $comp;
    }

    protected function compareGreaterThan($val, $comp, $fn)
    {
        if (is_string($val) && is_string($comp)) return strlen($val) > strlen($comp);
        if (is_string($val) && is_int($comp)) return strlen($val) > $comp;
        return $val > $comp;
    }

    protected function compareGreaterThanEquals($val, $comp, $fn)
    {
        if (is_string($val) && is_string($comp)) return strlen($val) >= strlen($comp);
        if (is_string($val) && is_int($comp)) return strlen($val) >= $comp;
        return $val >= $comp;
    }

    protected function compareLessThan($val, $comp, $fn)
    {
        if (is_string($val) && is_string($comp)) return strlen($val) < strlen($comp);
        if (is_string($val) && is_int($comp)) return strlen($val) < $comp;
        return $val < $comp;
    }

    protected function compareLessThanEquals($val, $comp, $fn)
    {
        if (is_string($val) && is_string($comp)) return strlen($val) <= strlen($comp);
        if (is_string($val) && is_int($comp)) return strlen($val) <= $comp;
        return $val <= $comp;
    }

    protected function compareCustom($val, $comp, $fn) {return call_user_func_array($fn, [$val, $comp]);}

    protected function compareIn($val, $comp, $fn) {return in_array($comp, $val);}

    protected function compareNotIn($val, $comp, $fn) {return !in_array($comp, $val);}

    protected function compareLike($val, $comp, $fn) {return preg_match("/($comp)/", $val) === 1;}

    protected function compareNotLike($val, $comp, $fn) {return preg_match("/($comp)/", $val) === 0;}

    protected function compareRegex($val, $comp, $fn) {return preg_match($comp, $val) === 1;}
}