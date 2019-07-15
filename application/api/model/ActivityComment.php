<?php
/**
 * Create by: yufei
 * Date: 2019/7/15
 * Time: 13:37
 * Copyright © 2019年 Fei. All rights reserved.
 */

namespace app\api\model;


use Snowflake;
use think\Model;

class ActivityComment extends Model
{
    /**
     * Notes:添加活动评论
     * @return array
     * author: Fei
     * Time: 2019/7/15 13:40
     */
    public function addComment()
    {
        try
        {
            $user = model('user')->getUserInfo_token($_REQUEST['token']);
            $comment = new ActivityComment();
            $id = Snowflake::getsnowId();
            $resval = $comment->validate(
                [
                    'comment_content'=>'require',
                    'activity_id'=>'require'
                ],
                [
                    'comment_content.require'=>'评论内容不能为空！',
                    'activity_id.require'=>'活动编号不能为空！'
                ]
            )->save(['id'=>$id,
                'parent_id'  => $_REQUEST['parent_id'],
                'activity_id' => $_REQUEST['activity_id'],
                'schedule_id' => $_REQUEST['schedule_id'],
                'comment_user_id' => $user['data']['id'],
                'comment_content' => $_REQUEST['comment_content'],
                'creator' => $user['data']['id']
            ]);
            if($resval)
            {
                $obj = $comment::get($id);
                if($obj)
                {
                    return array('code' => 1000,
                        'data' => $obj->data,
                        'message'=> '新增活动评论成功！');
                }
                else
                {
                    return array('code' => 3000,
                        'data' => array(),
                        'message'=> $comment->error);
                }
            }
            else
            {
                return array('code' => 4001,
                    'data' => array(),
                    'message'=> $comment->error);
            }

        }
        catch (\Exception $e)
        {
            return array('code' => 2000,
                'data' => array(),
                'message'=> $e->getMessage());
        }
    }


    /**
     * Notes:获取活动评论
     * @return array
     * author: Fei
     * Time: 2019/7/15 13:46
     */
    public function getComment()
    {
        try
        {
            $comment = new ActivityComment();

            //获取所有评论
            $allcomments = [];
            //一个父级评论下的所有评论
            $subcomments =[];

            //针对某个活动的所有评论
            if($_REQUEST['scheduleId'] == 0)
            {
                //获取所有评论的根
                $comments = $comment->where(['is_delete'=>0,
                    'activity_id'=>$_REQUEST['activityId'],
                    'parent_id'=>0
                ])->order('create_time')->select();

                foreach ($comments as $item)
                {
                    $subcomments = $this->getAllComments($item->data,array());
                    array_push($allcomments,$subcomments);
                }
            }
            //针对某个行程的所有评论
            else
            {
                //获取所有评论的根
                $comments = $comment->where(['is_delete'=>0,
                    'activity_id'=>$_REQUEST['activityId'],
                    'schedule_id'=>$_REQUEST['scheduleId'],
                    'parent_id'=>0
                ])->order('create_time')->select();

                foreach ($comments as $item)
                {
                    $subcomments = $this->getAllComments($item->data,$subcomments);
                    array_push($allcomments,$subcomments);
                }
            }
            if(count($allcomments) > 0)
            {
                return array('code' => 1000,
                    'data' => $allcomments,
                    'message'=> '获取评论成功！');
            }
            else
            {
                return array('code' => 1000,
                    'data' => array(),
                    'message'=> '该活动或行程下暂无评论！');
            }

        }
        catch (\Exception $e)
        {
            return array('code' => 2000,
                'data' => array(),
                'message'=> $e->getMessage());
        }
    }

    private function getAllComments($comment,$comments = [])
    {
        if($_REQUEST['scheduleId'] == 0)
        {
            $soncomments = ActivityComment::where(['is_delete'=>0,
                'activityId'=>$_REQUEST['activity_id'],
                'parent_id'=>$comment['id']])->order('create_time')->select();

            if($soncomments)
            {
                foreach ($soncomments as $item)
                {
                    array_push($comments,$item->data);
                    getAllComments($item->data->data,$comments);
                }
            }
            else
                return $comments;
        }
        else
        {
            $soncomments = ActivityComment::where(['is_delete'=>0,
                'activity_id'=>$_REQUEST['activityId'],
                'schedule_id'=>$_REQUEST['scheduleId'],
                'parent_id'=>$comment['id']])->order('create_time')->select();

            if($soncomments)
            {
                foreach ($soncomments as $item)
                {
                    array_push($comments,$item->data);
                    getAllComments($item->data->data,$comments);
                }
            }
            else
                return $comments;
        }

    }
}
