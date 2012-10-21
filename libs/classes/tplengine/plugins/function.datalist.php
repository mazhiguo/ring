<?php
function smarty_function_datalist($params, &$smarty)
{
    extract($params);

    $s = '';
    foreach($list as $i)
    {
        $s .= '<tr>';
        $s .= '<td class="list_c">'.$i['id'].'</td>';

        foreach($fields as $f)
        {
            $name = $i[$f['name']];

            if ($f['type'] == 'file')
            {
                $s .= '<td class="list_c"><a href="/models/downfile?file='.base64_encode($name).'">'.basename($name).'</a></td>';
            }
            else if ($f['type']=='radio' || $f['type']=='select')
            {
                $vs_arr = explode('|', $f['values']);
                $_v = '';
                foreach($vs_arr as $vs)
                {
                    list($k, $v) = explode('=', $vs);
                    if (!isset($k) || !isset($v)) continue;
                    if ($k == $name)
                    {
                  		$_v = $v;
                        break;
                    }
                }
                $s .= '<td class="list_c">'.$_v.'</td>';
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
                $s .= '<td class="list_c">'.implode('、', $s_arr).'</td>';
            }
            else
            {
                $s .= '<td class="list_c">'.$name.'</td>';
            }
        }

        $status = $i['status']==0 ? '草稿' : ($i['status']==1?'正式':'问题数据');
        $as = $i['status']!=2 ? '<a href="/models/updstatus?cid='.$col['id'].'&mid='.$mid.'&id='.$i['id'].'&status=2">问题数据</a>' : '';

        $s .= <<<HTML
<td class="list_c">$i[creator]</td>
<td class="list_c">$i[create_time]</td>
<td class="list_c">$status</td>
<td class="list_c">
	<a href="/models/deldata?cid=$col[id]&mid=$mid&id=$i[id]">删除</a>
	<a href="/models/showupd?cid=$col[id]&mid=$mid&id=$i[id]">编辑</a>
    $as
</td>
HTML;
    }

    return $s;
}

?>
