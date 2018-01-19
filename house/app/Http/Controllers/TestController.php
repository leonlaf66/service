<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function mlsDetailFieldLangs (Request $req)
    {
        $code = $this->getOriginNames();
        $code = str_replace('array (', '[', $code);
        $code = str_replace('),', ']', $code);
        echo $code;
        exit;
    }

    protected function getOriginNames() {
        $items = app('db')->table('house_field_prop_rule')
            ->select('xml_rules')
            ->get();

        $titleLangs = $this->getZhTitles();

        $allNames = [];
        foreach ($items as $item) {
            $xmlRules = $item->xml_rules;
            $xml = simplexml_load_string("<groups>{$xmlRules}</groups>");
            $groups = $xml->xpath('/groups/group');
            foreach($groups as $group) {
                $items = $group->xpath('items');
                foreach($items[0] as $item) {
                    $name = $item->getName();
                    $title = (string)$item->title[0];
                    if (!in_array($title, $allNames)) {
                        $allNames[$name] = [
                          'zh-CN' => [
                              'title' => $titleLangs[$title] ?? ''
                          ]
                        ];
                    }
                }
            }
        }

        return var_export($allNames, true);
    }

    protected function getZhTitles() {
        $sql = <<<EOT
select s.message, t."translation"
from i18n_source_message s
  inner join i18n_message t on s."id"=t."id" and t."language"='zh-CN'
where s.category = 'rets'
EOT;

        $rows = app('db')->select($sql);

        $names = [];
        foreach ($rows as $row) {
            $title = $row->message;
            $titleCn = $row->translation;
            $names[$title] = $titleCn;
        }

        return $names;
    }
}
