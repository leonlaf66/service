<?php
namespace App\Repositories\Listhub;

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

        $opt = array_mult_merge(config('house.listhub.fields.'.$name, []), $opt);
        if (is_chinese() && isset($opt['zh-CN'])) {
            $opt = array_mult_merge($opt, $opt['zh-CN']);
        }

        $houseEntity = $house->getDataEntity();
        if (isset($opt['value']) && get_class($opt['value']) === 'Closure') {
            $value = ($opt['value'])($houseEntity, $house);
        } elseif (isset($opt['index'])) {
            $index = $opt['index'];
            if (substr($index, 0, 1) === '@') {
                $index = substr($index, 1);
                $value = $house->$index;
            } else {
                $value = $houseEntity->$index;
            }
        } elseif (isset($opt['path'])) {
            $value = get_xml_text($houseEntity, $opt['path']);
        }

        if (isset($opt['filter'])) {
            if (is_string($opt['filter'])) {
                $value = \App\Helpers\HouseField::filter($value, $opt['filter']);
            } elseif (get_class($opt['filter']) === 'Closure') {
                $value = ($opt['filter'])($value);
            }
        }

        return $value;
    }

    /**
     * 获取字段实体
     * @param $name
     * @param $opt
     */
    public function getEntity($house, $name, & $opt = [])
    {
        $data = [
            'title' => '',
            'value' => $this->getValue($house, $name, $opt)
        ];

        $opt = array_mult_merge(config('house.listhub.fields.'.$name, []), $opt);
        if (is_chinese() && isset($opt['zh-CN'])) {
            $opt = array_mult_merge($opt, $opt['zh-CN']);
        }

        // 未提供值处理
        if (empty($data['value'])) {
            $data['is_empty'] = true;
            $data['value'] = tt('Unknown', '未提供');
        }

        // format
        if (!empty($data['value']) && isset($opt['format'])) {
            \App\Helpers\HouseField::format($data, $opt);
        } else {
            if (!empty($opt['prefix'])) {
                $data['prefix'] = $opt['prefix'];
            }
            if (!empty($opt['suffix'])) {
                $data['suffix'] = $opt['suffix'];
            }
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
        $xmlContent = app('db')->table('listhub_house_field_prop_rule')
            ->select('xml_rules')
            ->where('type_id', $propTypeId)
            ->value('xml_rules');

        $arrGroups = [];
        $xml = simplexml_load_string("<groups>{$xmlContent}</groups>");
        $groups = $xml->xpath('/groups/group');
        foreach($groups as $group) {
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
                if (isset($opts['flat'])) {
                    $idIdx = 0;
                    if (is_array($fieldResult['value'])) {
                        foreach ($fieldResult['value'] as $title => $value) {
                            $arrGroup['items'][$name.'_'.$idIdx] = [
                                'title' => $title,
                                'value' => $value
                            ];
                            $idIdx ++;
                        }
                        $fieldCount ++;
                    }
                } elseif (!isset($fieldResult ['is_empty'])) {
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
    public function getChineseValues($field)
    {
        return get_static_data('listhub/langs/enums/'.$field, []);
    }
}