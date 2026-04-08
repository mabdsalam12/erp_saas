<?php
class MY_PDF extends TCPDF {
    public $left_margin=10;
    public $font_name='helvetica';
    private General $general;
    public $data=[];
    private $total_amount=0;
    public $w1=30,$w2=30,$w3=30,$w4=15,$w5=50,$w6=10,$w7=20,$w8=25;
    public function pdf_init($data,General $general){
        $this->general=$general;
        $this->SetCreator('Abdus salam');
        $this->SetAuthor('Abdus salam');
        $this->SetTitle(SITE_NAME);
        $this->SetSubject(SITE_NAME);
        $this->SetKeywords('Abdus salam '.SITE_NAME);
        $this->SetMargins(1,1, PDF_MARGIN_RIGHT);
        $this->SetAutoPageBreak(TRUE,1);
        $this->Ln(10);
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $this->SetFont($this->font_name, '', 9);
        $this->AddPage();
        $this->data=$data;
    }
    public function sale_header() {
        $pgw=$this->getPageWidth();
        //$this->left_margin=20;
        //$this->MultiCell($pgw,20,'',0,'',0,'',0,0);
        $title='Invoice';
        //$this->SetTextColor(255,255,255);
        $this->SetFont($this->font_name,'B',25);
        $this->MultiCell($pgw,20,$title,0,'L',0,0,$this->left_margin,5);
        //$this->Image(ROOT_DIR.'/image/logo.jpg',$pgw-35,5,15);
        $this->Image(ROOT_DIR.'/image/logo.png',$pgw-40,3,25);
        $x=$this->left_margin;
        $y=25;
        $h=70;
        $w1=74;
        $w2=35;
        $w3=$pgw-$w1-$w2-($this->left_margin*2);
        $box_y=$y;
        $line_h=5;
        $c=$this->data['customer'];
        $customer_data=$this->general->getJsonFromString($c['data']);
        $base=$this->data['base'];
        //$mpo_data=$this->general->getJsonFromString($this->data['mpo']['data']);
        $base_title=$base['title'];
        $base_area=$base['area'];
        $base_district=$base['district'];
        $mpo=$this->data['mpo'];
        
        $district='';
        $police_station='';
        if(isset($customer_data['district'])){
            $district=$customer_data['district'];
        }
        if(isset($customer_data['police_station'])){
            $police_station=$customer_data['police_station'];
        }
        $s=$this->data['sale'];
        $this->SetFont($this->font_name,'B',10);
        $this->MultiCell($w1,$h,'Customer ID :'.$c['code'],1,'L',false,0,$x,$box_y);
        $y+=$line_h;
        $customer_string='Customer Name :'.$c['name'];
        $height=$this->getStringHeight($w1,$customer_string);
        $this->SetFont($this->font_name,'',10);
        $this->MultiCell($w1,$height,$customer_string,0,'L',false,0,$x,$y);
        $y+=$height;
        $this->MultiCell(0,$line_h,'Owner name : '.$c['owner_name'],0,'L',false,0,$x,$y);
        $y+=$line_h;
        $this->MultiCell(0,$line_h,'Address: '.$c['address'],0,'L',false,0,$x,$y);
        $y+=$line_h;
        $this->MultiCell(0,$line_h,'Thana- '.$police_station,0,'L',false,0,$x,$y);
        $y+=$line_h;
        $this->MultiCell(0,$line_h,'District- '.$district,0,'L',false,0,$x,$y);
        $y+=$line_h;
        $this->MultiCell(0,$line_h,'Phone Number: '.$c['mobile'],0,'L',false,0,$x,$y);
        $y+=$line_h+$line_h;
        $this->SetFont($this->font_name,'B',12);
        $this->MultiCell(0,$line_h,'Bazar: '.$c['bazar'],0,'L',false,0,$x,$y);
        $x+=$w1;
        $this->MultiCell($w2,$h,'Invoice No',1,'L',false,0,$x,$box_y);
        $y=$box_y;
        $y+=$line_h;
        $this->MultiCell(0,$line_h,$s['invoice_no'],0,'L',false,0,$x,$y);
        $y+=$line_h+3;
        $this->SetFont($this->font_name,'',10);
        $this->MultiCell(0,$line_h,'Invoice Date',0,'L',false,0,$x,$y);
        $y+=$line_h;
        $this->MultiCell(0,$line_h,date('d/m/y',$s['date']),0,'L',false,0,$x,$y);
        $y+=$line_h+3;
        $this->MultiCell(0,$line_h,'Due Date',0,'L',false,0,$x,$y);
        $y+=$line_h;
        $this->MultiCell(0,$line_h,date('d/m/y',$s['collection_date']),0,'L',false,0,$x,$y);
        
        $y+=$line_h+3;
        $this->MultiCell(0,$line_h,'Order No',0,'L',false,0,$x,$y);
        $y+=$line_h;
        $this->MultiCell(0,$line_h,$s['order_no'],0,'L',false,0,$x,$y);
        $y+=$line_h+3;
        $this->MultiCell(0,$line_h,'Order Date',0,'L',false,0,$x,$y);
        $y+=$line_h;
        $this->MultiCell(0,$line_h,date('d/m/y',$s['order_date']),0,'L',false,0,$x,$y);
        
        $x+=$w2;
        $this->SetFont($this->font_name,'B',18);
        $this->MultiCell($w3,$h,'',1,'L',false,0,$x,$box_y);
        $y=$box_y;
        $w3+=40;
        $this->MultiCell($w3,$line_h,'Panacea Agrovet Ltd',0,'L',false,0,$x,$y);
        $y+=$line_h+3;
        $this->SetFont($this->font_name,'B',13);
        $this->MultiCell($w3,$line_h,'Mfg License No. DLS-166',0,'L',false,0,$x,$y);
        $this->SetFont($this->font_name,'',11);
        $y+=$line_h;
        $this->MultiCell($w3,$line_h,'Dag No. 1310, Purbatiartola(Moheshpur Dhal)',0,'L',false,0,$x,$y);
        $y+=$line_h;
        $this->MultiCell($w3,$line_h,'Chatmohar Railbazar, Chatmohar, Pabna.',0,'L',false,0,$x,$y);
        $y+=$line_h;
        $this->MultiCell($w3,$line_h,'Contact No(Office).  01313444272',0,'L',false,0,$x,$y);
        $y+=$line_h+3;
        $this->SetFont($this->font_name,'B',14);
        $base_string="Base No. $base_title";
        $height=$this->getStringHeight(90,$base_string);




        $this->MultiCell($w3,$height,$base_string,0,'L',false,0,$x,$y);
        $this->SetFont($this->font_name,'',11);
        $y+=$height+3;
        $this->MultiCell($w3,$line_h,'MPO Name: '.$mpo['name'],0,'L',false,0,$x,$y);
        $y+=$line_h;
        $this->MultiCell($w3,$line_h,"Area-$base_area",0,'L',false,0,$x,$y);
        $y+=$line_h;
        $this->MultiCell($w3,$line_h,"District-$base_district",0,'L',false,0,$x,$y);
        $y+=$line_h;
        $this->MultiCell($w3,$line_h,"Contact No (MPO) $mpo[mobile]",0,'L',false,0,$x,$y);
        $y=$box_y+$h;
        return $y;
    }
    private function createTableBodyCell($w,$h,$text,$x,$y,$align='L'){
        $this->MultiCell($w,$h,$text,1,$align,false,0,$x,$y);
        return $x+=$w;
    }
    public function sale_product($y,$with_tp=1) {
        $y+=10;
        $pgw=$this->getPageWidth();
        $pgw-=$this->left_margin*2;
        $x=$this->left_margin;
        $h=6;$w1=12;$w2=15;$w4=20;$w5=15;$w6=20;
        $w7=20;$w8=20;$w9=28;
        $table_width=$w1+$w2+$w4+$w5+$w6+$w7+$w8+$w9;
        if($this->data['challan']==0){
            $table_width=$w1+$w2+$w4+$w5+$w6+$w7+$w8+$w9;
            if($with_tp==1){
                $w3=$pgw-$table_width;
            }
            else{
                $w3=$w4+$w7+$pgw-$table_width;
            }
        }
        else{
            $table_width=$w1+$w2+$w5;
            $w3=$pgw-$table_width;
        }
        $this->SetFont($this->font_name,'B',10);

        $x=$this->createTableBodyCell($w1,$h,'SN',$x,$y);
        $x=$this->createTableBodyCell($w2,$h,'ID',$x,$y);
        $x=$this->createTableBodyCell($w3,$h,'Product Name ',$x,$y);
        if($with_tp==1&&$this->data['challan']==0){
            $x=$this->createTableBodyCell($w4,$h,'TP',$x,$y);
        }
        $x=$this->createTableBodyCell($w5,$h,'Qty',$x,$y);
        if($this->data['challan']==0){
            $x=$this->createTableBodyCell($w6,$h,'Bonus',$x,$y);
            if($with_tp==1){
                $x=$this->createTableBodyCell($w7,$h,'Total',$x,$y);
                $x=$this->createTableBodyCell($w8,$h,'Discount',$x,$y);
            }
            else{
                $x=$this->createTableBodyCell($w7,$h,'SP',$x,$y);
            }
            $x=$this->createTableBodyCell($w9,$h,'Total Value',$x,$y);
        }
        $y+=$h;
        $serial=1;
        foreach($this->data['details'] as $d){
            $p=$d['product'];
            $x=$this->left_margin;
            $this->SetFont($this->font_name,'',8);
            $x=$this->createTableBodyCell($w1,$h,$serial++, $x, $y, 'L');
            $x=$this->createTableBodyCell($w2,$h,$p['code'], $x, $y, 'L');
            $x=$this->createTableBodyCell($w3,$h,$p['title'], $x, $y, 'L');
            
            if($with_tp==1&&$this->data['challan']==0){
                $x=$this->createTableBodyCell($w4,$h,(float)$d['unit_price'], $x, $y, 'L');
            }
            $x=$this->createTableBodyCell($w5,$h,(float)$d['sale_qty'], $x, $y, 'R');
            if($this->data['challan']==0){
                $x=$this->createTableBodyCell($w6,$h,(float)$d['free_qty'], $x, $y, 'R');
                if($with_tp==1){
                    $x=$this->createTableBodyCell($w7,$h,(float)$d['sub_total'], $x, $y, 'R');
                    $x=$this->createTableBodyCell($w8,$h,(float)$d['discount'], $x, $y, 'R');
                }
                else{
                    $x=$this->createTableBodyCell($w7,$h,(float)round(($d['total']/$d['sale_qty']),2), $x, $y, 'R');
                }
                $x=$this->createTableBodyCell($w9,$h,(float)$d['total'], $x, $y, 'R');
            }
            $y+=$h;
        }
        if($this->data['challan']==0){
            $this->SetFont($this->font_name,'B',11);
            $h=6;
            $s=$this->data['sale'];
            $footer_data=[];
            if($with_tp==1){
                $footer_data[]=[
                    'title'=>'Total value',
                    'amount'=> $s['sub_total']
                ];
                $footer_data[]=[
                    'title'=>'Discount',
                    'amount'=> $s['discount']+$s['extra_discount']
                ];
                $footer_data[]=[
                    'title'=>'VAT',
                    'amount'=> $s['VAT']
                ];
                $footer_data[]=[
                    'title'=>'Net Value',
                    'amount'=> $s['total']
                ];
            }
            else{
                $footer_data[]=[
                    'title'=>'Net Value',
                    'amount'=> $s['total']
                ];
            }
            foreach($footer_data as $f){
                $x=150;
                $w=25;
                $this->MultiCell($w,$h,$f['title'],0,'R',false,0,$x,$y);
                $x+=$w;
                $this->MultiCell($w,$h,(float)$f['amount'],0,'R',false,0,$x,$y);
                $y+=$h;
            }
            // $y+=$h;
        }
        return $y;
    }
    public function sale_footer($y){
        if($this->data['challan']==0){
            $x=$this->left_margin;
            $sale_data=$this->general->getJsonFromString($this->data['sale']['data']);
            $old_due=0;
            if(isset($sale_data['customer_closing_balance'])){
                $old_due=floatval($sale_data['customer_closing_balance']);
            }
            $new_due=$old_due+$this->data['sale']['total'];

            $amount_in_word=strtolower($this->general->convertNumberToWords($this->data['sale']['total']));
            $this->MultiCell(0,0,'Taka in words :'.ucfirst($amount_in_word).'taka only.',0,'L',false,0,$x,$y);
            $y+=10;
            $x=$this->left_margin;
            $this->SetFont($this->font_name,'',12);
            $type=$this->general->pay_type_title_by_id($this->data['sale']['pay_type']);



            $footer_data=[];
            
            $footer_data[]=[
                'title'=>'Sales Term',
                'amount'=> $type
            ];
            $footer_data[]=[
                'title'=>'Current Receivable',
                'amount'=> (float)$this->data['sale']['total']
            ];
            $footer_data[]=[
                'title'=>'Previous Receivable',
                'amount'=> (float)$old_due
            ];
            $footer_data[]=[
                'title'=>'Total Receivable',
                'amount'=> (float)$new_due
            ];
            $h=6;
            foreach($footer_data as $f){
                $y+=$h;
                $x=$this->left_margin;
                $w=45;
                $this->MultiCell($w,$h,$f['title'],0,'L',false,0,$x,$y);
                $x+=$w;
                $w=4;
                $this->MultiCell($w,$h,':',0,'L',false,0,$x,$y);
                $x+=$w;
                $w=20;
                $this->MultiCell($w,$h,$f['amount'],0,'R',false,0,$x,$y);
            }
            $y=$this->sale_due_invoice_list($y);

        }


        $y=$this->getPageHeight()-20;
        $x=20;
        $this->MultiCell(120,0,'Prepared by',0,'L',false,0,$x,$y);
        $x+=60;
        $this->MultiCell(0,0,'MPO Acknowledgement',0,'L',false,0,$x,$y);
        $x+=73;
        $this->MultiCell(90,0,'Received by',0,'L',false,0,$x,$y);
        $x=10;
        $y+=6;
        $this->MultiCell(0,0,'(Distribution Sign & Date)',0,'L',false,0,$x,$y);
        $x+=75;
        $this->MultiCell(0,0,'(MPO Sign & Date)',0,'L',false,0,$x,$y);
        $x+=60;
        $this->MultiCell(90,0,'(customer sign & date)',0,'L',false,0,$x,$y);
        $y+=10;
        return $y;
    }

    public function sale_due_invoice_list($y){
        
        $general=$this->general;
        
        $y+=10;
        $pgw=$this->getPageWidth();
        $x=$this->left_margin;
        $h=6;
        $w1=12;$w2=30;$w4=40;$w5=40;$w6=30;
        $table_width=$w1+$w2+$w4+$w5+$w6;
        $w3=$pgw-$table_width-($this->left_margin*2);

        $this->MultiCell(0,$h,'Please pay within '.$general->make_date($this->data['sale']['collection_date']),0,'L',false,0,$x,$y);
        $y+=$h+$h;

        $due_data=[];
        $data=$general->getJsonFromString($this->data['sale']['data']);
        if(isset($data['customer_due_invoice'])){
            $due_data=$data['customer_due_invoice'];
        }
        //$this->general->printArray($due_data);exit;
        if(!empty($due_data)){
            $this->MultiCell(0,$h,'Previous Dues History',0,'L',false,0,$x,$y);
            $y+=$h+$h;
            $this->SetFont($this->font_name,'B',10);
                        $this->MultiCell($w1,$h,'SN',1,'L',false,0,$x,$y);
            $x+=$w1;    $this->MultiCell($w2,$h,'',1,'L',false,0,$x,$y);
                        $this->MultiCell($w2+$w2+$w2,$h,'Invoice Date',0,'L',false,0,$x,$y);
            $x+=$w2;    $this->MultiCell($w3,$h,'Invoice No',1,'L',false,0,$x,$y);
            $x+=$w3;    $this->MultiCell($w4,$h,'Sales Terms',1,'L',false,0,$x,$y);
            $x+=$w4;    $this->MultiCell($w5,$h,'Dues',1,'L',false,0,$x,$y);
            $x+=$w5;    $this->MultiCell($w6,$h,'',1,'L',false,0,$x,$y);
                        $this->MultiCell($w6*2,$h,'Aging (Days)',0,'L',false,0,$x,$y);
            $y+=$h;
            $serial=1;
            foreach($due_data as $d){
                $x=$this->left_margin;
                $this->SetFont($this->font_name,'',8);
                $this->MultiCell($w1,$h,$serial++,1,'L',false,0,$x,$y);
                
                $x+=$w1;    $this->MultiCell($w2,$h,date('d-m-y',$d['date']),1,'C',false,0,$x,$y);
                $x+=$w2;    $this->MultiCell($w3,$h,$d['invoice_no'],1,'C',false,0,$x,$y);
                $x+=$w3;    $this->MultiCell($w4,$h,$d['pay_type'],1,'C',false,0,$x,$y);
                $x+=$w4;    $this->MultiCell($w5,$h,$general->numberFormat($d['due']),1,'C',false,0,$x,$y);
                $x+=$w5;    $this->MultiCell($w6,$h,$d['aging'],1,'C',false,0,$x,$y);
                $y+=$h;
            }
        }
        return $y;
    }
    
    public function gift_dist_header() {
        $pgw=$this->getPageWidth();
        $title='Gift distribute';
        
        $this->SetFont($this->font_name,'B',25);
        $this->MultiCell($pgw,20,$title,0,'L',0,'',$this->left_margin,5);
        //$this->Image(ROOT_DIR.'/image/logo.jpg',$pgw-35,5,15);
        $this->Image(ROOT_DIR.'/image/logo.png',$pgw-40,3,25);
        $x=$this->left_margin;
        $y=25;
        $h=70;
        $w1=65;
        $w2=35;
        $w3=$pgw-$w1-$w2-($this->left_margin*2);
        $box_y=$y;
        $line_h=5;
        $base=$this->data['base'];
        $base_title=$base['title'];
        $base_area=$base['area'];
        $base_district=$base['district'];
        $user=$this->data['user'];
        
        $district='';
        $police_station='';
        
        $g=$this->data['g'];
        $this->SetFont($this->font_name,'B',10);
        $this->MultiCell($w1,$h,'',1,'L',false,0,$x,$box_y);
        $y+=$line_h;
        $customer_string='';
        $height=$this->getStringHeight(90,$customer_string);
            $this->SetFont($this->font_name,'',10);
            $this->MultiCell(90,$height,$customer_string,0,'L',false,0,$x,$y);
            $y+=$height;
            $this->MultiCell(0,$line_h,'',0,'L',false,0,$x,$y);
            $y+=$line_h;
            $this->MultiCell(0,$line_h,'',0,'L',false,0,$x,$y);
            $y+=$line_h;
            $this->MultiCell(0,$line_h,'Thana- '.$police_station,0,'L',false,0,$x,$y);
            $y+=$line_h;
            $this->MultiCell(0,$line_h,'District- '.$district,0,'L',false,0,$x,$y);
            $y+=$line_h;
            $this->MultiCell(0,$line_h,'',0,'L',false,0,$x,$y);
            $y+=$line_h+$line_h;
            $this->SetFont($this->font_name,'B',12);
            $this->MultiCell(0,$line_h,'',0,'L',false,0,$x,$y);
        $x+=$w1;
        $this->MultiCell($w2,$h,'Invoice No',1,'L',false,0,$x,$box_y);
            $y=$box_y;
            $y+=$line_h;
            $this->MultiCell(0,$line_h,$g['id'],0,'L',false,0,$x,$y);
            $y+=$line_h*2;
            $this->SetFont($this->font_name,'',10);
            $this->MultiCell(0,$line_h,'Date',0,'L',false,0,$x,$y);
            $y+=$line_h;
            $this->MultiCell(0,$line_h,date('d/m/y',$g['date']),0,'L',false,0,$x,$y);
            $y+=$line_h*2;
            
            
        $x+=$w2;
        $this->SetFont($this->font_name,'B',18);
        $this->MultiCell($w3,$h,'',1,'L',false,0,$x,$box_y);
            $y=$box_y;
            $w3+=40;
            $this->MultiCell($w3,$line_h,'Panacea Agrovet Ltd',0,'L',false,0,$x,$y);
            $y+=$line_h+3;
            $this->SetFont($this->font_name,'B',13);
            $this->MultiCell($w3,$line_h,'Mfg License No. DLS-166',0,'L',false,0,$x,$y);
            $this->SetFont($this->font_name,'',11);
            $y+=$line_h;
            $this->MultiCell($w3,$line_h,'Dag No. 1310, Purbatiartola(Moheshpur Dhal)',0,'L',false,0,$x,$y);
            $y+=$line_h;
            $this->MultiCell($w3,$line_h,'Chatmohar Railbazar, Chatmohar, Pabna.',0,'L',false,0,$x,$y);
            $y+=$line_h;
            $this->MultiCell($w3,$line_h,'Contact No(Office).  01313444272',0,'L',false,0,$x,$y);
            $y+=$line_h+3;
            $this->SetFont($this->font_name,'B',14);
            $base_string="Base No. $base_title";
            $height=$this->getStringHeight(90,$base_string);




            $this->MultiCell($w3,$height,$base_string,0,'L',false,0,$x,$y);
            $this->SetFont($this->font_name,'',11);
            $y+=$height+3;
            $this->MultiCell($w3,$line_h,'MPO Name: '.$user['name'],0,'L',false,0,$x,$y);
            $y+=$line_h;
            $this->MultiCell($w3,$line_h,"Area-$base_area",0,'L',false,0,$x,$y);
            $y+=$line_h;
            $this->MultiCell($w3,$line_h,"District-$base_district",0,'L',false,0,$x,$y);
            $y+=$line_h;
            $this->MultiCell($w3,$line_h,"Contact No (MPO) $user[mobile]",0,'L',false,0,$x,$y);
        $y=$box_y+$h;
            return $y;
    }
    
    public function gift_dist_product($y,$with_tp=1) {
        $y+=10;
        $pgw=$this->getPageWidth();
        $x=$this->left_margin;
        $h=6;
        $w1=12;$w2=10;$w4=20;$w5=20;$w6=20;
        $w7=20;$w8=22;$w9=28;
        $table_width=$w1+$w2+$w5;
        if($with_tp==1){
            $table_width+=$w6+$w4;
            $w3=$pgw-$table_width-($this->left_margin*2);    
        }
        else{
            $w3=$pgw-$table_width-($this->left_margin*2);
        }
        $this->SetFont($this->font_name,'B',10);
        $this->MultiCell($w1,$h,'SN',1,'L',false,0,$x,$y);

        $x+=$w1; $this->MultiCell($w2,$h,'ID',1,'L',false,0,$x,$y);

        $x+=$w2; $this->MultiCell($w3,$h,'Product Name ',1,'L',false,0,$x,$y);
        $x+=$w3;
        if($with_tp==1){
            $this->MultiCell($w4,$h,'TP',1,'L',false,0,$x,$y);
            $x+=$w4; 
        }
        $this->MultiCell($w5,$h,'Qty',1,'L',false,0,$x,$y);        
        $x+=$w5; 
        if($with_tp==1){
            $this->MultiCell($w6,$h,'Total',1,'L',false,0,$x,$y);        
            $x+=$w6; 
        }
        $y+=$h;
        $serial=1;
        foreach($this->data['details'] as $d){
            $amount=$d['tp']*$d['quantity'];
            $this->total_amount+=$amount;
            $b=1;
            $p=$d['product'];
            $x=$this->left_margin;
            $this->SetFont($this->font_name,'',8);
            $this->MultiCell($w1,$h,$serial++,1,'L',false,0,$x,$y);

            $x+=$w1; $this->MultiCell($w2,$h,$p['code'],1,'L',false,0,$x,$y);
            $x+=$w2; 
            $this->MultiCell($w3,$h,'',1,'L',false,0,$x,$y);
            $this->MultiCell($w3*2,$h,$p['title'],0,'L',false,0,$x,$y);
            
            $x+=$w3;
            if($with_tp==1){
                $this->MultiCell($w4,$h,'',1,'L',false,0,$x,$y);
                $this->MultiCell($w4,$h,(float)$d['tp'],0,'C',false,0,$x,$y);
                $x+=$w4;
            }
            $this->MultiCell($w5,$h,(float)$d['quantity'],1,'C',false,0,$x,$y);

            

            $x+=$w5;
            if($with_tp==1){
                $this->MultiCell($w6,$h,'',1,'C',false,0,$x,$y);
                $this->MultiCell($w6*2,$h,(float)$amount,0,'C',false,0,$x-($w6/2),$y);
                $x+=$w6;
            }
            $y+=$h;
        }
        $this->SetFont($this->font_name,'B',11);
        $h=6;
        
        $footer_data=[];
        if($with_tp==1){
            $footer_data[]=[
                'title'=>'Total value',
                'amount'=> $this->general->numberFormat($this->total_amount)
            ];
        }
        foreach($footer_data as $f){
            
            $x=$this->left_margin;
            $x+=$w1;
            $x+=$w2;
            $x+=$w3;
            if($with_tp==1){
                $this->MultiCell($w4*2,$h,$f['title'],0,'L',false,0,$x+10,$y+2);
                $x+=$w4*2;
                $this->MultiCell($w4*2,$h,$f['amount'],0,'C',false,0,$x-($w9/2),$y+2);
                $y+=$h;
            }
            else{
                $x-=$w4;
                $this->MultiCell($w8+$w6,$h,$f['title'],0,'L',false,0,$x+$w8,$y+2);
                $x+=$w6;
                $x+=$w8;
                $this->MultiCell($w8*2,$h,$f['amount'],0,'C',false,0,$x-($w8/2),$y+2);
                $y+=$h;
            }
        }
        $y+=$h;

        return $y;
    }
    
    public function gift_dist_footer($y){
        $pgw=$this->getPageWidth();
        
        $x=$this->left_margin;
        
        
        $amount_in_word=strtolower($this->general->convertNumberToWords($this->total_amount));
        $this->MultiCell(300,0,'Taka in words :'.ucfirst($amount_in_word).'taka only.',0,'L',false,0,$x,$y);
        $y+=10;
        $x=$this->left_margin;
        $this->SetFont($this->font_name,'',12);
        
        $y=$this->getPageHeight()-20;
        $x=20;
        $this->MultiCell(120,0,'Prepared by',0,'L',false,0,$x,$y);
        $x+=60;
        $this->MultiCell(0,0,'MPO Acknowledgement',0,'L',false,0,$x,$y);
        $x+=73;
        $this->MultiCell(90,0,'Received by',0,'L',false,0,$x,$y);
        $x=10;
        $y+=6;
        $this->MultiCell(0,0,'(Distribution Sign & Date)',0,'L',false,0,$x,$y);
        $x+=75;
        $this->MultiCell(0,0,'(MPO Sign & Date)',0,'L',false,0,$x,$y);
        $x+=60;
        $this->MultiCell(90,0,'(customer sign & date)',0,'L',false,0,$x,$y);
        $y+=10;
        return $y;
    }
}
