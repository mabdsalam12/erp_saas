<?php
    $base_id = intval($_POST['base_id']);
    $base = $db->selectAll('base');
    $general->arrayIndexChange($base,'id');

    $sq='';
    if($base_id>0){
        $sq='where base_id='.$base_id;
    }
    $bazars = $db->selectAll('bazar',$sq);
    $rData=[];
    $total=1;
    if(!empty($bazars)){
        foreach($bazars as $a){
            $rData[]=[
                's'=>$total++,
                'base'=>$base[$a['base_id']]['title']??'',
                'title'=>$a['title'],
                'edit' => '<a href="'.URL.'?mdl=bazar&edit='.$a['id'].'" class="btn btn-info">Edit</a>',
            ];
        }
    }
    $fileName='bazar_list_'.TIME.rand(0,999).'.txt';
    $report_data=array(
        'name'      => 'bazar_list',
        'title'     => 'bazar list',
        'fileName'  => $fileName,
        'head'=>array(
            array('title'=>"#"            ,'key'=>'s','hw'=>5),
            array('title'=>"Base"         ,'key'=>'base'),
            array('title'=>"Title"         ,'key'=>'title'),
            array('title'=>"Edit"         ,'key'=>'edit'),
        ),
        'data'=>$rData
    );
    $gAr['report_data']= $report_data;
    textFileWrite(json_encode($report_data),ROOT_DIR.'/print_file/'.$fileName);
    $jArray['html']     = $general->fileToVariable(ROOT_DIR.'/ajax/report.phtml');
    $jArray['status']=1;