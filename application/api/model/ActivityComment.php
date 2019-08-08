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
                $obj = ActivityComment::get($id);
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
            //获取所有评论
            $allcomments = [];

            //针对某个活动的所有评论
            if($_REQUEST['scheduleId'] == 0)
            {
                //获取所有评论的根
                $comments = ActivityComment::where(['is_delete'=>0,
                    'activity_id'=>$_REQUEST['activityId']
                ])->order('create_time')->select();

                foreach ($comments as $item)
                {
                    array_push($allcomments,$item->data);

//                    $subcomments = $this->getAllComments($item->data);
//                    foreach ($subcomments as $subitem)
//                        array_push($allcomments,$subitem);
                }

            }
            //针对某个行程的所有评论
            else
            {
                //获取所有评论的根
                $comments = ActivityComment::where(['is_delete'=>0,
                    'activity_id'=>$_REQUEST['activityId'],
                    'schedule_id'=>$_REQUEST['scheduleId']
                ])->order('create_time')->select();

                foreach ($comments as $item)
                {
                    array_push($allcomments,$item->data);

//                    $subcomments = $this->getAllComments($item->data);
//                    foreach ($subcomments as $subitem)
//                        array_push($allcomments,$subitem);
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


    public function getMyComment()
    {
        try
        {
            //获取所有评论
            $allcomments = [];
            //一个父级评论下的所有评论
            $subcomments =[];

            //针对某个活动的所有评论
            if($_REQUEST['scheduleId'] == 0)
            {
                //获取所有评论的根
                $comments = ActivityComment::where(['is_delete'=>0,
                    'activity_id'=>$_REQUEST['activityId'],
                    'parent_id'=>0
                ])->order('create_time')->select();

                foreach ($comments as $item)
                {
                    array_push($allcomments,$item->data);

                    $subcomments = $this->getAllComments($item->data);
                    foreach ($subcomments as $subitem)
                        array_push($allcomments,$subitem);
                }

            }
            //针对某个行程的所有评论
            else
            {
                //获取所有评论的根
                $comments = ActivityComment::where(['is_delete'=>0,
                    'activity_id'=>$_REQUEST['activityId'],
                    'schedule_id'=>$_REQUEST['scheduleId'],
                    'parent_id'=>0
                ])->order('create_time')->select();

                foreach ($comments as $item)
                {
                    array_push($allcomments,$item->data);

                    $subcomments = $this->getAllComments($item->data);
                    foreach ($subcomments as $subitem)
                        array_push($allcomments,$subitem);
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

    /**
     * Notes:删除评论
     * @return array
     * author: Fei
     * Time: 2019/7/16 16:32
     */
    public function delComment()
    {
        try
        {
            $comment = new ActivityComment();
            $comment->where('id',$_REQUEST['id'])->update(['is_delete' => 1]);
            if($comment->error)
            {
                return array('code' => 1000,
                    'data' => array(),
                    'message'=> '删除活动评论成功！');

            }
            else
            {
                return array('code' => 3000,
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
     * Notes:编辑活动评论
     * @return array
     * author: Fei
     * Time: 2019/7/16 16:37
     */
    public function editComment()
    {
        try
        {
            $comment = new ActivityComment();
            $resval = $comment->validate(
                [
                    'comment_content'=>'require'
                ],
                [
                    'comment_content.require'=>'评论内容不能为空！'
                ]
            )->where('id',$_REQUEST['id'])
                ->where('is_delete',0)
                ->update(['comment_content' => $_REQUEST['comment_content']]);
            if($resval)
            {
                if($comment->error)
                {
                    return array('code' => 1000,
                        'data' => array(),
                        'message'=> '编辑活动评论成功！');

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
     * Notes:获取个评论的回复
     * @param $comment
     * @param array $comments
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * author: Fei
     * Time: 2019/7/16 16:29
     */
    private function getAllComments($comment,$comments = [])
    {
        if($_REQUEST['scheduleId'] == 0)
        {
            $soncomments = ActivityComment::where(['is_delete'=>0,
                'activity_id'=>$_REQUEST['activityId'],
                'parent_id'=>$comment['id']])->order('create_time')->select();

            if(count($soncomments) > 0)
            {
                foreach ($soncomments as $item)
                {
                    array_push($comments,$item->data);
                    $this->getAllComments($item->data,$comments);
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

            if(count($soncomments) > 0)
            {
                foreach ($soncomments as $item)
                {
                    array_push($comments,$item->data);
                    $this->getAllComments($item->data,$comments);
                }
            }
            else
                return $comments;
        }

    }
}
