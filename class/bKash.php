<?php
class BKASH
{
    private $baseUrl;
    private $filePath;
    private $bKashData;
    public function __construct()
    {
        $this->baseUrl = BKASH_URL;
        $this->filePath = ROOT_DIR . '/config/bKash.json';
        $this->setBkashData();
        $needToken = false;
        if (!isset($this->bKashData['token'])) {
            $needToken = true;
        } elseif ($this->bKashData['tokenStart'] < strtotime('-45 minute')) {
            $needToken = true;
        }
        if ($needToken == true) {
            $this->bKashGetToken();
        }
    }
    private function setBkashData()
    {
        $bKashFileContent = file_get_contents($this->filePath);
        $bKashData = json_decode($bKashFileContent, true);
        $this->bKashData = $bKashData;
    }

    private function bKashGetToken(&$jArray=[])
    {
        $postBody = [
            'app_key' => BKASH_APPKEY,
            'app_secret' => BKASH_APPSECRET
        ];
        $headers = [
            "accept: application/json",
            "content-type: application/json",
            "password: " . BKASH_PASSWORD,
            "username: " . BKASH_USERNAME
        ];
        $result = $this->getresponsedata('/token/grant', $postBody,$headers,$jArray);
        if (isset($result['id_token'])) {
            $this->bKashData['token'] = $result['id_token'];
            $this->bKashData['tokenStart'] = TIME;
        } else {
            $this->bKashData['token'] = '';
            $this->bKashData['tokenStart'] = 0;
        }
        $newJsonString = json_encode($this->bKashData);
        file_put_contents($this->filePath, $newJsonString);
    }

    public function getresponsedata($path, $body,$headers,&$jArray=[])
    {
        $jArray[fl()][]=$body;
        $jArray[fl()][]=$headers;
        $jArray[fl()][]=$this->baseUrl .  $path;
        $ch = curl_init($this->baseUrl .  $path);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $jArray[fl()][]=$response;
        curl_close($ch);
        $data = json_decode($response, true);
        return $data;
    }
    
    public function refundPayment($payData,&$jArray=[]){
        $return=[
            'status'=>0,
            'message'=>''            
        ];
        $createbody =[
            'paymentID'=>$payData['paymentID'],
            'amount'=>$payData['amount'],
            'trxID'=>$payData['trxID'],
            'sku'=>'sku',
            'reason'=>'Ticket Cancel',
        ];
        $createheaders=[
            "Authorization: ".$this->bKashData['token'],
            "X-APP-Key: ".BKASH_APPKEY,
            "accept: application/json",
            "content-type: application/json"
        ];
        $jArray[fl()]=$this->bKashData;
        $createdata = $this->getresponsedata("/payment/refund",$createbody,$createheaders);
        $jArray[fl()]=$createdata;
        if(isset($createdata['statusMessage']) && $createdata['statusMessage']=='Successful' ){
            $return['status']=1;
            $return['refundData']=$createdata;
            
        }
        return $return;
    }
    public function createInvoice(string $transaction_id,float $amount,array &$jArray=[]):array{
        $return=[
            'status'=>0,
            'message'=>''            
        ];
        $createbody =[
            'mode'=>'0011',
            'callbackURL'=>URL.'/crn/payment_complete.php',
            'currency'=>'BDT',
            'intent'=>'sale',
            'payerReference'=>' ',
            'amount'=>$amount,
            'merchantInvoiceNumber'=>$transaction_id
        ];
        $createheaders=[
            "Authorization: ".$this->bKashData['token'],
            "X-APP-Key: ".BKASH_APPKEY,
            "accept: application/json",
            "content-type: application/json"
        ];
        $jArray[fl()]=$this->bKashData;
        $createdata = $this->getresponsedata("/create",$createbody,$createheaders,$jArray);
        $jArray[fl()]=$createdata;
        if(isset($createdata['bkashURL'])){
            $return['status']=1;
            $return['url']=$createdata["bkashURL"];
            $return['paymentID']=$createdata["paymentID"];
        }
        return $return;
    }
    public function checkInvoice(string $paymentID,array &$jArray=[]):array{
        $return=[
            'status'=>0,
            'message'=>''            
        ];
        $createbody =[
            'paymentID'=>$paymentID
        ];
        $createheaders=[
            "Authorization: ".$this->bKashData['token'],
            "X-APP-Key: ".BKASH_APPKEY,
            "accept: application/json",
            "content-type: application/json"
        ];
        $jArray[fl()]=$this->bKashData;
        $executeData = $this->getresponsedata("/execute",$createbody,$createheaders);
        $jArray[fl()]=$executeData;
        if(isset($executeData['statusCode'])){
            if($executeData['statusCode']=='0000'){
                $return['status']=1;
                $return['response']=$executeData;
            }
            elseif(isset($executeData['statusMessage'])){
                $return['status']=2;
                $return['message']=$executeData['statusMessage'];
            }
        }
        return $return;
    }
}