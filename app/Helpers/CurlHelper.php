<?php

namespace App\Helpers;
use App\Modules\ServerStorage\Models\StorageServer;
class CurlHelper
{

    public static function curl_post($url, $data) {

        if(is_array($data))
            $dataPost = http_build_query($data);
        else
            $dataPost = $data;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $dataPost);
        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        curl_setopt($ch, CURLOPT_REFERER, $actual_link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public static function curl_get($url){

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($curl);
        curl_close($curl);

        return $result;
    }

    /**
     * Tại đây sẽ khai báo các phương thức làm việc với API
     * Sử dụng cURL để cấu hình và gọi đến các API
     * Có 2 tham số truyền vào mặc định đó là
     *  $url: là đường link dẫn tới máy chủ
     *  $apikey: là khóa an toàn để xác thực thông tin
     * */

    /**
     * Lấy ra thông tin của tất cả các máy chủ lưu trữ
     * Tham số đầu vào là đường dẫn tới API cung cấp thông tin toàn bộ máy chủ
     * Ngoài ra cần 1 tham số là apikey để xác thực máy chủ
     * Lấy ra những máy chủ ít dung lượng nhất và có tỷ lệ sử dụng ít nhất
     * */
    public function getAllSystemInfo($url,$apikey){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'api-key:'.$apikey
        ));
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
    /**
     * Tạo mới tài khoản FTP
     * Tham số đầu vào gồm: tên tài khoản và mật khẩu
     * */
    public function createFTPUser($url, $username, $password, $apiKey){
        $data = array('username'=>$username,'password'=>$password);
        $data_json = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        curl_setopt($ch, CURLOPT_REFERER, $actual_link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'api-key:'.$apiKey
        ));
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public function uploadFTPFile($url, $username, $file, $apiKey){
        $data = array('username'=>$username,'uploadFile'=>$file);
        $data_json = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        curl_setopt($ch, CURLOPT_REFERER, $actual_link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'api-key:'.$apiKey
        ));
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Reset FTP Password
     * Tham số đầu vào là username, password (new)
     * */
    public function updateFTPPassword($url, $username, $newPassword, $apikey){
        $data = array('username'=>$username,'password'=>$newPassword);
        $data_json = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        curl_setopt($ch, CURLOPT_REFERER, $actual_link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'api-key:'.$apikey,
        ));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
    /**
     * Xóa 1 FTP file
     * tham số đầu vào là tên tài khoản
     * */
    public function deleteFTPUser($url, $username,$apikey){
        $data = array('username'=>$username);
        $data_json = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        curl_setopt($ch, CURLOPT_REFERER, $actual_link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'api-key:'.$apikey
        ));
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
    /**
     * Mã hóa file gửi lên
     * Tham số đầu vào là tên của file và tên người dùng
     * */
    public function DecryptFile($username,$fileName,$url,$apikey){
        $data = array('username'=>$username, 'filename'=>$fileName);
        $data_json = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        curl_setopt($ch, CURLOPT_REFERER, $actual_link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'api-key:'.$apikey,
        ));
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
    /**
     * Xóa 1 file đã được mã hóa
     * Tham số đầu vào là tên file và tên người dùng
     * */
    public function DeleteDecryptFile($username,$fileName,$url,$apikey){
        $data = array('username'=>$username, 'filename'=>$fileName);
        $data_json = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        curl_setopt($ch, CURLOPT_REFERER, $actual_link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'api-key:'.$apikey,
        ));
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
    /**
     * Xóa 1 file đã được giải mã
     * Tham số đầu vào là tên file và tên người dùng
     * */
    public function DeleteEncryptFile($username,$fileName,$url,$apikey){
        $data = array('username'=>$username, 'filename'=>$fileName);
        $data_json = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        curl_setopt($ch, CURLOPT_REFERER, $actual_link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'api-key:'.$apikey,
        ));
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
    /**
     * Upload file bằng 1 url
     * Nhập đường dẫn URL của file sau đó up lên server
     * */
    public function UploadFileFormUrl($username,$url,$urlFIle,$apikey){
        $data = array('url'=>$urlFIle);
        $data_json = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        $actual_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        curl_setopt($ch, CURLOPT_REFERER, $actual_link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'api-key:'.$apikey,
            'username:'.$username
        ));
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Thuật toán để lấy ra storage được sử dụng ít nhất
     * Xử lý mảng 2 chiều
     * Nếu lệch thuật toán thì sửa lại tại dòng if(){}
     * */
    public function getMinStorage(){
        $arrayData = $this->getSystemInfo();
        $i=0;
        for($i; $i <= count($arrayData);$i++){
            if($arrayData[$i]["mount"] > $arrayData[$i++]["mount"]){
                return $arrayData[$i];
            }else{
                return $arrayData[0];
            }
        }
    }
    /**
     * Phương thức lấy ra thông tin của máy chủ
     * Truy vấn dữ liệu trong CSDL
     * Lấy ra thông tin
     * Gọi đến phương thức getAllSystemInfo và truyền tham số
     * Nhận đc dữ liệu trả về và gán vào 1 mảng
     * Lấy ra tỷ lệ sử dụng thấp nhất của storage tính theo số người dùng
     * Gán vào mảng và trả về mảng đó để hàm khác sẽ phân tích
     * */
    public function getSystemInfo(){
        $storage = StorageServer::all();
        $apikey = "hanv#123";
        $arrayStorage = array();
        $i = 0;
        foreach($storage as $key=>$value){
            $data = json_decode($this->getAllSystemInfo($value->url, $apikey));
            $total_mem = $data->mem->total;
            $free_mem = $data->mem->free;
            $mount = ($free_mem / $total_mem) * 100;
            $new_array = array($i,"model"=>$data->model,"mount" => $mount);
            $arrayStorage[] = $new_array;
            $i++;
        }
        return $arrayStorage;
    }


}
