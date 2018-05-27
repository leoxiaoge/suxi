//cmzUpload('img-area','img')
/**
 * [cmzUpload description]
 * @Author   Jerry
 * @DateTime 2017-05-05T14:14:27+0800
 * @Example  eg:
 * @param    {[type]}                 area      [旋转区域]
 * @param    {[type]}                 inputname [input取个名字]
 * @param    {[type]}                 img       [图片地址(默认图片地址)]
 * @param    {[type]}                 parames   [相关参数]
 * @return   {[type]}                           [description]
 */
function cmzUpload(area,inputname,img,parames){
    parames = parames?parames:[];
    width = parames['width']?parames['width']:100;
    height = parames['height']?parames['height']:100;
    inputname = inputname?inputname:"cmz"
    if(!area){
        alert('放置的区域为必填');
    }
    // ##图片地址
    if(!img){
        img = '<img src="/images/add-card.png" width="'+width+'" height="'+height+'" id="img_'+inputname+'" alt=""> <input type="hidden" id="'+inputname+'" name="'+inputname+'" > ';
    }else{
        img = '<img src="'+img+'" width="'+width+'" height="'+height+'" id="img_'+inputname+'" alt=""> <input type="hidden" id="'+inputname+'" name="'+inputname+'" value="'+img+'"  > ';
    }

    //上传域表单
    var html='<input type="file" id="btn_'+inputname+'" style="display:none;">'
        html+=img
    $('#'+area).append(html)
    $('#img_'+inputname).click(function(event) {
        $('#btn_'+inputname).click();
       // layer.open({
       //      content: '通过style设置你想要的样式',
       //      style: 'background-color:#09C1FF; color:#fff; border:none;',
       //      time: 2
       //  });
    });
        document.querySelector('#btn_'+inputname).addEventListener('change', function () {
        // alert('sfds')
        var that = this;

        lrz(that.files[0], {
            width: 800
        })
            .then(function (rst) {
                var img = new Image(),
                div = document.createElement('div'),
                p = document.createElement('p'),
                sourceSize = toFixed2(that.files[0].size / 1024),
                resultSize = toFixed2(rst.fileLen / 1024),
                scale = parseInt(100 - (resultSize / sourceSize * 100));
                
                // document.querySelector('#'+imgId).src= rst.base64;
                img.src = rst.base64;
                 // xhr.open('POST', '/Api/Cmzupload/up');
                    $.ajax({
                        url: '/?r=adm/service/uploads',
                        type: 'post',
                        dataType: 'json',
                        data: {base64: rst.base64},
                    })
                    .done(function(d) {
                        // $('#'+imgId).attr('src', d.data.path);
                        $('#img_'+inputname).attr('src', d.data.path);;
                        $('#'+inputname).val(d.data.path);
                      
                    })


            });
    });


}


function toFixed2 (num) {
    return parseFloat(+num.toFixed(2));
}

/**
 * 替换字符串 !{}
 * @param obj
 * @returns {String}
 * @example
 * '我是!{str}'.render({str: '测试'});
 */
String.prototype.render = function (obj) {
    var str = this, reg;

    Object.keys(obj).forEach(function (v) {
        reg = new RegExp('\\!\\{' + v + '\\}', 'g');
        str = str.replace(reg, obj[v]);
    });

    return str;
};

/**
 * 触发事件 - 只是为了兼容演示demo而已
 * @param element
 * @param event
 * @returns {boolean}
 */
function fireEvent (element, event) {
    var evt;

    if (document.createEventObject) {
        // IE浏览器支持fireEvent方法
        evt = document.createEventObject();
        return element.fireEvent('on' + event, evt)
    }
    else {
        // 其他标准浏览器使用dispatchEvent方法
        evt = document.createEvent('HTMLEvents');
        // initEvent接受3个参数：
        // 事件类型，是否冒泡，是否阻止浏览器的默认行为
        evt.initEvent(event, true, true);
        return !element.dispatchEvent(evt);
    }
}
