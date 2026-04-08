<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    $fileName=ROOT_DIR.'/print_file/'.$_POST['reportJsonToExcel'];
    if(file_exists($fileName)){
        $handle = fopen($fileName, 'r');    
        $a=fread($handle,filesize($fileName));
        $rq=json_decode($a,true);
        $dir=ROOT_DIR.'/print_file/';
        $lastTime=strtotime('-2 day');
        $dd=scandir($dir);
        foreach($dd as $d){
            if($d!='.'&&$d!='..'&&$d!='index.html'){
                $d=$dir.$d;
                $lastModified=filemtime($d);
                if($lastModified<$lastTime){unlink($d);}
            }
        }
    }
    else{
        echo $fileName;
        exit;
    }
    //echo __FILE__."\n<br>";
    
    $ex = new Spreadsheet();
    $ex->getProperties()->setCreator("Abdus Salam");
    $ex->getProperties()->setLastModifiedBy("Abdus Salam");
    $ex->getProperties()->setTitle("Abdus Salam");
    $ex->getProperties()->setSubject("Abdus Salam");
    $ex->getProperties()->setDescription("Final Report file");
    $sheet = $ex->getActiveSheet();
    $head=$rq['head'];
    $keys=[];
    $row=1;
    $i=1;
    //echo __LINE__."\n<br>";exit;
    foreach($head as $d){
        if(!isset($d['no_for_excel'])){
            $h=$d['title'];
            $keys[]=$d['key'];
            $h=str_ireplace('\n'," - ",$h);
            if(isset($d['w'])){
                $sheet->getColumnDimensionByColumn($i)->setAutoSize(false);
                $sheet->getColumnDimensionByColumn($i)->setWidth($d['w']);
            }
            if(isset($d['al'])&&0){
                if($d['al']=='l')$al='left';
                elseif($d['al']=='r')$al='right';
                else$al='center';
                //$h.=' '.$d['al'].' '.count($rq['data']);
                $sheet
                ->getStyle(PHPExcel_Cell::stringFromColumnIndex($i).'1:'.PHPExcel_Cell::stringFromColumnIndex($i).count($rq['data']))
                ->getAlignment($al)
                ->setHorizontal();
            }
            $sheet->setCellValue([$i,$row],$h);
            $i++;
        }
    }
    $row++;
    foreach($rq['data'] as $b){
        $i=1;
        foreach($keys as $k){
            $tt='-';
            if(isset($b[$k])){
                $tt=$b[$k];
            }
            if(!is_array($tt)){
                $text=$tt;
            }else{
                $text=$tt['t'];
            }
            if(isset($d['al'])&&0){
                if($d['al']=='l')$al=PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
                elseif($d['al']=='r')$al=PHPExcel_Style_Alignment::HORIZONTAL_RIGHT;
                else$al=PHPExcel_Style_Alignment::HORIZONTAL_CENTER;
                $sheet
                ->getStyle([$i,$row])
                ->getAlignment($al)
                ->setHorizontal();
            }
            $sheet->SetCellValue([$i,$row],$text);
            $i++;
        }
        $row++;     
    }
    if(isset($rq['name'])){
        $path=$rq['name'].'_'.TIME.'.xlsx';
    }
    else{
        $path='report_'.TIME.'.xlsx';
    }
    //echo ROOT_DIR.'/print_file/'.$path;exit;
    $writer = new Xlsx($ex);
    $writer->save(ROOT_DIR.'/print_file/'.$path);
    $jArray['status']=1;
    $jArray['link']=URL.'/print_file/'.$path;
    $general->jsonHeader($jArray);
