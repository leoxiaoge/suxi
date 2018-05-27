<?php  
if ($data = $_POST['base64']) {

        preg_match("/data:image\/(.*);base64,/",$data,$res);
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $data, $result)){
          $type = $result[2];
          $new_file = "/upload/cmzup/".date('YmdHis',time()).".".$type;
          $imgBase64 = base64_decode(str_replace($result[1], '', $data));
          if (file_put_contents(".".$new_file, $imgBase64)){
            $fh = fopen(".".$new_file, "r");
                  $data = fread($fh, filesize(".".$new_file));
                  $length = filesize(".".$new_file);
                  fclose($fh);
                  $info['type'] = "image/".$type;
                  $type = ($type=="jpeg")?"jpg":$type;
                  $info['name'] = time() . "." . $type; //自定义图片名称
                  $datas['path'] = $new_file;
                  $datas['msg'] = "上传成功";
                  responseJson(0,$datas);
            

          }else{
            	responseJson(1,'上传失败');
          }
        }
  }