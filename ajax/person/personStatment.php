<?php
    $id=intval($_POST['id']);
    $dRange=$_POST['dRange'];
    $general->getFromToFromString($dRange,$from,$to);
    $rData=[];
    $person=$db->get_rowData('person','id',$id);
    if(!empty($person)){
        $hID=$acc->getPersonHead($person);
        //$head=$db->selectAll($general->table(97),'WHERE hID='.$hID);


        if($hID>0){
            $closingBalance=$acc->headBalance($hID,$from);
        }
        else{
            $closingBalance=0;
        }
        $od=0;
        $oc=0;
        $balance=0;
        if($closingBalance<0){
            $oc=-$closingBalance;
        }
        else{
            $od=$closingBalance;
        }
        $balance+=$od;
        $balance-=$oc;
        $serial=1;
        $rData[]=[
            's'=>$serial++,
            'n'=>'',
            'm'=>'',
            'h'=>'',
            'd'=>'',
            'p'=>['t'=>'Opening','b'=>1],
            'u'=>'',
            'in'=>'',
            'out'=>'',
            'b'=>$general->numberFormat($balance),
        ];
        $tIn=0;
        $tOut=0;
        if(!empty($person)){
            $statement=$acc->headStatement(0,$hID,$from,$to,$jArray);
            if(!empty($statement)){
                $users=$db->allUsers();
                foreach($statement as $s){
                    $tIn+=$s['in'];
                    $tOut+=$s['out'];

                    $balance+=$s['in'];
                    $balance-=$s['out'];
                    $veIDs[$s['voucher_id']]=$s['voucher_id'];
                    $rData[]=[
                        's'=>$serial++,
                        'n'=>$person['name'],
                        //'h'=>$s['hTitle'],//.' '.$s['veID'],
                        'h'=>$s['head_title'].' '.$s['time'],
                        'd'=>$general->make_date($s['time'],'time'),
                        'p'=>$s['note'],
                        'u'=>@$users[$s['createdBy']]['username'],
                        'in'=>$general->numberFormat($s['in']),
                        'out'=>$general->numberFormat($s['out']),
                        'b'=>$general->numberFormat($balance),
                    ];
                }
            }
            $rData[]=[
                's'=>'',
                'n'=>['t'=>'Total','b'=>1,'col'=>5],
                'h'=>['t'=>false],
                'd'=>['t'=>false],
                'p'=>['t'=>false],
                'u'=>['t'=>false],
                'in'=>['t'=>$general->numberFormat($tIn),'b'=>1],
                'out'=>['t'=>$general->numberFormat($tOut),'b'=>1],
                'b'=>['t'=>$general->numberFormat($balance),'b'=>1],
            ];

        }
        $fileName='customerStat'.TIME.rand(0,999).'.txt';
        $report_data=array(
            'name'      => 'customerStat'.date('d/m/Y'),
            'title'     => 'customer Statment',
            //'info'      => $reportInfo,
            'fileName'  => $fileName,
            'head'=>array(
                array('title'=>"#"              ,'key'=>'s'     ,'hw'=>5),
                array('title'=>"person Name"    ,'key'=>'n'),
                array('title'=>"Head"           ,'key'=>'h'     ,'hw'=>15),
                array('title'=>"Date Time"      ,'key'=>'d'),
                array('title'=>"Particular"     ,'key'=>'p'),
                array('title'=>"User"           ,'key'=>'u'),
                array('title'=>"Pay"           ,'key'=>'in'    ,'al'=>'r'),
                array('title'=>"Receive"         ,'key'=>'out'     ,'al'=>'r'),
                array('title'=>"Balance"        ,'key'=>'b'    ,'al'=>'r'),

            ),
            'data'=>$rData
        );
        $jArray[__LINE__]=$report_data;
        $gAr['report_data']= $report_data;
        textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
        $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
        $jArray['status']=1;   
        $general->jsonHeader($jArray);
    }
?>
