<?php
// 防止非法调用
/*
if (! defined ( 'IN_VIPSHOP' )) {
	die ( 'It Is Forbitten' );
}
*/
session_start();
$session_id = session_id();//获取session_id

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link href="bootstrap/css/bootstrap.min.css"
	rel="stylesheet" media="screen">
<title>图片上传</title>
<script src="bootstrap/js/jquery1.7.2.js"></script>
<script type="text/javascript" src="SWFUpload/swfupload.js"></script>
<script type="text/javascript"
	src="SWFUpload/plugins/swfupload.queue.js"></script>
<script type="text/javascript"
	src="SWFUpload/plugins/swfupload.speed.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
	<div class="container-fluid">
		<div class="row-fluid">
			<div class="span6">
				<div class="form-actions">
					<button type="button" class="btn" id="swfu-placeholder">选择上传文件</button>
					<button type="submit" class="btn btn-primary" id="upload_btn">上传图片</button>
					<button type="button" class="btn btn-danger" id="cancle_all">清空上传列表</button>
					<span class="label label-important">一次最多上传<b id="max_num">50</b>
					个文件，文件大小<bid="max_size">2M</b>，文件类型：<b id="upload_file_type">*.jpg;*.gif;*.png;*.swf</b>
					</span>
				</div>
				    <div class="page-header">
                      <h4>文件上传列表 <small>上传成功：</small><span class="label label-success" id="upload_img_sucess_num">0</span>
                      <small>上传失败：</small><span class="label label-important" id="upload_img_failure_num">0</span>
                      <small>等待上传：</small><span class="label label-info" id="upload_img_wait_num">0</span>
                      </h4>
                      
                    </div>
                    <div class="page-header" style="height: 450px; overflow: auto;">
                        <ul id="logList"></ul>
                    </div>
			</div>

		</div>
	</div>
</body>
<script type="text/javascript">
var swfu = null;
var img_name_arr = [];//记录上传选择的图片名称，排除重命名图片
var upload_img_num = 0;//记录上传文件数
var upload_img_sucess_num = 0;//记录上传成功的图片数
var upload_img_failure = 0;//记录上传失败的图片数
var upload_img_wait = 0;//当前等待上传的图片
/*
 * 添加文件--过滤相同名称文件
 */
function fileQueued(file) {
    var swfUpload = this;
        //console.log(file.id+":"+file.name);
        if ($.inArray(file.name, img_name_arr) != -1) {
        	swfUpload.cancelUpload(file.id,false);//存在同名的就去除新添加进来的图片
        	alert(file.name+'文件已经存在上传队列中！');
        }
        else {
        	var listItem = '<li id="' + file.id + '">';
            listItem += '<h6>文件:' + file.name + '<small>(文件大小:' + Math.round(file.size/1024) + ' KB)&nbsp;</small>';
            listItem += '<span class="label" id="upload_status_'+file.id+'">等待上传</span>';
            listItem += '<span class="cancel" id="upload_cancel_'+file.id+'"><input type="button" class="btn btn-link" value="删除" /></span></h6>';
            listItem += '<div class="progress" style="width:50%"><div class="bar" id="upload_progress_'+file.id+'"></div></div>'
            listItem += '</li>';

            upload_img_wait ++;//等待计数器加1
            $("#upload_img_wait_num").html(upload_img_wait);
            //console.log(file.name);
            img_name_arr.push(file.name);
            $("#logList").append(listItem);
            //删除准备上传的文件
            $("li#" + file.id + " .cancel").click(function() {
                //console.log(img_name_arr);
            	swfUpload.cancelUpload(file.id,false);
            	if (img_name_arr.length > 0) {
                	$.each(img_name_arr, function(key, val){
                    	if (val == file.name) {
                        	img_name_arr.splice(key, 1);//删除数组对应的值
                        }
                    });
                }
            	if (upload_img_wait > 0) {
        	        upload_img_wait --;//等待计数器减1
                    $("#upload_img_wait_num").html(upload_img_wait);
                }
                //console.log(img_name_arr);
                $("li#" + file.id).slideUp('fast');
            }) 
        }
}
/**
 * 添加文件出错
 */
function fileQueueError(file,error,message) {
	alert('fileQueueError');
} 

/**
 * 文件开始上传时触发
 */
 function uploadStart(file) {
     if (file) {
    	 $("#upload_status_"+file.id).html("上传中...");
         $("#upload_progress_"+file.id).html("0%");
     }
 }
 /**
 *关闭文件选择框后触发的事件
 */
 function fileDialogComplete(numSelected,numQueued,numTotalInQueued) {
	    //var swfupload = this;
	    //console.log(numSelected);
	    //console.log(numQueued);
	    //console.log(numTotalInQueued);
	    //console.log(swfuOption.file_upload_limit);
	    if (numSelected > swfuOption.file_upload_limit) {
		    alert('上传文件数超过'+swfuOption.file_upload_limit);
		}
	    else if (numSelected + numTotalInQueued > swfuOption.file_upload_limit && numQueued == 0) {
	    	alert('上传文件数超过'+swfuOption.file_upload_limit+"\n已经选择了"+numTotalInQueued+"个文件\n剩下只能选择"+(swfuOption.file_upload_limit-numTotalInQueued)+"个文件");
		}
	    else if (numQueued == 0 && numSelected > 0) {
            alert('选择的图片不符合条件！');
		}
	    else {
            alert('共选择了'+numSelected+'个文件！');
		}
}  
/**
 * 上传状态
 */

function uploadProgress(file, curBytes, totalBytes) {
    //statusE.innerHTML += ['文件名：'+file.name, '总尺寸：'+totalBytes, 'B已上传：'+curBytes, 'B进度：'+parseInt((curBytes/totalBytes)*100)+'%'].join('');
    var ct = parseInt((curBytes/totalBytes)*100)+'%';
    $("#upload_progress_"+file.id).css("width",ct);
    $("#upload_status_"+file.id).html("上传中"+ct+"...");
}


//上传过程中出错
function uploadError(file, errCode, msg) {
	//-280是取消上传文件返回的错误代码
	if (errCode != -280) {
		//statusE.innerHTML += ['文件['+file.name+']上传出错，出错代码：['+errCode+']，出错原因：'+msg].join('');
		$("#upload_progress_"+file.id).html("上传失败");
		$("#upload_status_"+file.id).html(['文件['+file.name+']上传出错，出错代码：['+errCode+']，出错原因：'+msg].join(''));
		$("#upload_status_"+file.id).attr("class","label label-important");
	}
	upload_img_failure ++;//上传失败图片计数器加1
    $("#upload_img_failure_num").html(upload_img_failure);
    if (upload_img_wait > 0) {
        upload_img_wait --;//等待计数器减1
        $("#upload_img_wait_num").html(upload_img_wait);
    }
}
 
//上传成功
function uploadSuccess(file, data) {
    //statusE.innerHTML += ['文件['+file.name+']上传成功，服务器返回信息：', data].join('');
    //console.log(data);
    if (data == "1") {
	    $("#upload_progress_"+file.id).html('上传成功');
	    $("#upload_status_"+file.id).html("上传成功");
	    $("#upload_status_"+file.id).attr("class","label label-success");
	    upload_img_sucess_num ++;//上传成功计数器加1
	    $("#upload_img_sucess_num").html(upload_img_sucess_num);
        if (upload_img_wait > 0) {
	        upload_img_wait --;//等待计数器减1
            $("#upload_img_wait_num").html(upload_img_wait);
        }
    }
    else {
    	$("#upload_progress_"+file.id).html('上传失败');
	    $("#upload_status_"+file.id).html("上传失败:"+data);
	    $("#upload_status_"+file.id).attr("class","label label-important");
	    upload_img_failure ++;//上传失败图片计数器加1
	    $("#upload_img_failure_num").html(upload_img_failure);
	    if (upload_img_wait > 0) {
	        upload_img_wait --;//等待计数器减1
            $("#upload_img_wait_num").html(upload_img_wait);
        }
    }
}
 
//上传完成，无论上传过程中出错还是上传成功，都会触发该事件，并且在那两个事件后被触发
function uploadComplete(file) {
    this.setButtonDisabled(false);//恢复上传按钮
    //上传失败或者成功将文件踢出上传图片数组，可以在次添加
    if (img_name_arr.length > 0) {
    	$.each(img_name_arr, function(key, val){
        	if (val == file.name) {
            	img_name_arr.splice(key, 1);//删除数组对应的值
            }
        });
    }
}

var swfuOption = {//swfupload选项
        upload_url : "upload_img.php", //接收上传的服务端url
        flash_url : "SWFUpload/Flash/swfupload.swf",//swfupload压缩包解压后swfupload.swf的url
        button_placeholder_id : "swfu-placeholder",//上传按钮占位符的id
        file_size_limit : "2MB",//用户可以选择的文件大小，有效的单位有B、KB、MB、GB，若无单位默认为KB
        file_upload_limit : 50,//上传文件限制
        file_types : "*.jpg;*.gif;*.png;*.swf;",//文件类型 
        button_width: 120, //按钮宽度
        button_height: 20, //按钮高度
        button_text: '点我选择文件',//按钮文字
        //button_text_style : "color: #F5F5F5; font-size: 16pt;",
        //button_image_url : "SWFUpload/Flash/add.png",
        //设置自定义post参数
        post_params: {
        	"PHPSESSID" : "<?php echo $session_id;?>",//设置session_id
            "path" : "<?php echo $path;?>",//可以设置上传的目录
            "act"  : "upload_img",
        },
        
        file_queued_handler: fileQueued,// 文件被加入队列时触发
        //file_queue_error_handler: fileQueueError,// 文件加入队列出错时触发，包括大小限制，类型限制，空文件等均会触发
        file_dialog_complete_handler: fileDialogComplete,// 文件选择对话框在文件选择完成并关闭时触发
        upload_start_handler: uploadStart,// 文件开始上传时触发
        upload_progress_handler: uploadProgress,//监视上传的进度
        upload_error_handler: uploadError,//文件上传出错
        upload_success_handler: uploadSuccess,//文件上传成功
        upload_complete_handler: uploadComplete,//文件上传完成，在upload_error_handler或者upload_success_handler之后触发
        
        debug:true,
}
$(function(){
	var swfu = new SWFUpload(swfuOption);//初始化并将swfupload按钮替换swfupload占位符
	$("#upload_btn").click(function(){
        if (upload_img_wait <= 0) {
            alert("抱歉，当前没有上传文件！请选择文件吧！");
        }
        else {
		    swfu.startUpload();
        }
	});
	$("#cancle_all").click(function(){
		$("#logList").html('');
		swfu.cancelQueue();
		img_name_arr = [];//重新清空上传的图片名称
		upload_img_wait = 0;//记录等待上传图片数量归0
		$("#upload_img_wait_num").html(upload_img_wait);
	});
	$("#max_num").html(swfuOption.file_upload_limit);
	$("#max_size").html(swfuOption.file_size_limit);
	$("#upload_file_type").html(swfuOption.file_types);
});

</script>
</html>