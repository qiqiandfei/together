<?php
/**
 * Create by: yufei
 * Date: 2019/7/10
 * Time: 13:33
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\model;


use Snowflake;
use think\Model;

class ActivityLabel extends Model
{
    /**
     * Notes:添加活动标签
     * @return array
     * author: Fei
     * Time: 2019/7/10 13:36
     */
    public function addLabel()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $label = new ActivityLabel();
            $id = Snowflake::getsnowId();
            $resval = $label->validate(
                [
                    'label_name' => 'require',
                    'activity_id'=>'require',
                ],
                [
                    'label_name.require' => '活动标签不能为空!',
                    'activity_id.require'=>'活动编号不能为空!',
                ]
            )->save(['id' => $id,
                    'activity_id' => $_REQUEST['activityId'],
                    'label_name' => $_REQUEST['labelName'],
                    'creator' => $user['data']['id']
                    ]);
            if($resval)
            {
                $obj = ActivityLabel::get($id);
                if($obj)
                {
                    return array('code' => 1000,
                        'data' => $obj->data,
                        'message'=> '添加活动标签成功！');
                }
                else
                {
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> $label->error);
                }
            }
            else
            {
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $label->error);
            }

        }
        catch(\Exception $e)
        {
            return array('code' => 2000,
                'data' => array(),
                'message'=> $e->getMessage());
        }
    }

    /**
     * Notes:
     * @return array
     * author: Fei
     * Time: 2019/7/17 9:44
     */
    public function delLabel()
    {
        try
        {
            $label = new ActivityLabel();
            $label->where('id' , $_REQUEST['id'])->delete();
            if(empty($label->error))
            {
                return array('code' => 1000,
                    'data' => array(),
                    'message'=> '删除活动标签成功！');
            }
            else
            {
                return array('code' => 4002,
                    'data' => array(),
                    'message'=> $label->error);
            }

        }
        catch(\Exception $e)
        {
            return array('code' => 2000,
                'data' => array(),
                'message'=> $e->getMessage());
        }
    }

    /**
     * Notes:
     * @return array
     * author: Fei
     * Time: 2019/7/17 9:52
     */
    public function getLabel()
    {
        try
        {
            $label = new ActivityLabel();
            $labels = $label->where('activity_id',$_REQUEST['activity_id'])
                ->order('create_time')->select();
            if(empty($label->error))
            {
                $res = [];
                foreach ($labels as $item)
                    array_push($res,$item->data);

                if(count($res) > 0)
                {
                    return array('code' => 1000,
                        'data' => $res,
                        'message'=> '获取活动标签成功！');
                }
                else
                {
                    return array('code' => 1000,
                        'data' => array(),
                        'message'=> '暂无活动标签！');
                }
            }
            else
            {
                return array('code' => 4002,
                    'data' => array(),
                    'message'=> $label->error);
            }

        }
        catch(\Exception $e)
        {
            return array('code' => 2000,
                'data' => array(),
                'message'=> $e->getMessage());
        }
    }
}
