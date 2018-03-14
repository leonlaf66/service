<?php
namespace App\Helpers;

class FieldRender
{
    public static function process($fieldIds, $fieldRules, $data)
    {
        $returns = [];
        if (is_string($fieldIds)) {
            $fieldIds = explode(',', $fieldIds);
        }
        foreach($fieldIds as $field) {
            if (isset($fieldRules[$field])) {
                $returns[$field] = ($fieldRules[$field])($data);
            }
        }
        return $returns;
    }
}