<?php

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
        for ($i = 0; $i < count($conditions); $i++) { // AND
            if (!is_array($conditions[$i][0])) {
                $fulfilled[$i] = $this->callCompareFunction($conditions[$i][0], $conditions[$i][1], $conditions[$i][2], $data);
            } else { // OR
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
        if (is_array($val) && is_int($comp)) return count($val) == $comp;
        if (is_array($val) && is_array($comp)) return !boolval(array_diff($val, $comp));
        return $val == $comp;
    }

    protected function compareNotEquals($val, $comp, $fn) {return !$this->compareEquals($val, $comp, $fn);}

    protected function compareStrictEquals($val, $comp, $fn) {return $val === $comp;}

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

    protected function compareIn($val, $comp, $fn)
    {
        if (is_string($comp) && (is_string($val) || is_numeric($val))) return mb_strpos($comp, (string)$val) !== FALSE;
        if (is_string($comp) && is_array($val)) {
            for ($i = 0; $i < count($val); $i++) if (mb_strpos($comp, (string)$val[$i]) === FALSE) return false;
            return true;
        }
        if (is_array($comp) && is_array($val)) {
            for ($i = 0; $i < count($val); $i++) if (!in_array($val[$i], $comp)) return false;
            return true;
        }
        return in_array($val, $comp);
    }

    protected function compareNotIn($val, $comp, $fn) {return !$this->compareIn($val, $comp, $fn);}

    protected function compareRegex($val, $comp, $fn) {return preg_match($comp, $val) === 1;}
}