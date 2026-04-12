<?php
class Company{
    private DB $db;
    private General $general;
    private string $table = 'companys';
    public $lastErrorLine = null;

    public function __construct(DB $db, General $general){
        $this->db = $db;
        $this->general = $general;
    }

    public function getById($id){
        return $this->db->get_rowData($this->table,'id',intval($id));
    }

    public function add(array $input){
        $data = $this->prepareData($input,true);
        if($data === false){
            return false;
        }
        $package = $this->getPackageData(intval($data['package_id'] ?? 0));
        if($package === false){
            return false;
        }

        $user_group_id = $this->getPackageUserGroupId($package);
        if($user_group_id<1){
            setMessage(66);
            $this->lastErrorLine = fl();
            return false;
        }

        $group = $this->db->adminGroupInfoByID($user_group_id);
        if(empty($group)){
            setMessage(63,'User group');
            $this->lastErrorLine = fl();
            return false;
        }

        $userData = $this->prepareUserData($input,$data,$user_group_id);
        if($userData === false){
            return false;
        }

        if(defined('USER_ID')){
            $this->db->arrayUserInfoAdd($data);
        }

        $this->db->transactionStart();
        $company_id = $this->db->insert($this->table,$data,true);
        if(!$company_id){
            setMessage(66);
            $this->lastErrorLine = fl();
            $this->db->transactionStop(false);
            return false;
        }

        $userData['company_id'] = $company_id;
        if(defined('USER_ID')){
            $this->db->arrayUserInfoAdd($userData);
        }

        $user_insert = $this->db->insert('users',$userData,true);
        if(!$user_insert){
            setMessage(66);
            $this->lastErrorLine = fl();
            $this->db->transactionStop(false);
            return false;
        }

        $this->db->transactionStop(true);
        return $company_id;
    }

    public function edit($id,array $input){
        $id = intval($id);
        if($id<1){
            setMessage(66);
            $this->lastErrorLine = fl();
            return false;
        }

        $company = $this->getById($id);
        if(empty($company)){
            setMessage(66);
            $this->lastErrorLine = fl();
            return false;
        }

        $data = $this->prepareData($input,false,$id);
        if($data === false){
            return false;
        }

        if(defined('USER_ID')){
            $this->db->arrayUserInfoEdit($data);
        }

        return $this->db->update($this->table,$data,array('id'=>$id));
    }

    private function prepareData(array $input,$isNew=true,$id=0){
        $company_name   = trim((string)($input['company_name'] ?? $input['name'] ?? ''));
        $mobile         = trim((string)($input['mobile'] ?? ''));
        $email          = trim((string)($input['email'] ?? ''));
        $contact_person = trim((string)($input['contact_person'] ?? ''));
        $package_id     = trim((string)($input['package_id'] ?? ''));

        if($company_name==''){
            setMessage(36,'Company name');
            $this->lastErrorLine = fl();
            return false;
        }
        if($mobile==''){
            setMessage(36,'Mobile number');
            $this->lastErrorLine = fl();
            return false;
        }
        if($email==''){
            setMessage(36,'Email');
            $this->lastErrorLine = fl();
            return false;
        }
        if($contact_person==''){
            setMessage(36,'Contact person');
            $this->lastErrorLine = fl();
            return false;
        }
        if(!$this->general->bangladeshiMobileCheck($mobile)){
            setMessage(63,'Mobile number');
            $this->lastErrorLine = fl();
            return false;
        }
        if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
            setMessage(63,'Email');
            $this->lastErrorLine = fl();
            return false;
        }
        if($this->isDuplicate('mobile',$mobile,$id)){
            setMessage(7,'Mobile number');
            $this->lastErrorLine = fl();
            return false;
        }
        if($this->isDuplicate('email',$email,$id)){
            setMessage(7,'Email');
            $this->lastErrorLine = fl();
            return false;
        }

        if($isNew && $package_id==''){
            $package_id = (string)$_ENV['FREE_PACKAGE_ID'];
        }

        $data = array(
            'name'           => $company_name,
            'mobile'         => $mobile,
            'email'          => $email,
            'contact_person' => $contact_person
        );

        if($package_id!=''){
            $data['package_id'] = $package_id;
        }

        return $data;
    }

    private function prepareUserData(array $input,array $companyData,int $group_id){
        $username = trim((string)($input['username'] ?? ''));
        $password = (string)($input['password'] ?? '');

        if($username==''){
            setMessage(36,'Username');
            $this->lastErrorLine = fl();
            return false;
        }
        if($password==''){
            setMessage(36,'Password');
            $this->lastErrorLine = fl();
            return false;
        }
        if($this->db->check_available('users'," where username = '".$this->db->esc($username)."'")==false){
            setMessage(55);
            $this->lastErrorLine = fl();
            return false;
        }

        $salt = md5(rand(0,9).'t'.rand(0,9).'a@'.rand(0,9).'Q'.rand(0,9).'u'.rand(0,9).'W');
        $encPas = md5($password.$salt);

        return array(
            'base_id'       => 0,
            'group_id'      => $group_id,
            'employee_id'   => 0,
            'company_id'    => 0,
            'ledger_id'     => 0,
            'name'          => $companyData['contact_person'] ?: $companyData['name'],
            'mobile'        => $companyData['mobile'],
            'email'         => $companyData['email'],
            'address'       => '',
            'type'          => 0,
            'username'      => $username,
            'password'      => $encPas,
            'password_salt' => $salt,
            'data'          => json_encode(array()),
            'isActive'      => 1
        );
    }

    private function getPackageData(int $package_id){
        if($package_id<1){
            setMessage(63,'Package');
            $this->lastErrorLine = fl();
            return false;
        }

        $package = $this->db->get_rowData('packages','id',$package_id);
        if(empty($package)){
            setMessage(63,'Package');
            $this->lastErrorLine = fl();
            return false;
        }

        return $package;
    }

    private function getPackageUserGroupId(array $package): int{
        $meta = $package['meta'] ?? '';
        if($meta==''){
            return 0;
        }

        $meta_data = json_decode($meta,true);
        if(!is_array($meta_data)){
            return 0;
        }

        return intval($meta_data['user_group'] ?? 0);
    }

    private function isDuplicate($field,$value,$id=0){
        $value = $this->db->esc($value);
        $where = "where `$field` = '$value'";
        if($id>0){
            $where .= " and `id`!='".intval($id)."'";
        }
        $row = $this->db->getRowData($this->table,$where);
        return !empty($row);
    }
}
