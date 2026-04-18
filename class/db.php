<?php
// include 'connection.php';
class DB{
    private $con;
    private $general;
    public $allModules=array();
    public $allModulePermissions=array();
    public $allPermissions=array();
    public $allBrands=array();
    public $allLangText=array();
    private $all_users=[];
    private $all_base=[];
    private $allDistricts=array();
    private $company_data=[];
    public function __construct($general){
        $this->general  = $general;
        $GLOBALS['connection'] = mysqli_connect($_ENV['DB_SERVER'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_DATABASE']) or die('Oops connection error 1'.mysqli_connect_error());
	    mysqli_set_charset($GLOBALS['connection'],'utf8mb4');
        $this->con=$GLOBALS['connection'];
    }
    public function l($slug){
        return $slug;
    }
    public function allUsers($query=''){
        $users=$this->selectAll('users','where group_id!='.SUPERADMIN_USER.' '.$query,'id,group_id,name,username,data,base_id');
        if(!empty($users)){
            $this->general->arrayIndexChange($users,'id');
        }
        return $users;
    }
    public function allBase($query=''){
        $base=$this->selectAll('base',$query,'id,title');
        if(!empty($base)){
            $this->general->arrayIndexChange($base,'id');
        }
        return $base;
    }
    function userInfoByID($id){
        if(array_key_exists($id,$this->all_users)){
            $e=$this->all_users[$id];
        }
        else{
            $e=$this->get_rowData('users','id',$id);
            $this->all_users[$id]=$e;
        }
        return $e;
    }
    function baseInfoByID($id){
        if(array_key_exists($id,$this->all_base)){
            $e=$this->all_base[$id];
        }
        else{
            $e=$this->get_rowData('base','id',$id);
            $this->all_base[$id]=$e;
        }
        return $e;
    }
    public function allGroups($query=''){
        return $this->selectAll('user_group','where id!='.SUPERADMIN_USER.' and isActive=1 '.$query);
    }
    public function adminGroupInfoByID($group_id){
        return $this->get_rowData('user_group','id',$group_id);
    }
    public function  arrayUserInfoAdd(&$array){
        $array['createdBy']=USER_ID;
        $array['createdOn']=TIME;
        $array['modifiedBy']=USER_ID;
        $array['modifiedOn']=TIME;
    }
    public function arrayUserInfoEdit(&$array){
        $array['modifiedBy']=USER_ID;
        $array['modifiedOn']=TIME;
    }
    public function permissionSetForModule($group_id,$module_id,$st){
        $data = array('module_id'=>$module_id,'group_id'=>$group_id);
        $this->delete('module_permission',$data);
        if($st==1){
            $this->insert('module_permission',$data);
        }
    }
    public function permissionSetForPermission($group_id,$permission_id,$st){
        $data = array('permission_id'=>$permission_id,'group_id'=>$group_id);
        $this->delete('user_permission_assign',$data);
        if($st==1){
            $this->insert('user_permission_assign',$data);
        }
    }
    public function update_by_id($id,$table,$data){
        return $this->update($table,$data,['id'=>$id]);
    }
    public function update(string $table,array $data,array $where,string $echo='No',array &$jArray=[]){
        $count = count($data);$where_count = count($where);$sql = "UPDATE `".$table."` SET";
        $start = 0;
        foreach ($data as $k => $v){$start = $start + 1;if($start == $count){ $sql .= " `".$k."` = '".$this->esc($v)."'"; } else { $sql .= " `".$k."` = '".$this->esc($v)."', "; }
        }
        $sql .= " WHERE ";
        if($where_count == 1){ foreach($where as $m => $n) { $sql .= "`".$m."` = '".$this->esc($n)."'"; } }
        else{$x=0;foreach($where as $m=>$n){
            $x = $x + 1;if($x == $where_count){$sql.= "`".$m."`='".$this->esc($n)."'";}else{$sql.= "`".$m."`='".$this->esc($n)."' and ";}}}
        if($echo=='array'){$jArray['update_query'][]=$sql;}
        else{if($echo != 'No'){echo '<pre>'.$sql.'</pre>';}}
        $update = mysqli_query($this->con,$sql);
        if($echo=='array'){$jArray['update_error'][]=mysqli_error($this->con);}
        else{if(($echo != 'No')){echo mysqli_error($this->con);}}
        if($update){return true;}else{return false;}
    }

    /**
    * Insert data to table
    * @param (string) table name
    * @param (array) inserted data
    * @param (bool) if need mysqli_insert_id then true
    * @param (string) array/anything
    * @param (array) if $echo set array then here set value
    * @return bool/int
    */
    public function insert($table,$data,$getId=false,$echo='No',&$jArray=array()){
        $count = count($data);
        $sql = "INSERT INTO `".$table."` (";
        $start=0;
        foreach($data as $k=>$v){$start=$start+1;if($start==$count){$sql.= "`".$k."`";}else{$sql.="`".$k."`,";}}
        $sql.= ") value (";
        $number=0;
        foreach($data as $k=>$v){$number=$number+1;if($number==$count){$sql.="'".$this->esc($v)."'";}else{$sql.="'".$this->esc($v)."', ";}}
        $sql.= ")";
        if($echo != 'No'&&$echo!='array'){
            echo '<pre>'.$sql.';</pre>';
        }
        $insert=mysqli_query($this->con,$sql);
        if($echo=='array'){
            $jArray[__LINE__][]=$sql;
        }
        else{
            if($echo != 'No'&&$echo!='array'){
                echo '<pre>'.mysqli_error($this->con).'</pre>';
            }
        }
        if(mysqli_error($this->con)!=''){
            $backtrace = debug_backtrace();
            //echo mysqli_error($this->con);
            $this->general->createLog('insertError',array('backtrace'=>$backtrace,'query'=>$sql,'SQL Query error. '.mysqli_error($this->con)));
        }
        if($echo=='array'){
            $jArray[__LINE__][]=mysqli_error($this->con);
        }
        if($insert){
            if($getId=='getId'||$getId===true){return mysqli_insert_id($this->con);}
            else{
                return true;
            }
        }else{
            return false;
        }
    }
    public function delete(string $table, array $where,$echo = 'No',&$jArray=[]){
        $where_count = count($where);
        $sql = "DELETE FROM `".$table."` WHERE ";
        if($where_count == 1){foreach($where as $m => $n){$sql .= "`".$m."` = '".$this->esc($n)."'";}}
        else{$x = 0;foreach($where as $m => $n){$x = $x + 1;if($x == $where_count){$sql .= "`".$m."` = '".$this->esc($n)."'";} else {$sql .= "`".$m."` = '".$this->esc($n)."' and ";}}}
        if($echo != 'No'){
            if($echo=='array'){
                $jArray[fl()][]=$sql;
            }
            else {
                echo "<pre>$sql;</pre>";
            }
        }
        $delete = mysqli_query($this->con,$sql);

        if($echo != 'No'){
            if($echo=='array'){
                $jArray[fl()][]=mysqli_error($this->con);
            }
            else {
                echo '<pre>'.mysqli_error($this->con).'</pre>';
            }
        }
        if($delete)return true;else return false;
    }
    public function lastError(){return mysqli_error($this->con);}
    public function esc($string){
        if (is_null($string)) {
            $string = '';
        }
        return mysqli_real_escape_string($this->con,$string);}
    public function get_data($tableName, $where, $whereValue, $rowName,$echo = 'No'){
        if($echo != 'No'){
            echo "SELECT * FROM $tableName WHERE $where = '$whereValue'<br>";
        }
        $sql = mysqli_query($this->con,"SELECT * FROM $tableName WHERE $where = '$whereValue'");
        $row = mysqli_fetch_assoc($sql);
        return $row["$rowName"];
    }
    public function getData(string $tableName, string $where, $rowName,$echo = 'No'){
        if($echo != 'No'){
            echo "SELECT * FROM $tableName $where '<br>";
        }
        $sql = mysqli_query($this->con,"SELECT * FROM $tableName $where");
        $row = mysqli_fetch_assoc($sql);
        return $row["$rowName"];
    }
    public function get_rowData(string $tableName, string $where, $whereValue,$echo='No',&$jArray=array()){
        $query="SELECT * FROM $tableName WHERE $where = '$whereValue'";
        if($echo=='array'){
            $jArray[fl()][]=$query;
        }
        else{
            if($echo != 'No'){echo '<pre>'.$query.'</pre>';}
        }
        $sql = mysqli_query($this->con,$query);
        if(mysqli_error($this->con)!=''){
            $backtrace = debug_backtrace();
            //echo mysqli_error($this->con);
            textFileWrite(array('backtrace'=>$backtrace,'query'=>$query,'SQL Query error. '.mysqli_error($this->con)));
        }
        $row = mysqli_fetch_assoc($sql);
        if($echo=='array'){
            $jArray[fl()][]=mysqli_error($this->con);
        }
        else{
            if($echo != 'No'){echo mysqli_error($this->con).'<br>';}
        }
        return $row;

    }
    public function getRowData($tableName,$where,$echo='No',&$jArray=[]){
        $query="SELECT * FROM $tableName $where limit 1";
        if($echo=='array'){
            $jArray[fl()][]=$query;
        }
        else{
            if($echo != 'No'){echo '<pre>'.$query.'</pre>';}
        }
        $sql = mysqli_query($this->con,$query);
        $row = mysqli_fetch_assoc($sql);
        if(mysqli_error($this->con)!=''){
            $backtrace = debug_backtrace();
            textFileWrite($backtrace);
            textFileWrite($query);
            textFileWrite(mysqli_error($this->con));
        }
        if($echo=='array'){
            $jArray[fl()][]=mysqli_error($this->con);
        }
        else{
            if($echo != 'No'){echo mysqli_error($this->con).'<br>';}
        }
        return $row;
    }
    public function fetchQuery($query,$echo = 'No',&$jArray=array()){
        if($echo != 'No'){
            if($echo!='array'){
                echo '<pre>'.$query.'</pre>';   
            }
            else{
                $jArray['fetch_query'][]=$query;
            }
        }
        $result = [];
        $all = mysqli_query($this->con,$query);
        while($table= mysqli_fetch_assoc($all)){$result[] = $table;}
        if($echo != 'No'){
            if($echo!='array'){
                echo mysqli_error($this->con).'<br>';
            }
            else{
                $jArray['fetch_query_error'][]=mysqli_error($this->con);
            }
        }
        if(mysqli_error($this->con)!=''){
            echo  mysqli_error($this->con);
            $backtrace = debug_backtrace();
            //$this->general->printArray($backtrace);
            textFileWrite(array('backtrace'=>$backtrace,'query'=>$query,'SQL Query error. '.mysqli_error($this->con)));
        }
        return $result;
    }
    public function runQuery($query,$echo = 'No',&$jArray=[]){
        if(($echo != 'No')&&$echo!='array'){echo '<pre>'.$query.'</pre>';}
        elseif($echo=='array'){
            $jArray['runQuery'][]=$query;
        }
        $all = mysqli_query($this->con,$query);
        if($echo != 'No'){
            if($echo!='array'){
                echo mysqli_error($this->con).'<br>';
            }
            else{
                $jArray['runQuery_error'][]=mysqli_error($this->con);
            }
        }
        return $all;
    }
    public function runQueryFetch($res){return mysqli_fetch_assoc($res);}
    public function selectAll($table, $where='', $fields='*', $echo = 'No',&$jArray=[]){
        $result = [];
        $data = $this->esc($table);
        if($fields == ''){$fields = '*';}
        //            if($echo == 'No'){
        $query = "SELECT ".$fields." FROM $data $where";
        if($echo=='array'){
            $jArray['selectAllQ'][]=$query;
        }
        else{
            if($echo != 'No'){echo '<pre>'.$query.'</pre>';}
        }
        $all = mysqli_query($this->con,$query);
        while($table= mysqli_fetch_assoc($all)){$result[] = $table;}
        if(mysqli_error($this->con)!=''){
            $backtrace = debug_backtrace();
            $v=array('time'=>date('d-m-Y h:i:s'),'b'=>$backtrace,'query'=>$query,'SQL Query error. '.mysqli_error($this->con));
            $this->general->createLog('dbSelectAllError',$v);
        }
        //if(mysqli_error($this->con)!=''){echo $query.' $err='. mysqli_error($this->con);}
        if($echo=='array'){
            $jArray['selectAllE'][]=mysqli_error($this->con);
        }
        else{
            if($echo != 'No'){echo mysqli_error($this->con).'<br>';}
        }
        return $result;
    }
    /**
    * This method return user access.<br />
    * @param (string) Table name
    * @param (string) Table column name which will be search
    * @param (array) Columnm ID collection 
    * @param (string) if query then start with "and" or any other statment canbe included(order by,group by) 
    * @param (bool) array index change 
    * @param (string) is echo 
    * @param (array) array address 
    * @param (bool) return array index change by columnId 
    * @return array
    */
    public function selectAllByID($table,$columnID,$columnArray,$extraQuery='',$indexChangeByID=true,$echo='No',&$jArray=[]){
        $data=$this->selectAll($table,'where '.$columnID.' in('.implode(',',$columnArray).') '.$extraQuery,'',$echo,$jArray);
        if($indexChangeByID===true){
            $this->general->arrayIndexChange($data,$columnID);
        }
        return $data;
    }

    /**
    * This method return user access.<br />
    * @param (int) permission number which provide module permission page.
    * @param (int/bool) if need spesific section then true.
    * @return bool/array 
    */
    function permission(int $perID):bool{
    if(GROUP_ID==SUPERADMIN_USER)return true;
    $p=$this->getRowData('user_permission_assign','where permission_id='.$perID.' and group_id='.GROUP_ID);
    if(!empty($p)){
    return true;
}
else{
    return false;
}
}
/**
* This method return user access.<br />
* @param (int) module id which provide module permission page.
* @param (int/bool) if need spesific branch then true.
* @return bool/array depend on $bID
*/
public function modulePermission(int $module_id):bool{
    if(empty($this->allModules)){
        $am=$this->selectAll('module','where isActive=1');
        $this->general->arrayIndexChange($am,'id');
        $this->allModules=$am;
    }
    if(array_key_exists($module_id,$this->allModules)){
        if(empty($this->allModulePermissions)){
            $am=$this->selectAll('module_permission','where group_id='.GROUP_ID);
            $ad=array();
            foreach($am as $a){
                $ad[$a['module_id']]=$a;
            }
            $this->allModulePermissions=$ad;
        }
        if(array_key_exists($module_id,$this->allModulePermissions)||GROUP_ID==SUPERADMIN_USER){
            return true;
        }
    }
    return false;
}

public function check_available($table, $where ){
    $total = $this->selectAll($table,$where);$count = count($total);
    //echo "SELECT * FROM $table   $where<br>"; print_r($total); echo '<br>'.$count;
    if($count>0){return false;}else{return true;}
}
public function checkUrlAvailable($url,$edit=array()){
    $pWhere="where pUrl='".$url."'";
    $cWhere="where pcParent=0 and pcUrl='".$url."'";
    $spWhere="where spUrl='".$url."'";
    $bWhere="where bUrl='".$url."'";
    if(!empty($edit)){
        if(isset($edit['p'])){$pWhere.=' and pID!='.$edit['p'];}
        if(isset($edit['c'])){$cWhere.=' and pcID!='.$edit['c'];}
        if(isset($edit['sp'])){$spWhere.=' and spID!='.$edit['sp'];}
        if(isset($edit['b'])){$bWhere.=' and bID!='.$edit['b'];}
    }
    $reservUrl=array(
        'category','admin','ajax','cpanel','cart','checkout','logout','account','root','blog','blogs','fb_login','calculator'
    );
    if(in_array($url,$reservUrl)){return false;}
    elseif(intval($url)>0){return false;}
    elseif($this->check_available($this->general->table(10),$pWhere)==false){return false;}
    elseif($this->check_available($this->general->table(7),$cWhere)==false){return false;}
    elseif($this->check_available($this->general->table(9),$spWhere)==false){return false;}
    elseif($this->check_available($this->general->table(25),$bWhere)==false){return false;}
    return true;
}

public function login($userLogin){
    $thisUser = $this->get_rowData($this->general->table(5),'username',$userLogin);
    if(empty($thisUser)){
        unset($_SESSION['halfuser']);
        header('location: '.@$this->general->stting->url);
        exit();

    }
}

public function dragNdropOrder($table,$no,$where=''){
    $array=$this->general->tableOrdArray($no);
    $id         = $array['id'];
    $order      = $array['order'];
    $title      = $array['title'];
    $mainArray  = $this->selectAll($this->general->table($table),$where.' order by '.$order);
    ?>
    <!--<script type="text/javascript" src="<?=URL?>/js/jquery-ui-1.7.1.custom.min.js"></script>-->
    <style type="text/css">
        #contentWrap {
            width: 700px;
            margin: 0 auto;
            height: auto;
            overflow: hidden;
        }
        #contentTop {
            width: 600px;
            padding: 10px;
            margin-left: 30px;
        }
        #contentLeft {
            float: left;
            width:100%;
        }
        #contentLeft li {
            background: url("images/left-meny.png") repeat-y scroll left top rgba(0, 0, 0, 0);
            border: 1px solid #CCCCCC;
            color: #0E0E0E;
            list-style: none outside none;
            margin: 0 0 4px;
            padding: 10px 0 10px 18px;
        }
        #contentLeft li:hover{
            cursor: move;
        }    
    </style>
    <script type="text/javascript">
        $(document).ready(function(){                    
            $(function() {
                $("#contentLeft ul").sortable({ opacity: 0.6, cursor: 'move', update: function() {
                    $('#contentLeft').hide();
                    $('#orderLoading').show();
                    var d=$(this).sortable("serialize");
                    var find = 'recordsArray';
                    var re = new RegExp(find, 'g');
                    dd = d.replace(re, '');
                    var find = "[[\]]=";
                    var re = new RegExp(find, 'g');
                    dd = dd.replace(re, '');
                    var find = "&";
                    var re = new RegExp(find, 'g');
                    dd = dd.replace(re, ',');
                    var order = 'recordsArray='+dd + '&data_orde=ord&actn=<?=$table?>&trg=<?=$no?>'; 
                    $.post(ajUrl, order, function(theResponse){
                        //                        t(theResponse);
                        $('#contentLeft').show();
                        $('#orderLoading').hide();
                    });
                    }                                  
                });
            });

        });    
    </script>
    <div id="contentWrap">
        <div id="contentLeft">
            <ul>
                <?php
                foreach($mainArray as $h){
                    ?>
                    <li id="recordsArray_<?=$h[$id]?>"><?=$h[$order] . " ) " . $h[$title]?></li>
                    <?php } ?>
            </ul>
        </div>
        <div id="orderLoading" style="display: none;"><h1>Working Please wait and do not reload now</h1></div>
    </div>
    <?php
}
/**
* Set the block dimensions accounting for page breaks and page/column fitting
* @param (number) database table name
* @param (string) select tag name and id
* @param (string) table colum id which set in option value
* @param (string) table colum title which show in option
* @param (string) value will be selected
* @param (string) select tag class name keep '' for select2
* @param (string) deafult No. Possible value are  No,y
* @param (string) deafult n. Which embed in select tag Possible value any string 
* @param (string) deafult ''. have option select. Possible value any '',n. when !=n or !='' then first select option of that text
* @param (string) deafult ''. start with and which embeded after isActive=1
* @param (string) deafult ''. In any value then show query
* @return no return
*/
public function dropdownInput
($table,$inputName,$columnID,$columnTitle,$currentValue='',$inputClassName='form-control',$required='No',$script='n',$haveSelect='',$query='',$echo='No')
{
    if($inputClassName==''){$inputClassName='form-control select2';}
    ?>
    <select name="<?=$inputName?>" id="<?=$inputName?>" class="<?=$inputClassName?>" <?=($required=='y')?'required':''?> <?=($script!='n')?$script:''?> >
        <?php
        if($haveSelect!='n'){
            if($haveSelect==''){
                ?>
                <option value="">Select</option>
                <?php
            }
            else{
                ?>
                <option value=""><?=$haveSelect?></option>
                <?php
            }
        }
        $inputs= $this->selectAll($this->general->table($table),'where isActive=1 '.$query.' order by '.$columnTitle,'',$echo);
        $this->general->arrayContentShow($inputs);
        foreach($inputs as $i){
            ?>
            <option value="<?=$i[$columnID]?>" <?=$this->general->selected($currentValue,$i[$columnID])?>><?=$i[$columnTitle]?></option>
            <?php
        }
        ?>
    </select>
    <?php
}
public function headValue($key,$bID){
    $settings = $this->getRowData($this->general->table(76),"where ahsKey='".$key."' and bID=".$bID);
    if(empty($settings)){
        $data=array(
            'ahsKey'=> $key,
            'bID'   => $bID
        );
        $this->insert($this->general->table(76),$data);
        return 0;
    }
    return $settings['hID'];
}
public function transactionStart(){
    $v=$this->runQuery('SET AUTOCOMMIT=0;');
    $q=$this->runQuery('START TRANSACTION;');
    $q=$this->runQuery('begin;');
    //            var_dump('$v');echo '<br>';
    //            var_dump($v);echo '<br>';
    //            var_dump('$q');echo '<br>';
    //            var_dump($q);echo '<br>';
    return true;
}
/**
* close alrady started transecion
* @param (bool) true=commite false=rollback
* @return void
*/
public function transactionStop($ac){
    if($ac===true){$ac='COMMIT';}else{$ac='ROLLBACK';}
    $this->runQuery($ac);
}
public function reportCacheGet($key){
    $this->runQuery('DELETE FROM report_cach WHERE validity<'.TIME);
    $r=$this->get_rowData('report_cach','cash_key',$key);
    if(!empty($r)){
        return $r['value'];
    }else{return false;}
}
public function reportCacheSet($key,$value,$expeir=0){
    /*$data=array(
    'rcKey'     => rand(0,9999).$key,
    'rcValidity'=> 0,
    'rcValue'   => json_encode(debug_backtrace())
    );
    $this->insert($this->general->table(40),$data);*/
    $expeir=intval($expeir);if($expeir<1)$expeir=strtotime('+1 hour',TIME);
    $r=$this->get_rowData('report_cach','cash_key',$key);
    if(empty($r)){
        $data=array(
            'cash_key'     => $key,
            'validity'    => $expeir,
            'value'   => $value
        );
        return $this->insert('report_cach',$data);
    }
    else{
        $data=array(
            'validity'    => $expeir,
            'value'   => $value
        );
        $where=array('key'=>$key);
        return $this->update('report_cach',$data,$where);
    }
}
public function actionLogCreate($key,$description){

    $a['data']=$description;
    /*if(is_array($description)){

    foreach($description as $k=>$d){
    if(intval($k)==0){
    $a[$k]=$d;
    }else{
    $a[]=$d;
    }

    }
    }
    else{
    if(strlen($description)<50){
    $dd=debug_backtrace();
    $this->general->createLog('oldStyleLog',$dd);
    }
    $a['data']=$description;
    }*/
    $data=array(
        'log_key'=>$key,
        'time'=>TIME,
        'details'=>json_encode($a)
    );
    return $this->insert('action_log',$data);


}

public function dropdownInputFromArray
($dataArray,$inputName,$columnID,$columnTitle,$currentValue='',$required='No',$inputClassName='form-control',$script='n',$haveSelect='')
{
    if($inputClassName==''){$inputClassName='form-control select2';}
    ?>
    <select name="<?=$inputName?>" id="<?=$inputName?>" class="<?=$inputClassName?>" <?=($required=='y')?'required':''?> <?=($script!='n')?$script:''?> >
        <?php
        if($haveSelect!='n'){
            if($haveSelect==''){
                ?>
                <option value="">Select</option>
                <?php
            }
            else{
                ?>
                <option value=""><?=$haveSelect?></option>
                <?php
            }
        }
        if(!empty($dataArray)){
            $this->general->arrayContentShow($dataArray);
            echo $currentValue;
            foreach($dataArray as $i){
                ?>
                <option value="<?=$i[$columnID]?>" <?=$this->general->selected($currentValue,$i[$columnID])?>><?=$i[$columnTitle]?></option>
                <?php
            }
        }
        ?>
    </select>
    <?php
}

public function getUserWiseBase(int $user_type){
    $q = ['status=1'];
    if($user_type == USER_TYPE_MPO){
        $user=$this->userInfoByID(USER_ID);
        $q[] = "id={$user['base_id']}";
    }
    elseif(in_array($user_type,[USER_TYPE_MANAGER,USER_TYPE_RSM])){
        $assign_base = $this->selectAll(
                'user_manager',
            'where user_id=' . USER_ID . ' and isActive=1',
            'assign_base_id',
        );

        $all_assign = [0];
        if (!empty($assign_base)) {
            $all_assign = array_column($assign_base, 'assign_base_id');
        }

        $q[] = "id in (" . implode(',', $all_assign) . ")";
    }
    return $this->selectAll('base', 'where ' . implode(' and ', $q));
}


function getAllDistricts(){
    if(empty($this->allDistricts)){
        $allDistricts=$this->selectAll('districts','where isActive=1 order by distTitle asc');
        if(!empty($allDistricts)){
            $this->general->arrayIndexChange($allDistricts,'distID');
            $this->allDistricts=$allDistricts;
        }
    }
    return $this->allDistricts;
}
/**
* /
* @param mixed $type
* @param mixed $id
* @param mixed $prefix
* @param mixed $jArray
* @return bool|string
*/
function setAutoCode($type,$id,$prefix='',&$jArray=[]){

    if($type=='purchaseInvoice')  {$prefix='PI';  $tbl= 'purchase';   $tblID='id';  $codeColumn = 'invoice_no';}
    elseif($type=='purchase_mrr_no')  {$prefix='MRR';  $tbl= 'purchase';   $tblID='id';  $codeColumn = 'mrr_no';}
    elseif($type=='sale_return')  {$prefix='SR';  $tbl= 'sale_return';   $tblID='id';  $codeColumn = 'code';}
    elseif($type=='purchaseReturn')  {$prefix='PR';  $tbl= 'purchase_return';  $tblID='id';  $codeColumn = 'invNo';}
    elseif($type=='production')  {  $tbl= 'production_product';  $tblID='id';  $codeColumn = 'batch_no';}
    elseif($type=='products_code')  {$prefix='P';  $tbl= 'products';  $tblID='id';  $codeColumn = 'code';}
    elseif($type=='sale_invoice_no')  {$tbl= 'sale';  $tblID='id';  $codeColumn = 'invoice_no';}
    elseif($type=='purchase_requisition')  {$prefix='PURQ';$tbl= 'purchase_requisition';  $tblID='id';  $codeColumn = 'code';}
    elseif($type=='reject_entry')  {$prefix='RJ';$tbl= 'reject_products';  $tblID='id';  $codeColumn = 'code';}
    elseif($type=='stock_entry')  {$prefix='SE';$tbl= 'products_stock_in';  $tblID='id';  $codeColumn = 'code';}
    elseif($type=='doctor_visit')  {$prefix='DV';$tbl= 'doctor_visit';  $tblID='id';  $codeColumn = 'code';}
    elseif($type=='customer_visit')  {$prefix='CV';$tbl= 'customer_visit';  $tblID='id';  $codeColumn = 'code';}
    elseif($type=='bazar_visit')  {$prefix='BV';$tbl= 'customer_visit';  $tblID='id';  $codeColumn = 'code';}
    elseif($type=='voucher')  {$tbl= 'a_voucher_entry';  $tblID='id';  $codeColumn = 'code';}
    else{return false;}

    $oCode=$prefix.str_pad($id,4,0,STR_PAD_LEFT);
    $data=[
        $codeColumn => $oCode
    ];
    $where=[
        $tblID  => $id
    ]; 
    $update=$this->update($tbl,$data,$where,'array',$jArray);
    if($update){
        return $oCode;
    }
    else{
        return false;
    }
}
function old_sale_return_code_generator(){
    $sale_return = $this->selectAll('sale_return','where code IS NULL');
    if(!empty($sale_return)){
        foreach($sale_return as $s){
            $this->setAutoCode('sale_return',$s['id']);
        }
    }
    return true;
}
function getCategoryData(){
    $use_product_category = $this->get_company_settings('use_product_category');
    $categoryData=[];
    if($use_product_category==1){
        $categories=$this->selectAll('product_category','where isActive=1 order by title asc');
        $this->general->arrayIndexChange($categories,'id');

        if(!empty($categories)){
            foreach($categories as $k=>$c){
                if($c['parent']==0){
                    $categoryData[$c['id']]=[
                        'id'            => $c['id'],
                        'title'            => $c['title'],
                        'childCategory'    => []
                    ];
                }
            }
            foreach($categories as $k=>$c){
                if($c['parent']!=0&&!isset($categoryData[$c['parent']])){
                    $parent = $categories[$c['parent']];
                    $categoryData[$c['parent']]=[
                        'id'            => $parent['id'],
                        'title'            => $parent['title'],
                        'childCategory'    => []
                    ];
                }

                if($c['parent']!=0){
                    if(!isset($categoryData[$c['parent']]['childCategory'])){
                        $categoryData[$c['parent']]['childCategory']=[] ;
                    }
                    $categoryData[$c['parent']]['childCategory'][$c['id']]=[
                        'id'    => $c['id'],
                        'title'    => $c['title']
                    ];  
                }
            }
        }
    }
    return $categoryData; 
}
public function product_price_log(int $product_id,array $product_data,array &$jArray=[]){
    $log_data=['id'=>$product_id,'product_data'=>$product_data];
    $sale_price = $product_data['sale_price'];
    $unit_cost = $product_data['unit_cost'];
    $price_log = $this->getRowData('product_price_log','where product_id='.$product_id.' ORDER by id DESC');
    $data = [
        'product_id'=>$product_id,
        'sale_price'=>$sale_price,
        'unit_cost'=>$unit_cost,
        'time'=>TIME,
    ];
    if(empty($price_log)){
        $data['time']=0;
    }
    else{
        if($sale_price==$price_log['sale_price']&&$unit_cost==$price_log['unit_cost']){return true;}
    }
    $trace = debug_backtrace();
    $log_data['data']=$data;
    $log_data['post_data']=$_POST;
    $log_data['jArray']=$jArray;
    $log_data['call_from']=$trace;
    // ইস্যু একটা পাইছি সেটা ঠিক করছি তাও লগ রেখে দিলাম যাতে অন্য যায়গার সব সময় এর সব কিছু চেক করা যায় মইনুল ২২-০৪-২০২৫ 
    $this->general->createLog('product_price_log',$log_data);
    return $this->insert('product_price_log',$data,false,'array',$jArray);
}
public function getProductData($query='',$type_wise=false){
    $products=$this->selectAll('products','where isActive=1 '.$query.' order by title asc');
    $units=$this->selectAll('unit');
    $this->general->arrayIndexChange($units,'id');    
    $productData=[];
    if(!empty($products)){
        foreach($products as $k=>$p){
            $data = $this->general->getJsonFromString($p['data']);
            $box_unit='';
            $box_unit_quantity=0;
            if($p['box_unit_id']>0){
                if(isset($data['box_unit_quantity'])&&$data['box_unit_quantity']>0){
                    $box_unit_quantity=intval($data['box_unit_quantity']);
                }
                $box_unit=$units[$p['box_unit_id']]['title'];
            }
            if($type_wise){
                $productData[$p['type']][$p['id']]=[
                    'id'                => $p['id'],
                    't'                 => $p['code'].' - '.$p['title'],
                    'pc'                => $p['category_id'],
                    's'                 => floatval($p['sale_price']),
                    'u'                 => $units[$p['unit_id']]['title'],
                    'box_unit'          => $box_unit,
                    'box_unit_quantity' => $box_unit_quantity,
                    'VAT'               => (float)$p['VAT'],
                    'st'                => (float)$p['stock'],
                    'uc'                => (float)$p['unit_cost'],
                    'get_one_free'      => intval(@$data['get_one_free']),
                    'type'              => $p['type'],
                ];
            }
            else{
                $productData[$p['id']]=[
                    'id'            => $p['id'],
                    't'             => $p['code'].' - '.$p['title'],
                    'pc'            => $p['category_id'],
                    's'             => floatval($p['sale_price']),
                    'u'             => $units[$p['unit_id']]['title'] ,
                    'box_unit'          => $box_unit,
                    'box_unit_quantity' => $box_unit_quantity,
                    'VAT'           => (float)$p['VAT'],
                    'st'            => (float)$p['stock'],
                    'uc'            => (float)$p['unit_cost'],
                    'get_one_free'  => intval(@$data['get_one_free']),
                    'type'          =>$p['type'],
                ];
            }
        }
    }
    return $productData; 
}
/**
* Summary of get_company_data
* @return array
*/
public function get_company_data():array{
    if(empty($this->company_data)){
        $co = $this->get_rowData('company','id',1);
        $company_data= $this->general->getJsonFromString($co['data']);
        $this->company_data=$company_data;
    }
    return $this->company_data;
}
/**
* Summary of get_company_settings
* @param string $key
* @return mixed
*/
public function get_company_settings(string $key){
    $company_data = $this->get_company_data();
    $setting = 0;
    if(isset($company_data[$key])){
        $setting = $company_data[$key];
    }
    return $setting;
}
/**
* Summary of company_data_update
* @param mixed $company_data
* @return bool
*/
public function company_data_update($company_data):bool{
    $data=['data'=>json_encode($company_data)];
    $where=['id'=>1];
    return $this->update('company',$data,$where);
}
public function allBase_for_voucher():array{
    $base = $this->selectAll('base','','id,title');
    $this->general->arrayIndexChange($base);
    return [0=>['id'=>'0','title'=>'General'],...$base];
}
public function get_product_price_log_price(int $time,&$jArray=[]){
    return $this->fetchQuery("WITH ranked_data AS ( SELECT t1.id, t1.product_id, t1.time,  t1.sale_price, t1.unit_cost, 'Q1' AS time_group, ROW_NUMBER() OVER (PARTITION BY t1.product_id ORDER BY t1.time DESC) AS rn FROM product_price_log t1 WHERE  t1.time <= $time) SELECT id,  product_id, time, sale_price, unit_cost, time_group FROM ranked_data WHERE rn = 1 ORDER BY product_id;",'array',$jArray);
}

}

