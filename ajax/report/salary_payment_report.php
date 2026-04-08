<?php
$dRange         = $_POST['dRange'];

list($startStr, $endStr) = explode(' to ', $dRange);

// ডেট অবজেক্ট তৈরি
$startDate = DateTime::createFromFormat('d-m-Y', $startStr);
$endDate = DateTime::createFromFormat('d-m-Y', $endStr);

// মাসের প্রথম দিন নির্ধারণ
$from = (clone $startDate)->modify('first day of this month')->setTime(0, 0, 0)->getTimestamp();

// মাসের শেষ দিন নির্ধারণ
$to = (clone $endDate)->modify('last day of this month')->setTime(23, 59, 59)->getTimestamp();
$payment_from=strtotime('+1 second',$to);
$payment_to = (clone $endDate)->modify('first day of next month')->modify('last day of this month')->setTime(23, 59, 59)->getTimestamp();


$department_id  = intval($_POST['department_id']);
$type           = @intval($_POST['report_type']);
$reportInfo=[
    'Salary date :'.date('d-m-Y', $from).' to '.date('d-m-Y', $to),
    'Payment Date :'.date('d-m-Y', $payment_from).' to '.date('d-m-Y', $payment_to)
];
$general->getFromToFromString($dRange,$from,$to);

$first_time_from = strtotime(date("Y-m-01", $from));
$last_time_to = strtotime(date("Y-m-t 23:59:59", $to)); 

$allowance=$db->selectAll('salary_allowance','where isActive=1');


$expTypes=[
    V_T_EMPLOYEE_PAY
];
$tSalary=0;
$rData=[];
$salaryData=[];
$salaryDataDetails=[];

$q[]="isActive=1";
if($department_id>0){
    $q[]="department_id=".$department_id;
}
$employees = $db->selectAll('employees', 'where '.implode(' and ',$q));
$general->arrayIndexChange($employees, 'id');



if(!empty($employees)){
    $qa[]="salary_date between $first_time_from and $last_time_to";
    $qa[]='employee_id in('.implode(',',array_keys($employees)).')';
    $employee_salary=$db->selectAll('employee_salary','where '.implode(' and ',$qa));
    $general->arrayIndexChange($employee_salary, 'id');

    if(!empty($employee_salary)){
        $employee_allowance=$db->selectAll('employee_salary_allowance','where salary_id in('.implode(',',array_keys($employee_salary)).')');
        $allowanceData=[];
        $expences=$acc->voucherDetails($expTypes,array_keys($employees),$payment_from,$payment_to);
        $expencesData=[];
        $jArray[fl()]=$expences;

        foreach($employee_allowance as $ea){
            $allowanceData[$ea['salary_id']][$ea['salary_allowance_id']]=$ea;
        }
        foreach($expences as $e){
            $monthYear = date("m-Y", $e['createdOn']);
            if(!isset($expencesData[$monthYear][$e['reference']])){
                $expencesData[$monthYear][$e['reference']]=0;
            }
            $expencesData[$monthYear][$e['reference']]+=$e['amount'];
        }
        $jArray[fl()]=$expencesData;
        if($type==1){

            foreach($employee_salary as $es){

                $monthYear = date("m-Y", $es['salary_date']);
                $pay_month_year=date("m-Y", strtotime("+1 month", $es['salary_date']));
                if(!isset($salaryData[$monthYear])){
                    $payment=0;
                    if(isset($expencesData[$pay_month_year])){
                        foreach($expencesData[$pay_month_year] as $e){
                            $payment+=$e;
                        }
                    }

                    $salaryData[$monthYear]=[
                        'month'=>date("F Y", $es['salary_date']),
                        'salary'=>0,
                        'total' =>0,
                        //'payment' =>isset($expencesData[$monthYear][$e['reference']])?$expencesData[$monthYear][$e['reference']]:0,
                        'payment' =>$payment,
                        'allowance' =>[],
                    ];
                }
                $salaryData[$monthYear]['salary']+=$es['salary'];
                $salaryData[$monthYear]['total']+=$es['total'];

                if(isset($allowanceData[$es['id']])){
                    foreach($allowanceData[$es['id']] as $al){
                        if(!isset($salaryData[$monthYear]['allowance'][$al['salary_allowance_id']])){
                            $salaryData[$monthYear]['allowance'][$al['salary_allowance_id']]=0;
                        }
                        $salaryData[$monthYear]['allowance'][$al['salary_allowance_id']]+=$al['amount']; 
                    }

                }
            }
            $jArray[fl()]=$salaryData;
        }
        else{
            $departments=$db->selectAll('employee_department','where isActive=1');
            $general->arrayIndexChange($departments, 'id'); 
            $target_employee=[];
            foreach($employee_salary as $es){
                $target_employee[$es['employee_id']]=$employees[$es['employee_id']];
            }
            
            $employee_balance=[];
            foreach($target_employee as $employee){
                $empHead= $acc->getEmployeeHead($employee,$jArray);
                if($empHead!=false){
                    $balance=$acc->headBalance($empHead);
                    $employee_balance[$employee['id']]=$balance;
                }
            }
            $jArray[fl()]=$employee_balance;
            $expencesData=[];
            foreach($expences as $e){
                if(!isset($expencesData[$e['reference']])){
                    $expencesData[$e['reference']]=0;
                }
                $expencesData[$e['reference']]+=$e['amount'];
            }

            foreach($employee_salary as $es){
                if(!isset($salaryDataDetails[$es['employee_id']])){
                    $e=$employees[$es['employee_id']];
                    $salaryDataDetails[$es['employee_id']]=[
                        'employee'=>$e['name'],
                        'department'=>$departments[$e['department_id']]['title'],
                        'salary'=>0,
                        'total' =>0,
                        'payment' =>$expencesData[$es['employee_id']] ?? 0,
                        'allowance' =>[],
                    ];
                }
                $salaryDataDetails[$es['employee_id']]['salary']+=$es['salary'];
                $salaryDataDetails[$es['employee_id']]['total']+=$es['total'];

                if(isset($allowanceData[$es['id']])){
                    foreach($allowanceData[$es['id']] as $al){
                        if(!isset($salaryDataDetails[$es['employee_id']]['allowance'][$al['salary_allowance_id']])){
                            $salaryDataDetails[$es['employee_id']]['allowance'][$al['salary_allowance_id']]=0;
                        }
                        $salaryDataDetails[$es['employee_id']]['allowance'][$al['salary_allowance_id']]+=$al['amount']; 
                    }

                }
            }
        }
    }
}


if($type==1&&!empty($salaryData)){
    $serial=1;
    foreach($salaryData as $sd){
        $data=[
            's'=>$serial++,
            'm'=>$sd['month'],
            'bs'=>$general->numberFormat($sd['salary']),
            't'=>$general->numberFormat($sd['total']),
            'p'=>$general->numberFormat($sd['payment'])
        ];

        foreach($allowance as $b){
            $al=(isset($sd['allowance'][$b['id']]))?$sd['allowance'][$b['id']]:0;
            $data['b_'.$b['id']]=$general->numberFormat($al);
        }

        $rData[]=$data;
    }
}
elseif($type==2&&!empty($salaryDataDetails)){
    $serial=1;
    foreach($salaryDataDetails as $employee_id=>$sd){
        $data=[
            's'=>$serial++,
            'n'=>$sd['employee'],
            'd'=>$sd['department'],
            'bs'=>$general->numberFormat($sd['salary']),
            't'=>$general->numberFormat($sd['total']),
            'p'=>$general->numberFormat($sd['payment']),
            'ba'=>$general->numberFormat($employee_balance[$employee_id]),
        ];

        foreach($allowance as $b){
            $al=(isset($sd['allowance'][$b['id']]))?$sd['allowance'][$b['id']]:0;
            $data['b_'.$b['id']]=$general->numberFormat($al);
        }

        $rData[]=$data;
    }
}

$head=[
    ['title'=>'SL','key'=>'s','hw'=>5]
];
if($type==1){
    $head[]=['title'=>'Month','key'=>'m'];
}
else{
    $head[]=['title'=>'Name','key'=>'n'];
    $head[]=['title'=>'Department','key'=>'d','al'=>'r'];
}

$head[]=['title'=>'Basic Salary','key'=>'bs','al'=>'r'];

foreach($allowance as $b){
    $head[]=['title'=>$b['title'],'key'=>'b_'.$b['id'],'al'=>'r'];
}
$head[]=['title'=>'Total','key'=>'t','al'=>'r'];
$head[]=['title'=>'Payment','key'=>'p','al'=>'r'];
if($type==2){
    $head[]=['title'=>'Balance','key'=>'ba','al'=>'r'];
}


$fileName='salary_payment_report_'.TIME.rand(0,999).'.txt';
$report_data=array(
    'name'      => 'empSalaryReport',
    'title'     => 'Employee Salary Report',
    'info'      => $reportInfo,
    'fileName'  => $fileName,
    'head'=>$head,
    'data'=>$rData
);


$jArray[__LINE__]=$report_data;
$gAr['report_data']= $report_data;
textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
$jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
$jArray['status']=1;
