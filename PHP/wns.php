<?php
//Define Wns class
class Wns {
    public $access_token="";
    public $sid = "";
    public $secret = "";

    //for prepare message tile xml
    public function buildTileXml($title, $subtitle){
        $toastMessage = "<toast>
                            <visual>
                                <binding template=\"ToastImageAndText04\">
                                    <image id=\"1\" placement=\"appLogoOverride\" hint-crop=\"circle\" src=\"https://upload.wikimedia.org/wikipedia/commons/thumb/b/b4/The_Sun_by_the_Atmospheric_Imaging_Assembly_of_NASA%27s_Solar_Dynamics_Observatory_-_20100819.jpg/260px-The_Sun_by_the_Atmospheric_Imaging_Assembly_of_NASA%27s_Solar_Dynamics_Observatory_-_20100819.jpg\" alt=\"image1\"/>
                                    <text id=\"1\">".$title."</text>
                                    <text id=\"2\">".$subtitle."</text>
                                </binding>
                            </visual>
                        </toast>";
        return $toastMessage;
    }
    
    public function sendWindowsNotification($uri, $xml_data, $type = 'wns/toast', $tileTag = ''){
        if($this->access_token == ''){
            $this->get_access_token();
        }
    
        $headers = array('Content-Type: text/xml',"Content-Type: text/xml", "X-WNS-Type: wns/toast","Content-Length: " . strlen($xml_data),"X-NotificationClass:2" ,"X-WindowsPhone-Target: toast","Authorization: Bearer $this->access_token");
        if($tileTag != ''){
            array_push($headers, "X-WNS-Tag: $tileTag");
        }
        $ch = curl_init($uri);
        # Tiles: http://msdn.microsoft.com/en-us/library/windows/apps/xaml/hh868263.aspx
        # http://msdn.microsoft.com/en-us/library/windows/apps/hh465435.aspx
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "$xml_data");
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        $response = curl_getinfo( $ch );
        
        curl_close($ch);
        $code = $response['http_code'];
        
        if($code == 200){
            return new WPNResponse('Successfully sent message', $code);
        }
        else if($code == 401){
            $this->access_token = '';
            return $this->post_tile($uri, $xml_data, $type, $tileTag);
        }
        else if($code == 410 || $code == 404){
            return new WPNResponse('Expired or invalid URI', $code, true);
        }
        else{
            return new WPNResponse('Unknown error while sending message', $code, true);
        }
    }
    
    private function get_access_token(){
        if($this->access_token != ''){
            return;
        }
       
        $str = "grant_type=client_credentials&client_id=$this->sid&client_secret=$this->secret&scope=notify.windows.com";
        $url = "https://login.live.com/accesstoken.srf";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, "$str");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);                       
        $output = json_decode($output);
        if(isset($output->error)){
            throw new Exception($output->error_description);
        }
        $this->access_token = $output->access_token;
    }
}

//Define WPN Response Class
class WPNResponse{
    public $message = '';
    public $error = false;
    public $httpCode = '';
    
    function __construct($message, $httpCode, $error = false){
        $this->message = $message;
        $this->httpCode = $httpCode;
        $this->error = $error;
    }

    function ToString()
    {
        echo $this->message . " " . $this->httpCode . " " . $this->error ."<br>;
    }
}
?>