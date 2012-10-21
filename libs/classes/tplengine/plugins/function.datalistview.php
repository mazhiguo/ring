<?php
function smarty_function_datalistview($params, &$smarty)
{
    extract($params);

    $s = '';
    foreach($list as $i)
    {
        $s .= '<div style="padding:5px;margin-bottom:10px;background:#eee;">';
        foreach($fields as $f)
        {
            $s .= '<div style="line-hegiht:22px;">';
            $s .= '<label style="width:70px;display:inline-block;">'.$f['desc'].'</label>';

            $name = $i[$f['name']];

            if ($f['type'] == 'file')
            {
                $s .= '<a href="/models/downfile?file='.base64_encode($name).'" title="'.basename($name).'">[点击下载]</a>';
            }
            else if ($f['type']=='radio' || $f['type']=='select')
            {
                $vs_arr = explode('|', $f['values']);
                foreach($vs_arr as $vs)
                {
                    list($k, $v) = explode('=', $vs);
                    if (!isset($k) || !isset($v)) continue;
                    if ($k == $name)
                    {
                        $s .= $v;
                        break;
                    }
                }
            }
            else if ($f['type'] == 'checkbox')
            {
                $vs_arr = explode('|', $f['values']);
                $s_arr = array();
                foreach($vs_arr as $vs)
                {
                    list($k, $v) = explode('=', $vs);
                    if (!isset($k) || !isset($v)) continue;

                    $name_arr = explode(',', $name);
                    if (in_array($k, $name_arr))
                    {
                        $s_arr[] = $v;
                        continue;
                    }
                }
                $s .= implode('、', $s_arr);
            }
            else
            {
                $s .= $name;
            }
            $s .= '</div>';
        }
        $s .= '</div>';
    }

    return $s;
}

?>
