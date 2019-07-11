<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:66:"D:\xampp\htdocs\yqb\public/../application/api\view\users\test.html";i:1552292227;}*/ ?>
<!DOCTYPE html>
<html lang="zh_CN">
<head>
    <meta charset="UTF-8">
    <title>请求测试</title>
</head>
<script type="text/javascript" src="<?php echo \think\Config::get('PUBLIC'); ?>/jquery.1.10.2.js"></script>
<body>
<a href="javascript:void(0)"  plain="true" onclick="request()">请求</a>
</body>
<script>
    function request() {
    $.ajax({
        type:"POST",
        url:"<?php echo url('api/users/adduser'); ?>",
        data:{

            //errMsg: "getUserInfo:ok",
            //rawData: '{"nickName":"溯游","gender":1,"language":"zh_CN","ci…cG2BRibQxgmoJ1eNNInUdAfKibgm3GEjqp1iaIQbiag/132"}',
            // signature: "9806cf6dc485304a79e75157aa3f1f157dd1a4cd",
             iv:"lsSiPbkszxYZV6jGe/MMIQ==",
            // rawData:'{"nickName":"溯游","gender":1,"language":"zh_CN","city":"Hanzhong","province":"Shaanxi","country":"China","avatarUrl":"https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTJVUWsJ82Jp6Ux0ZUiahwO7s4F3Vm16tyuCUsOlcG2BRibQxgmoJ1eNNInUdAfKibgm3GEjqp1iaIQbiag/132"}',
             encryptedData: "EHfVmr+dIb3D+ewwNRDQt+Kz0Ri1V75Gwap+QeIcf2WeCrGWL9U4KkleAImj67n7sXu/jqoAB6oEeZQVfUovNnHyM5JNs2VxeqWeFKtuXCdQg9EYYHrAM2d6zAMcugcNAI5Gsi9A5aXdSZYK/2EGfpaE75ULwLnuoogmbhqvI1RCTrllI2nxS3bpsrBq7YgWPDpG+zGUZ6tFqOqnkq9FAFAxREjcDsuzbd4axdCpNRN018y8vqELykQlhee+WiRormDLIfd5H6EzPrGEMtz/YOABdK8Xsj//R931fbnSNje0GXqEW5pAJTaw6jw8X24bXutYdRHT/ukhVmqX8jhFvrlEH5ojARIroixRkwLvHlmUVBQiOO0HgRNiVWBMNoHkkATjjnDGhXapinSUmlMZJ8nG76z7MEo5bbRGsatc7LG1G2fqEjd8mJKT2RvqSqaDWcdqWR5E/IBI09bl7nwc7feAy0CyOE/mims+fKL8yQg=",
            //code:"001M3Y6N1ODZE71rN75N1Rd47N1M3Y6r"

            // iv:"",
            // encryptedData:""


            },
        success:function(data){
            var res = $.parseJSON(data);
            if(res.status == "1")
            {
                alert("保存成功！")
            }
            else
            {
                alert(res.message);
            }

        }
    });
}
</script>
</html>
