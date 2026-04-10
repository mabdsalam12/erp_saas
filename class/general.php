<?php
    class General
    {
    public function pay_type_title_by_id($type){
        $value='';
        switch ($type) {
            case PAY_TYPE_CASH:
                $value='Cash';
                break;

            case PAY_TYPE_CREDIT:
                $value='Credit';
                break;

            case PAY_TYPE_CASH_ON_DELIVERY:
                $value='Cash on delivery';
                break;
        }
        return $value;

    }
    
    public function arrayIndexChange(&$array,$arrayIndex='id',$contentShow=false){
        $return = array();
        if(is_array($array)){

            $return = array_column($array,null,$arrayIndex);
            // foreach($array as $a){
            //     $return[$a[$arrayIndex]]=$a;
            // }
        }
        if($contentShow===true){$this->arrayContentShow($return);}
        $array=$return;
        return $return;
    }
    function tableOrdArray($no){
        $array=array(
            1=>array(
                'id'=>'cnID',
                'title'=>'cnTitle',
                'order'=>'cnOrder'
            ),/*
            2=>array(
            'id'=>'siID',
            'title'=>'siTitle',
            'order'=>'siOrder'
            ),
            3=>array(
            'id'=>'slID',
            'title'=>'slTitle',
            'order'=>'slOrder'
            ),
            4=>array(
            'id'=>'pcID',
            'title'=>'pcTitle',
            'order'=>'pcOrder'
            ),
            5=>array(
            'id'=>'btID',
            'title'=>'btTitle',
            'order'=>'btOrder'
            ),
            6=>array(
            'id'=>'smID',
            'title'=>'smTitle',
            'order'=>'smOrder'
            )*/
        );
        return $array[$no];
    }
    /**
    * This method return user access.<br />
    * @param (array) Sorting Array
    * @param (string) array index which will be sorted
    * @param (int) SORT_ASC/SORT_DESC
    * @return void
    */
    public function arraySortByColumn(&$arr, $col, $dir = SORT_ASC) {
        $sort_col = [];
        foreach ($arr as $key=> $row){$sort_col[$key]=$row[$col];}
        array_multisort($sort_col, $dir, $arr);
    }
    public function arrayValueIntval(&$array){
        $intArray = [];
        foreach($array as $k=>$a){
            if(!is_array($a)){$intArray[$k]=intval($a);}
            else{$this->arrayValueIntval($a);}
        }
        $array=$intArray;
    }
    function addBtnHtml($pUrl,$aStatus=true){
        if($aStatus==true){
            return '<a style="font-size: 20px; color: #228AE6;margin-left: 20px;" href="'.$pUrl.'&add=1"><i class="fa fa-plus-square "></i></a>';
        }
    }
    function pageHeader($title,$breadCramp=array(),$extraHtml=''){
        if(!is_array($breadCramp)){$data=array($breadCramp=>$title);}
        else{
            $data=$breadCramp;
        }
    ?>
    <div class="row bg-title">
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            <h4 class="page-title"><?php echo $title;?> <?php echo $extraHtml;?></h4>
        </div>
        <?php 
            if(empty($data)){
                $data=array('javascript:void()'=>$title);
            }
            $this->breadcrumb($data);            
        ?>
    </div>
    <?php
    }
    function breadcrumb($data){
    ?>

    <div class="col-lg-5 col-sm-5 col-md-5 col-xs-12">
        <ol class="breadcrumb">

            <li><a href="<?=URL?>">Home</a></li>
            <?php
                foreach($data as $d=>$key){
                    if($d == 1){
                    ?>
                    <li class="active"><?=$key?></li>
                    <?php
                    }
                    else{
                    ?>
                    <li><a href="<?=$d?>"><?=$key?></a></li>
                    <?php
                    }
                }    
            ?>
        </ol>
    </div>
    <?php             
    }
    function make_url($requestUrl){
        return strtolower(preg_replace('#[^\w-]#',"",str_ireplace(' ', '_',trim($requestUrl))));
    }
    function make_future_timestamp($day,$timestamp){return strtotime("+$day day", $timestamp);}

    function make_date($timestamp,$st='',$y_m_d='',$time='') {
        if($timestamp==''||$timestamp==0){return '';}
        else{
            if($st){
                if($st== 'i'){
                    return date('d-m-Y h:i:s A', $timestamp);
                }
                elseif($st== 'time'){
                    return date('d-m-Y h:i:s A', $timestamp);
                }
                elseif($st== 'st'){
                    return date('d-m-y h:i A', $timestamp);
                }
                elseif($st == 'y_m_d'){
                    return date('y-m-d', $timestamp);
                }
                elseif($st == 'm_d_y'){
                    return date('m/d/Y', $timestamp);
                }
                elseif($st == 'd/m/y'){
                    return date('m/d/Y', $timestamp);
                }
                else{
                    return date('d-m-Y', $timestamp);
                }
            }
            else{
                return date('d-m-Y', $timestamp);
            }
        }
    }
    public function getHourMint($timestamp){
        if(1){
            //if(PROJECT!=LOCAL){
            return date('h:i A', $timestamp);
            /*}
            else{
            return $date=$this->timeForSwahili($timestamp);
            } */
        }
        else{
            return date('H:i', $timestamp);
        }
    }

    public function get_time_difference($start, $end,$difference_in='s'){
        $datetime1 = new DateTime(date('Y-m-d H:i:s',$start));
        $datetime2 = new DateTime(date('Y-m-d H:i:s',$end));
        $interval = $datetime1->diff($datetime2);
        if($difference_in=='d'){
            return $interval->d;
        }
        return $interval->s;
    }
    public function get_time_difference_in_days($start, $end){
        $datetime1 = new DateTime(date('Y-m-d H:i:s',$start));
        $datetime2 = new DateTime(date('Y-m-d H:i:s',$end));
        $interval = $datetime1->diff($datetime2);
        return $interval->days;
    }
    function getFromToFromString($dRange,&$from,&$to){
        $dRange = explode(' to ',$dRange);
        $from   = strtotime($dRange[0]);
        $to     = strtotime(trim($dRange[1])) ;
        $to       = strtotime('+1 day',$to);
        $to       = strtotime('-1 second',$to);
    }

    function bangladeshiMobileCheck($number){
        //return true;//সবার জন্যই ওপেন শুধু ভ্যালিড গুলো এস এম এস পাবে
        if(strlen($number)==13){
            return preg_match("/^[8]{2}[01]{2}[3-9]{1}[0-9]{8}$/i", $number);//8801730912895 are valid    88011 invalid
        }
        elseif(strlen($number)==11){
            return preg_match("/^[01]{2}[3-9]{1}[0-9]{8}$/i", $number);//01730912895 are valid 011 invalid
        }
        else{
            return false;
        }

    }

    function checked($valu1,$value2='myNameisSalam'){
        if($value2 == 'myNameisSalam'){
            if($valu1 == 1){
                $return = 'checked="checked"';
            }
            else{
                $return = '';
            }
        }
        else{
            if(!is_array($valu1)){
                if($valu1 == $value2){
                    $return = 'checked="checked"';
                    //echo $value.'-'.$value;
                }
                else{
                    //echo $value.'-'.$value;
                    $return = '';
                }
            }
            else{
                if(in_array($value2,$valu1)){
                    $return = 'checked="checked"';
                }
                else{
                    $return = '';
                } 
            }
        }
        return $return;
    }
    function selected($valu1,$value2){
        if(!is_array($valu1)){
            if($valu1 == $value2){
                $return = 'selected="selected"';
            }
            else{
                $return = '';
            }
        }
        else{
            if(in_array($value2,$valu1)){
                $return = 'selected="selected"';
            }
            else{
                $return = '';
            }
        }
        return $return;

    }
    function content_show($content,$rn='no'){
        if($content==null||$content==''){
            return '';
        }
        $content = html_entity_decode(stripcslashes($content));
        if($rn=='br'){
            $content = str_ireplace("\r\n",'<br>',$content);
            $content = str_ireplace("\n",'<br>',$content);
        }
        elseif($rn=='rn'){
            $content = str_ireplace("\r\n",'',$content);
            $content = str_ireplace("\n",'',$content);
        }
        return $content;
    }
    function arrayContentShow(&$v) {
        $data = $v;
        $output = array();
        if(is_array($data)) {foreach($data as $k=>$d) {$output[$k] = $this->arrayContentShow($d);}}
        else{$output = $this->content_show($data);}
        $v = $output;
        return $v;
    }
    function arrayTabRemove(&$v) {
        $data = $v;
        $output = array();
        if(is_array($data)) {foreach($data as $k=>$d) {$output[$k] = $this->arrayTabRemove($d);}}
        else{
            $output=preg_replace('/\s+/', ' ',$data);
        }
        $v = $output;
        return $v;
    }
    function redirect($url,$message=''){
        if($message!=''){
            if(!is_array($message)){$a=func_get_args();$n=func_num_args();$m=array();for($i=1;$i<$n;$i++){$m[]=$a[$i];}}else{$m=$message;}
            setMessage($m);
        }
        header('location:'.$url);exit();
    }

    function onclickChangeBTN($id,$checkest=''){
    ?>
    <div class="checkbox checkbox-info checkbox-circle">
        <input type="checkbox"
            class="checkbox-circle" <?=$checkest?>
            onclick="actinact('<?=$id?>',this.checked);"
            id="act_<?=$id?>"
            name="act_<?=$id?>">
        <label for="act_<?=$id?>"></label>
    </div>
    <?php
    }
    function onclickChangeJavaScript($tbl,$name){
    ?>
    <script type="text/javascript">
        function actinact(pId,chekValue){
            var stch = '<?=$tbl?>';
            var name = '<?=$name?>';
            if(chekValue==true){var ch=1;}else{var ch=0;}
            if (window.XMLHttpRequest){xmlhttp=new XMLHttpRequest();}
            else{xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");}
            xmlhttp.onreadystatechange=function(){
                if (xmlhttp.readyState==4 && xmlhttp.status==200){}
            }
            xmlhttp.open("GET",ajUrl+"&stch="+stch+"&ch_id="+pId+'&action='+ch+'&name='+name,true);
            xmlhttp.send();
        }
    </script>
    <?php
    }

    function jsonHeader($jArray=array()){header('Content-Type: application/json');if(!empty($jArray)){echo json_encode($jArray);exit();}}

    function convert_number_to_words($number,$abc=''){
        //            $number=str_ireplace(',','',$number);
        $hyphen      = '-';
        $conjunction = ' and ';
        $separator   = ', ';
        $negative    = 'negative ';
        $decimal     = ' point ';
        $dictionary  = array(
            0                   => 'zero',
            1                   => 'one',
            2                   => 'two',
            3                   => 'three',
            4                   => 'four',
            5                   => 'five',
            6                   => 'six',
            7                   => 'seven',
            8                   => 'eight',
            9                   => 'nine',
            10                  => 'ten',
            11                  => 'eleven',
            12                  => 'twelve',
            13                  => 'thirteen',
            14                  => 'fourteen',
            15                  => 'fifteen',
            16                  => 'sixteen',
            17                  => 'seventeen',
            18                  => 'eighteen',
            19                  => 'nineteen',
            20                  => 'twenty',
            30                  => 'thirty',
            40                  => 'fourty',
            50                  => 'fifty',
            60                  => 'sixty',
            70                  => 'seventy',
            80                  => 'eighty',
            90                  => 'ninety',
            100                 => 'hundred',
            1000                => 'thousand',
            1000000             => 'million',
            1000000000          => 'billion',
            1000000000000       => 'trillion',
            1000000000000000    => 'quadrillion',
            1000000000000000000 => 'quintillion'
        );

        if (!is_numeric($number)) {return false;}

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . $this->convert_number_to_words(abs($number));
        }

        $string = $fraction = null;

        $d = explode('.',$number);if(isset($d[1])){if(intval($d[1])==0){$number=$d[0];}}

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens   = ((int) ($number / 10)) * 10;
                $units  = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds  = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction .$this->convert_number_to_words($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = $this->convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->convert_number_to_words($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }
        $string=str_ireplace('Only.','',$string);
        if($string!='')$string.=' '.$abc.' Only.';
        $string=str_ireplace('  ',' ',$string);

        return ucfirst($string);
    }

    function fileToVariable($path,$data=[]) {
        ob_start();
        if(!empty($data)){
            global $gAr;
            $gAr=$data;
        }
        if(!function_exists('selected')){
            function selected($valu1,$value2){
                if(!is_array($valu1)){
                    if($valu1 == $value2){$return = 'selected="selected"';}
                    else{$return = '';}
                }
                else{
                    if(in_array($value2,$valu1)){$return = 'selected="selected"';}
                    else{$return = '';}
                }
                return $return;
            }
        }
        $pageContent = '';
        include $path;
        $pageContent = ob_get_contents();
        ob_end_clean();

        return $pageContent;
    }
    function printArray($a){echo '<pre>';print_r($a);echo '</pre>';}

    function varDump($a){echo '<pre>';var_dump($a);echo '</pre>';}


    /**
    * This method return user access.<br />
    * @param string input name and id name.
    * @param string label name and placeholder
    * @param string in any value available for input box
    * @param string  if this input required then it's value will be **y**
    * @param string form-control+$className 
    * @param string input field script like onclick,disable..
    * @return void 
    */
    function inputBoxText(string $name,$label,$currentValue='',$required='n',$className='',$inputScript='',$extraData=''):void {
        $className='form-control '.$className;
        $lc='';
        if(isset($extraData['labelClass'])){
            $lc=$extraData['labelClass']; 
        }
    ?>
    <div class="form-group row">
        <label for="<?php echo $name;?>" class="col-md-4 col-form-label <?php echo $lc?>"><?php echo $label;?> <?php echo $required=='y'?BOOTSTRAP_REQUIRED:'';?></label>
        <div class="col-md-8">
            <input class="<?php echo $className;?>" value="<?php echo $currentValue;?>" placeholder="<?php echo $label;?>" id="<?php echo $name;?>" type="text" name="<?php echo $name;?>" <?php echo $required=='y'?'required="required"':'';?> <?php echo $inputScript;?>>
        </div>
    </div>
    <?php
    }
    public function inputBoxTextForReport(string $name, string $label,$currentValue='',$required='n',$className='',$inputScript='',$extraData=''):void {
        $className='form-control '.$className;
    ?>
    <div class="col-md-2">
        <h5 class="box-title"><?=$label?></h5>
        <input class="<?=$className;?>" value="<?=$currentValue;?>" placeholder="<?=$label;?>" id="<?=$name;?>" type="text" name="<?=$name;?>" <?=$required=='y'?'required="required"':'';?> <?php echo $inputScript;?>>
    </div>
    <?php
    }
    /**
    * This method return user access.<br />
    * @param (string) input name and id name.
    * @param (string) label name and placeholder
    * @param (string) in any value available for input box
    * @param (string) y/'' if this input required then it's value will be y
    * @param (string) form-control+$className 
    * @param (string) input field script like onclick,disable..
    * @return bool 
    */
    public function inputBoxTextArea($name,$label,$currentValue='',$required='n',$className='',$inputScript=''): void{
        $className='form-control '.$className;
    ?>
    <div class="form-group row">
        <label for="<?php echo $name;?>" class="col-md-4 col-form-label"><?php echo $label;?> <?php echo $required=='y'?BOOTSTRAP_REQUIRED:'';?></label>
        <div class="col-md-8">
            <textarea class="<?php echo $className;?>" placeholder="<?php echo $label;?>" id="<?php echo $name;?>" type="text" name="<?php echo $name;?>" <?php echo $required=='y'?'required="required"':'';?> <?php echo $inputScript;?> autocomplete="off"><?php echo $currentValue;?></textarea>
        </div>
    </div>
    <?php
    }
/**
    * This method return user access.<br />
    * @param array select tag array collection
    * @param string label name and placeholder
    * @param string input name and id name
    * @param string array column for value
    * @param string array column for label
    * @param string current selected value
    * @param string y/''/No if this input required then it's value will be y
    * @param string form-control+$className 
    * @param string input field script like onclick,disable
    * @param string If there have any option for first option n=no
    * @return void 
    */
    function inputBoxSelect($dataArray,$label,$inputName,$columnID='id',$columnTitle='title',$currentValue='',$required='No',$inputClassName='form-control select2',$script='n',$haveSelect=''){
        if($inputClassName==''){$inputClassName='form-control select2';}
    ?>
    <div class="form-group row">
        <label class="col-md-4 col-form-label" for="<?=$inputName?>"><?php echo $label;?> <?php echo $required=='y'?BOOTSTRAP_REQUIRED:'';?></label>
        <div class="col-md-8">
            <select name="<?=$inputName?>" id="<?=$inputName?>" class="<?=$inputClassName?>" <?=($required=='y')?'required':''?> <?=($script!='n')?$script:''?> >
                <?php
                    if($haveSelect!='n'){
                        if($haveSelect==''){
                        ?>
                        <option value=""><?=l('select')?></option>
                        <?php
                        }
                        else{
                        ?>
                        <option value=""><?=$haveSelect?></option>
                        <?php
                        }
                    }
                    if(!empty($dataArray)){
                        $this->arrayContentShow($dataArray);
                        foreach($dataArray as $i){
                        ?>
                        <option id="<?=$inputName?>-<?=$i[$columnID]?>" value="<?=$i[$columnID]?>" <?=$this->selected($currentValue,$i[$columnID])?>><?=$i[$columnTitle]?></option>
                        <?php
                        }
                    }
                ?>
            </select>
        </div>
    </div>
    <?php
    }
    /**
     * Summary of inputBoxSelectForReport
     * @param mixed $dataArray
     * @param mixed $label
     * @param mixed $inputName
     * @param mixed $columnID
     * @param mixed $columnTitle
     * @param mixed $currentValue
     * @param mixed $inputClassName
     * @param mixed $script
     * @param mixed $needFirstOption
     * @param mixed $all_value
     * @param mixed $haveSelect
     * @return void
     */
    function inputBoxSelectForReport($dataArray,$label,$inputName,$columnID,$columnTitle,$currentValue='',$inputClassName='form-control select2',$script='n',$needFirstOption=true,$all_value='',$haveSelect='All'){
        if($inputClassName==''){$inputClassName='form-control select2';}
    ?>
    <div class="col-md-2">
        <h5 class="box-title"><?=$label?> </h5>
        <select id="<?=$inputName?>" name="<?=$inputName?>" class="<?=$inputClassName?>" <?=($script!='n')?$script:''?>>
            <?php
                if($needFirstOption){
                ?>
                <option value="<?=$all_value?>"><?=$haveSelect?></option>
                <?php
                }
                if(!empty($dataArray)){
                    foreach($dataArray as $i){
                    ?><option value="<?=$i[$columnID]?>" <?=$this->selected($currentValue,$i[$columnID])?>><?=$i[$columnTitle]?></option><?php
                    }
                }
            ?>
        </select>
    </div>
    <?php 

    }
    
    function weekDateNameByID($id){
        if($id==WEEK_DAY_SATURDAY){     return 'Saturday';  }
        elseif($id==WEEK_DAY_SUNDAY){   return 'Sunday';    }
        elseif($id==WEEK_DAY_MONDAY){   return 'Monday';    }
        elseif($id==WEEK_DAY_TUESDAY){  return 'Tuesday';   }
        elseif($id==WEEK_DAY_WEDNESDAY){return 'Wednesday'; }
        elseif($id==WEEK_DAY_THURSDAY){ return 'Thursday';  }
        elseif($id==WEEK_DAY_FRIDAY){   return 'Friday';    }
        else{
            return 'Unknown Day';
        }
    }

    function weekDateValidateByID($id){
        if($id==WEEK_DAY_SATURDAY){     return true;}
        elseif($id==WEEK_DAY_SUNDAY){   return true;}
        elseif($id==WEEK_DAY_MONDAY){   return true;}
        elseif($id==WEEK_DAY_TUESDAY){  return true;}
        elseif($id==WEEK_DAY_WEDNESDAY){return true;}
        elseif($id==WEEK_DAY_THURSDAY){ return true;}
        elseif($id==WEEK_DAY_FRIDAY){   return true;}
        else{
            return false;
        }
    }
    function saveBtn($name='edit',$value= 'Update'){
    ?>
    <div class="col-md-12">
        <input type="submit" name="<?php echo $name;?>" value="<?php echo $value;?>" class="btn btn-info waves-effect waves-light m-t-5 pull-right"> 
    </div>
    <?php
    }
    function editBtn($name='edit',$value= 'Update'){
    ?>
    <div class="col-md-12">
        <input type="submit" name="<?php echo $name;?>" value="<?php echo $value;?>" class="btn btn-info waves-effect waves-light m-t-5 pull-right"> 
    </div>
    <?php
    }
    function addBtn($name='add',$value= 'Add'){
    ?>
    <div class="col-md-12">
        <input type="submit" name="<?php echo $name;?>" value="<?php echo $value;?>" class="btn btn-lg btn-success waves-effect waves-light m-t-5 pull-right"> 
    </div>
    <?php
    }
    function numberFormat($n,$dec=2){
        //return (float)$n;
        if(gettype($dec)=='string'){
            $v=debug_backtrace();
            $this->createLog('mm',$v);
            echo $dec;exit;
        }
        if($n==null){
            $n=0;
        }
        return number_format($n,$dec,'.',',');
    }
    public function numberFormatString($n,$dec=2){
        //return (float)$n;
        // if(gettype($dec)=='string'){
        //     $v=debug_backtrace();
        //     $this->createLog('mm',$v);
        //     echo $dec;exit;
        // }
        if($n==null){
            $n=0;
        }
        return number_format($n,$dec,'.','');
    }
    function showQuery(){
        if(isset($_GET['showq'])){
            return 'a';
        }
        else{
            return 'No';
        }
    }
    function monthNameById($m){
        $r='Unknown '.$m.' 0';
        switch($m){
            case 1:$r='January';break;
            case 2:$r='February';break;
            case 3:$r='March';break;
            case 4:$r='April';break;
            case 5:$r='May';break;
            case 6:$r='June';break;
            case 7:$r='July';break;
            case 8:$r='August';break;
            case 9:$r='September';break;
            case 10:$r='October';break;
            case 11:$r='November';break;
            case 12:$r='December';break;
        }
        return $r;
    }
    function allMonth(){
        return [
            ['id'=>1,'name'=>'January'],
            ['id'=>2,'name'=>'February'],
            ['id'=>3,'name'=>'March'],
            ['id'=>4,'name'=>'April'],
            ['id'=>5,'name'=>'May'],
            ['id'=>6,'name'=>'June'],
            ['id'=>7,'name'=>'July'],
            ['id'=>8,'name'=>'August'],
            ['id'=>9,'name'=>'September'],
            ['id'=>10,'name'=>'October'],
            ['id'=>11,'name'=>'November'],
            ['id'=>12,'name'=>'December'],
        ];
    }
    function colorHexToDec($color){
        //$color = '#ffffff';
        $rgbArray=array('r'=>0,'g'=>0,'b'=>0);
        $hex = str_replace('#','', $color);
        if(strlen($hex) == 3){
            $rgbArray['r'] = hexdec(substr($hex,0,1).substr($hex,0,1));
            $rgbArray['g'] = hexdec(substr($hex,1,1).substr($hex,1,1));
            $rgbArray['b'] = hexdec(substr($hex,2,1).substr($hex,2,1));
        }else{
            $rgbArray['r'] = hexdec(substr($hex,0,2));
            $rgbArray['g'] = hexdec(substr($hex,2,2));
            $rgbArray['b'] = hexdec(substr($hex,4,2));
        }
        return $rgbArray;

    }
    function getJsonFromString($data){
        if($data!=''){
            $json=json_decode($data,true);
            if($json!=false){
                return $json;
            }
            else{
                return [];
            }
        }
        else{
            return [];
        }
    }


    /**
    * This method return user access.<br />
    * @param array Main array
    * @param string array index which need to collect
    * @param array which variable are reset
    * @return void
    */
    function getIDFromVariable($array,$index,&$newArray):void{
        foreach($array as $a){
            if(isset($a[$index])){
                $newArray[$a[$index]]=$a[$index];
            }
        }
    }
    function getIDsFromArray(array $data,$index,&...$args): void {
        if(!$data)return;
        if(empty($index)) throw new Exception('getIDsFromArray');
        if(empty($args))throw new Exception('getIDsFromArray');
        if(!is_array($index)){$index=explode(',',str_ireplace(' ','', $index));}
        if(count($args)!=count($index))throw new Exception('getIDsFromArray');
        foreach($data as $d){
            foreach($index as $k=>$i){
                if(isset($d[$i])){
                    $args[$k][$d[$i]]=$d[$i];
                }
            }
        }
        
    }
    public function createLog($type,$data){
        $textFileName=ROOT_DIR.'/log/'.PROJECT.'/';
        if(!is_dir($textFileName)){mkdir($textFileName);}
        $textFileName.=date('Y').'/';
        if(!is_dir($textFileName)){mkdir($textFileName);}
        $textFileName.=date('m').'/';
        if(!is_dir($textFileName)){mkdir($textFileName);}
        $textFileName.=date('d').'/';
        if(!is_dir($textFileName)){mkdir($textFileName);}
        $textFileName.=$type.'/';
        if(!is_dir($textFileName)){mkdir($textFileName);}
        $textFileName.=date('H',TIME).'.txt';
        // echo $textFileName;
        textFileWrite(json_encode($data),$textFileName);
    }

    public function convertNumberToWordForHelp($num = false){
        $num = str_replace(array(',', ' '), '' , trim($num));
        if(! $num) {
            return false;
        }
        $num = (int) $num;
        $words = array();
        $list1 = array('', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven',
            'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'
        );
        $list2 = array('', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety', 'hundred');
        $list3 = array('', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion', 'sextillion', 'septillion',
            'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quattuordecillion',
            'quindecillion', 'sexdecillion', 'septendecillion', 'octodecillion', 'novemdecillion', 'vigintillion'
        );
        $num_length = strlen($num);
        $levels = (int) (($num_length + 2) / 3);
        $max_length = $levels * 3;
        $num = substr('00' . $num, -$max_length);
        $num_levels = str_split($num, 3);
        for ($i = 0; $i < count($num_levels); $i++) {
            $levels--;
            $hundreds = (int) ($num_levels[$i] / 100);
            $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' hundred' . ' ' : '');
            $tens = (int) ($num_levels[$i] % 100);
            $singles = '';
            if ( $tens < 20 ) {
                $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '' );
            } else {
                $tens = (int)($tens / 10);
                $tens = ' ' . $list2[$tens] . ' ';
                $singles = (int) ($num_levels[$i] % 10);
                $singles = ' ' . $list1[$singles] . ' ';
            }
            $words[] = $hundreds . $tens . $singles . ( ( $levels && ( int ) ( $num_levels[$i] ) ) ? ' ' . $list3[$levels] . ' ' : '' );
        } //end for loop
        $commas = count($words);
        if ($commas > 1) {
            $commas = $commas - 1;
        }
        return implode(' ', $words);
    }
    function convertNumberToWords($num){
        $num = str_replace(array(',', ' '), '' , trim($num));
        if(! $num) {
            return false;
        }
        $num = (int) $num;
        $words = array();
        $list3 = array('','thousand','lakh', 'crore');
        $list4= array('','billion', 'trillion', 'quadrillion', 'quintillion', 'sextillion', 'septillion',
            'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quattuordecillion',
            'quindecillion', 'sexdecillion', 'septendecillion', 'octodecillion', 'novemdecillion', 'vigintillion'
        );
        $num_length = strlen($num);
        $levels = (int) (($num_length + 2) / 3);
        $max_length = $levels * 3;
        $num = substr('00' . $num, -$max_length);
        $num_levels = str_split($num, 3);
        $fristNumber=$num_levels;
        //return $fristNumber;
        $c=count($num_levels);
        if($c>3){
            $l= $num_levels;

            $fristNumber=array_slice($l, -3, 3, true);
            $lestNumer=$num_levels;
            foreach($fristNumber as $i=>$v){
                unset($lestNumer[$i]);
            }

            $lestNumer=implode('',$lestNumer);

            $lestNumerLength=strlen($lestNumer);
            if($lestNumerLength%3!=0){
                $d= $lestNumerLength/3;
                $intd=intval($d);
                $decimal=$d-$intd;
                $decimal=round($decimal,2);
                $decimal=substr($decimal,2);
                if($decimal==33){
                    $lestNumer='00'.$lestNumer;
                }
                elseif($decimal==67){
                    $lestNumer='0'.$lestNumer;
                }

                /*if() */

            }
            $lestNumer=str_split($lestNumer,3);
            //return $num_levels;
            $count=count($lestNumer);
            foreach($lestNumer as $i=>$n){
                $n=(int)$n;
                $w=$this->convertNumberToWordForHelp($n);
                if(!empty($w)){    
                    $words[]=$w.$list4[$count];
                }

                $count--;
            }
        }
        //return $lestNumer;
        //return $fristNumber;
        $end_num=end($fristNumber);
        $end_word=$this->convertNumberToWordForHelp($end_num);
        array_pop($fristNumber);
        //return $num_levels;
        $fristNumber=implode('',$fristNumber);
        $fristNumber=(int) $fristNumber;
        if(!empty($fristNumber)){
            $fristNumberLength=strlen($fristNumber);
            if($fristNumberLength%2!=0){
                $fristNumber='0'.$fristNumber;
            }
            $fristNumber = str_split($fristNumber, 2);

            $count=count($fristNumber);
            //return $count;
            foreach($fristNumber as $i=>$n){
                /*if($list3[$count]=='billion'){
                if($n>9 && $n<1000){
                $million=(int)$n/10; 
                $w=convertNumberToWord($million);
                }
                }*/

                $w=$this->convertNumberToWordForHelp($n);
                if(!empty($w)){    
                    $words[]=$w.$list3[$count];
                }

                $count--;
            }
        }
        $words[]=$end_word;
        //return   $words;
        return implode('', $words);


    }
    function get_all_user_type(){
        return [
            ['id'=>USER_TYPE_COMMON,'title'=>'Common'],
            ['id'=>USER_TYPE_MPO,'title'=>'MPO'],
            ['id'=>USER_TYPE_MANAGER,'title'=>'Manager'],
            ['id'=>USER_TYPE_RSM,'title'=>'RSM']    
        ];
    }
    function get_all_doctor_type(){
        return[  
            1=>['id'=>1,'title'=>'DVM'],
            2=>['id'=>2,'title'=>'PC'],
            3=>['id'=>3,'title'=>'DC'],
        ];
    }
    function get_all_doctor_category(){
        return [ 
            1=>['id'=>1,'title'=>'A+'],
            2=>['id'=>2,'title'=>'A'],
            3=>['id'=>3,'title'=>'B'],
            4=>['id'=>3,'title'=>'C'],
        ];
    }
    public function percentage($percent,$percentNumber){$percent=floatval($percent);$percentNumber=floatval($percentNumber);$b=$percent*$percentNumber;$a=$b/100;return $a;}

    public function percentageOf($main,$current){
        if($main==0||$current==0 ){
            return 0;
        }
        $main=floatval($main);$current=floatval($current);$b=$current/$main;$a=$b*100;return number_format(($a),2);
    }  
}