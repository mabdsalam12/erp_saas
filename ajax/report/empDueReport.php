<?php
    $eName = @$_POST['eName'];
    $eMobile = @$_POST['eMobile'];
    $edID = intval($_POST['edID']);
    $q = [];
    if(isset($eName)){      
        if(!empty($eName)){   
            $q[]="eName like '%".$eName."%'";
        }
    }
    if(isset($eMobile)){      
        if(!empty($eMobile)){   
            $q[]="eMobile like '%".$eMobile."%'";
        }
    }
    if($edID>0){
        $q[] = "edID=".$edID;
    }
    $q[]='isActive in(1,0)';
    $sq='where '.implode(' and ',$q);

    $employees=$db->selectAll($general->table(74),$sq); 
    $general->arrayIndexChange($employees,'eID');  
    $eID=[];
    foreach($employees as $e){
        $eID[]=$e['eID'];
    }

    $employeesDue=$db->selectAll($general->table(20),'where sdRef in ('.implode(',',$eID).')'); 
    $general->arrayIndexChange($employeesDue,'sdRef');    
    $designations=$db->selectAll($general->table(72));
    $general->arrayIndexChange($designations,'edID');
   // print_r($designations);
   //print_r($employeesDue);    



    $serial=1;
    $totalCollect=0;

    if(!empty($employeesDue)){
        foreach($employeesDue as $ed){  
            $totalCollect+=$ed['sdCollect'];
            $edID = $employees[$ed['sdRef']]['edID'];
            $rData[]=[           
                's' => $serial++,
                'n' => $employees[$ed['sdRef']]['eName'],
                'm' => @$employees[$ed['sdRef']]['eMobile'],
                'c' => $general->numberFormat($ed['sdCollect']),
                'd' => $designations[$edID]['edTitle'],  
                'st' => '<a href="?mdl=dueStatment&eID='.$ed['sdRef'].'" class="btn btn-outline-warning">Statment</a>'
            ];
        }


    }
    $rData[]=[
        's'=>'',
        'n'=>['t'=>'Total','b'=>1,'col'=>2],
        'm'=>['t'=>false],
        'c'=>['t'=>$general->numberFormat($totalCollect),'b'=>1],
        'd'=>['t'=>''],
        'pr'=>['t'=>''],
        'prP'=>['t'=>''],
    ];
    
    $fileName='purRep_'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'empDueReport',
        'title'     => 'Employees Due Report',
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"#"                 ,'key'=>'s'         ,'w'=>5),
            array('title'=>"Employees Name"    ,'key'=>'n'         ,'w'=>10),          
            array('title'=>"Number"            ,'key'=>'m'        ,'hw'=>'r'),
            array('title'=>"Amount"            ,'key'=>'c'        ,'al'=>'r'),
            array('title'=>"Designations"      ,'key'=>'d'         ,'al'=>'r'),
            array('title'=>"Statment"              ,'key'=>'st'),
        ),
        'data'=>$rData,
    );
    $jArray[__LINE__]=$report_data;
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;   
?>
