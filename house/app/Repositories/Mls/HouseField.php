<?php
namespace App\Repositories\Mls;

class HouseField
{
    /**
     * 获取值
     * @param $house
     * @param $name
     * @param $opt
     * @return mixed|null|string
     */
    public function getValue($house, $name, $opt)
    {
        $value = null;

        $opt = array_merge(config('house.mls.fields.'.$name, []), $opt);
        if (is_chinese() && isset($opt['zh-CN'])) {
            $opt = array_merge($opt, $opt['zh-CN']);
        }

        $houseEntity = $house->entity->data;
        if (isset($opt['value']) && get_class($opt['value']) === 'Closure') {
            $value = ($opt['value'])($houseEntity, $house);
        } elseif (isset($opt['index'])) {
            $index = $opt['index'];
            if (substr($index, 0, 1) === '@') {
                $index = substr($index, 1);
                $value = $house->$index;
            } else {
                $value = array_get($houseEntity, $index);
            }
        } else {
            $value = array_get($houseEntity, $name);
        }

        if (isset($opt['filter'])) {
            if (is_string($opt['filter'])) {
                $value = \App\Helpers\HouseField::filter($value, $opt['filter']);
            } elseif (get_class($opt['filter']) === 'Closure') {
                $value = ($opt['filter'])($value);
            }
        }

        if (isset($opt['map']) && $opt['map'] === '1') {
            $values = explode(',', $value);
            $names = $this->getListValues($name, $house->prop_type);
            foreach ($values as $idx => $value) {
                if (isset($names[$value])) {
                    $values[$idx] = tt($names[$value]);
                }
            }
            $value = implode(',', $values);
        }

        return $value;
    }

    /**
     * 获取字段实体
     * @param $name
     * @param $opt
     */
    public function getEntity($house, $name, $opt = [])
    {
        $data = [
            'title' => '',
            'value' => $this->getValue($house, $name, $opt)
        ];

        $opt = array_merge(config('house.mls.fields.'.$name, []), $opt);
        if (is_chinese() && isset($opt['zh-CN'])) {
            $opt = array_merge($opt, $opt['zh-CN']);
        }

        // 未提供值处理
        if (empty($data['value'])) {
            $data['is_empty'] = true;
            $data['value'] = tt('Unknown', '未提供');
        }

        // format
        if (!empty($data['value']) && isset($opt['format'])) {
            \App\Helpers\HouseField::format($data, $opt);
        }

        // title
        $data['title'] = $opt['title'] ?? '';


        return $data;
    }

    /**
     * 获取字段详情列表
     * @return array
     */
    public function getDetails($house)
    {
        $propTypeId = strtolower($house->prop_type);
        $xmlContent = app('db')->table('house_field_prop_rule')
            ->select('xml_rules')
            ->where('type_id', $propTypeId)
            ->value('xml_rules');

        $arrGroups = [];
        $xml = simplexml_load_string("<groups>{$xmlContent}</groups>");
        $groups = $xml->xpath('/groups/group');
        foreach($groups as $group) {
            if (!$group->title_cn) $group->title_cn = $group->title;
            $arrGroup = [
                'title' => tt((string)$group->title, (string)$group->title_cn),
                'items' => []
            ];

            $items = $group->xpath('items');
            $fieldCount = 0;
            foreach($items[0] as $item) {
                $name = $item->getName();
                $opts = (array)$item;

                if(isset($opts['values'])) {
                    $opts['values'] = (array)$opts['values'];
                }

                if (isset($opts['zh-CN'])) {
                    $opts['zh-CN'] = (array)$opts['zh-CN'];
                    // 特殊处理，为空时将不返回空，所以。。。
                    if (isset($opts['zh-CN']['prefix']) && !is_string($opts['zh-CN']['prefix'])) {
                        $opts['zh-CN']['prefix'] = '';
                    }
                    if (isset($opts['suffix']) && !is_string($opts['suffix'])) {
                        $opts['zh-CN']['suffix'] = '';
                    }
                }

                $fieldResult = $this->getEntity($house, $name, $opts);
                if (!isset($fieldResult ['is_empty'])) {
                    unset($fieldResult['is_empty']);
                    $arrGroup['items'][$name] = $fieldResult;
                    $fieldCount ++;
                }
            }

            if ($fieldCount > 0)
                $arrGroups[] = $arrGroup;
        }

        return $arrGroups;
    }

    /**
     * 获取values
     * @param $field
     * @param $propTypeId
     * @return array
     */
    public function getListValues($field, $propTypeId)
    {
        $field = strtoupper($field);
        $propTypeId = strtolower($propTypeId);

        $items = app('db')->table('house_field_reference')
            ->select('id', 'short', 'medium','long', 'long_cn')
            ->where($propTypeId, 1)
            ->where('field', $field)
            ->get();

        $results = [];
        foreach ($items as $item) {
            $key = $item->medium && $item->medium !== 'NULL' ? $item->medium : $item->short;
            $results[$key] = [$item->long, $item->long_cn];
        }

        return $results;
    }
}