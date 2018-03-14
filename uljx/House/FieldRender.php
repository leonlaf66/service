<?php
namespace Uljx\House;

class FieldRender
{
    public static function process($fieldIds, $fieldRules, $house)
    {
        $returns = [];

        if (is_string($fieldIds)) {
            $fieldIds = explode(',', $fieldIds);
        }

        foreach($fieldIds as $field) {
            $field = trim($field);
            if (isset($fieldRules[$field])) {
                $fieldValueCaller = $fieldRules[$field];
                if (is_string($fieldValueCaller)) {
                    if (substr($fieldValueCaller, 0, 1) === '@') { // getFieldValue
                        $fieldValueCaller = substr($fieldValueCaller, 1);
                        $returns[$field] = $house->getFieldValue($fieldValueCaller);
                    } else { // property
                        $returns[$field] = $house->$fieldValueCaller;
                    }
                } else { // callable
                    $returns[$field] = $fieldValueCaller($house);
                }
            } else { // 直接字段
                $returns[$field] = $house->$field;
            }
        }

        return $returns;
    }
}
