<?php
/**
 * Create by: yufei
 * Date: 2019/7/16
 * Time: 16:48
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\model;


use Snowflake;
use think\Model;

class ActivityAttach extends Model
{
    /**
     * Notes:添加文件
     * @return array
     * author: Fei
     * Time: 2019/7/16 17:02
     */
    public function addAttach()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $attach = new ActivityAttach();
            $fileurls = $_REQUEST['fileurls'];
            $attachs = [];
            $successflg = false;
            $attach->startTrans();
            foreach ($fileurls as $url)
            {
                $id = Snowflake::getsnowId();
                $resval = $attach->validate(
                    [
                        'activity_id'=>'require',
                        'file_url' => 'require'
                    ],
                    [
                        'activity_id.require'=>'活动编号不能为空！',
                        'file_url.require'=>'文件url不能为空！'
                    ]
                )->save(['id'=>$id,
                    'activity_id'  => $user['data']['id'],
                    'schedule_id' => $_REQUEST['schedule_id'],
                    'attach_type' => $_REQUEST['attach_type'],
                    'attach_explain' => $_REQUEST['attach_explain'],
                    'file_url' => $url,
                    'creator' => $user['data']['id']
                ]);
                $obj = ActivityAttach::get($id);
                array_push($attachs,$obj->data);
                if(!$resval)
                {
                    $successflg = false;
                    break;
                }
                $successflg = true;
            }

            if($successflg)
            {
                $attach->commit();
                return array('code' => 1000,
                    'data' => $attachs,
                    'message'=> '活动点赞成功！');
            }
            else
            {
                $attach->rollback();
                return array('code' => 3000,
                    'data' => array(),
                    'message'=> $attach->error);
            }

        }
        catch (\Exception $e)
        {
            return array('code' => 2000,
                'data' => array(),
                'message'=> $e->getMessage());
        }
    }

}
