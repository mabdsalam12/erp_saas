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
        // set alpha to semi-transparency
        $this->SetAlpha(0.1);
        $this->Image(ROOT_DIR.'/images/'.PROJECT.'/logo_big.png', 40, 40, 130 );
        $this->SetAlpha(1);
        $this->data=$data;
    }
    public function sale_header() {
        $x=$this->left_margin;
        $this->Image(ROOT_DIR.'/images/'.PROJECT.'/print_header.png',$x,5,190);
        $y=35;
        $this->line(0,$y,$this->getPageWidth(),$y);
        $y+=5;
        $this->Image(ROOT_DIR.'/images/'.PROJECT.'/sale_invoice.png',$x,$y,190);        
        $y+=5;
        
        $h=70;
        $w1=100;
        
        $line_h=5;
        $c=$this->data['customer'];
        
        $base=$this->data['base'];
        //$mpo_data=$this->general->getJsonFromString($this->data['mpo']['data']);
        $base_title=$base['title'];
        $mpo=$this->data['mpo'];
        
        
        $user=$this->data['user'];
        $s=$this->data['sale'];
        $this->SetFont($this->font_name,'',10);
        $column_1_width=32;
        $column_2_width=95;
        $base_x=$x;
        $this->MultiCell($w1,$h,'Customer ID',0,'L',false,0,$x,$y);
        $x+=$column_1_width;
        $this->MultiCell($w1,$h,': ' .$c['code'],0,'L',false,0,$x,$y);

        $x+=$column_2_width;
        $this->MultiCell($w1,$h,'Invoice No',0,'L',false,0,$x,$y);
        $x+=$column_1_width;
        $this->MultiCell($w1,$h,': ' .$s['invoice_no'],0,'L',false,0,$x,$y);


        $y+=$line_h;
        $x=$base_x;
        $this->MultiCell($w1,$h,'Customer Name',0,'L',false,0,$x,$y);
        
        $x+=$column_1_width;
        $this->MultiCell($w1+60,$h,': ' .$c['name'],0,'L',false,0,$x,$y);

        $x+=$column_2_width;
        $this->MultiCell($w1,$h,'Invoice Date',0,'L',false,0,$x,$y);
        $x+=$column_1_width;
        $this->MultiCell($w1,$h,': ' .date('d/m/Y',$s['date']),0,'L',false,0,$x,$y);

        $address=$c['address'];
        $address_height=$line_h;
        $string_height=$this->getStringHeight($w1,$address);
        if($string_height>5){
            $address_height+=$line_h;
        }
        $y+=$line_h;
        $x=$base_x;
        $this->MultiCell($w1,$h,'Address',0,'L',false,0,$x,$y);
        
        $x+=$column_1_width;
        $this->MultiCell($w1,$h,': ' .$c['address'],0,'L',false,0,$x,$y);
        $x+=$column_2_width;
        $this->MultiCell($w1,$h,'Due Date',0,'L',false,0,$x,$y);
        $x+=$column_1_width;
        $this->MultiCell($w1,$h,': ' .date('d/m/y',$s['collection_date']),0,'L',false,0,$x,$y);
        $y+=$address_height;
        $x=$base_x;
        $this->MultiCell($w1,$h,'Mobile No',0,'L',false,0,$x,$y);
        $x+=$column_1_width;
        $this->MultiCell($w1,$h,': ' .$c['mobile'],0,'L',false,0,$x,$y);
        $x+=$column_2_width;

        
        $this->MultiCell($w1,$h,'Sold By',0,'L',false,0,$x,$y);
        $x+=$column_1_width;
        $this->MultiCell($w1,$h,": $mpo[name]",0,'L',false,0,$x,$y);
        $y+=$line_h;




        $x=$base_x;
        $this->MultiCell($w1,$h,'Base',0,'L',false,0,$x,$y);
        //$this->MultiCell(0,$h,'MPO Name & PH N',0,'L',false,0,$x,$y);
        $x+=$column_1_width;
        $this->MultiCell($w1,$h,": $base_title",0,'L',false,0,$x,$y);
        //$this->MultiCell(0,$h,": ".$mpo['name']." ".$mpo['mobile'],0,'L',false,0,$x,$y);
        $x+=$column_2_width;
        $type=$this->data['sale']['pay_type_name'];
        $this->MultiCell($w1,$h,'Sales Mode',0,'L',false,0,$x,$y);
        $x+=$column_1_width;
        $this->MultiCell($w1,$h,": $type",0,'L',false,0,$x,$y);
        $y+=$line_h;
        $x=$base_x;
        // $this->MultiCell($w1,$h,'Base',0,'L',false,0,$x,$y);
        $x+=$column_1_width;
        // $this->MultiCell($w1,$h,": $base_title",0,'L',false,0,$x,$y);
        $y+=$line_h;
        $x=$base_x;
        

        return $y;
    }
    private function createTableHeaderCell($w,$h,$text,$x,$y,$align='C'){
        $this->MultiCell($w,$h,$text,1,$align,true,0,$x,$y);
        return $x+=$w;
    }
    private function createTableBodyCell($w,$h,$text,$x,$y,$align='L'){
        $this->MultiCell($w,$h,$text,1,$align,false,0,$x,$y);
        return $x+=$w;
    }
    public function sale_product($y,$with_tp=1) {
        $y+=10;
        $pgw=$this->getPageWidth();
        $x=$this->left_margin;
        $h=6;$w1=8;$w3=15;$w4=15;$w5=20;$w6=16;
        $w7=20;$w8=22;$w9=25;
        $table_width=$w1+$w4+$w4+$w5+$w6+$w7+$w8+$w9;
        $margin=$this->left_margin*2;
        $w2=$pgw-($table_width+$margin);
    //    echo $pgw.' '.$table_width.' '.$margin.' '.$w3;exit; 
        $this->SetFont($this->font_name,'B',8);
        //$this->SetFillColor(249,249,249); // Grey
        // set color for background
        $this->SetFillColor(192, 192, 192);
        $this->setCellPadding(1);

        $x=$this->createTableHeaderCell($w1,$h,'SL',$x,$y);
        $x=$this->createTableHeaderCell($w2,$h,'PRODUCTS NAME',$x,$y);
        $x=$this->createTableHeaderCell($w3,$h,'UNIT ',$x,$y);
        $x=$this->createTableHeaderCell($w4,$h,'TP',$x,$y);
        $x=$this->createTableHeaderCell($w5,$h,'QUANTITY',$x,$y);
        $this->SetFont($this->font_name,'B',7.5);
        $x=$this->createTableHeaderCell($w6,$h,'FREE QUA',$x,$y);
        $x=$this->createTableHeaderCell($w7,$h,'TOTAL QUA',$x,$y);
        $x=$this->createTableHeaderCell($w8,$h,'TP VALUE',$x,$y);
        $this->SetFont($this->font_name,'B',7);
        $x=$this->createTableHeaderCell($w9,$h,'TOTAL VALUE',$x,$y);
        $y+=$h;
        $serial=1;
        
        $this->SetFont($this->font_name,'',12);
        $box_height=0;
        foreach($this->data['details'] as $d){
            if($y>230){
                $this->sale_footer($y);
                $this->AddPage();
                $y=20;
            }
            $box_height=$h;
            $box_height=6;
            $x=$this->left_margin;
            $this->SetFont($this->font_name,'',8);
            $p= $d['product'];

            $x=$this->createTableBodyCell($w1,$h,$serial++,$x,$y);
            $x=$this->createTableBodyCell($w2,$h,$p['title'],$x,$y);
            $x=$this->createTableBodyCell($w3,$h,$this->data['units'][$p['unit_id']]['title'],$x,$y);
            $x=$this->createTableBodyCell($w4,$h,(float)$d['unit_price'],$x,$y,'R');
            $x=$this->createTableBodyCell($w5,$h,(float)$d['sale_qty'],$x,$y,'R');
            $x=$this->createTableBodyCell($w6,$h,(float)$d['free_qty'],$x,$y,'R');
            $x=$this->createTableBodyCell($w7,$h,(float)$d['total_qty'],$x,$y,'R');
            $x=$this->createTableBodyCell($w8,$h,(float)$d['sub_total'],$x,$y,'R');
            $x=$this->createTableBodyCell($w9,$h,(float)$d['total'],$x,$y,'R');
            $x=$this->left_margin;
            $y+=$h;
        }
        $x=$this->left_margin;
        
        $x=$this->left_margin;
        $box_height=6;
        $this->SetFont($this->font_name,'B',8);
        $x=$this->createTableHeaderCell($w1+$w2+$w3+$w4+$w5+$w6+$w7,$box_height,'Total',$x,$y,'R');
        $x=$this->createTableHeaderCell($w8,$box_height,(float)$this->data['sale']['sub_total'],$x,$y,'R');
        $x=$this->createTableHeaderCell($w9,$box_height,(float)$this->data['sale']['total'],$x,$y,'R');

        $this->SetFont($this->font_name,'',10);
        $y+=$box_height;
        $x=$this->left_margin;
        $amount_in_word=strtolower(trim($this->general->convertNumberToWords($this->data['sale']['total'])));
        //var_dump($amount_in_word);exit;
        $this->MultiCell(150,0,'TAKA IN WORD: '.ucfirst($amount_in_word).' taka only.',0,'L',false,0,$x-1,$y);
        $h=4;
        $s=$this->data['sale'];
        $footer_data=[];
        $footer_data[]=[
            'title'=>'SUB TOTAL AMOUNT',
            'amount'=> $this->general->numberFormat($s['sub_total'])
        ];
        $footer_data[]=[
            'title'=>'DISCOUNT',
            'amount'=> $this->general->numberFormat($s['discount']+$s['extra_discount'])
        ];
        if($s['VAT']>0){
            $footer_data[]=[
                'title'=>'VAT',
                'amount'=> $this->general->numberFormat($s['VAT'])
            ];
        }
        
        $footer_data[]=[
            'title'=>'TOTAL PAYABLE AMOUNT',
            'amount'=> $this->general->numberFormat($s['total'])
        ];
        foreach($footer_data as $f){
            $x=$this->left_margin;
            $x+=110;
            $w=50;
            $this->MultiCell($w,$h,$f['title'],0,'L',false,0,$x,$y);
            $x+=$w;
            $w=5;
            $this->MultiCell($w,$h,':',0,'L',false,0,$x,$y);
            $x+=$w;
            $w=25;
            $this->MultiCell($w,$h,$f['amount'],0,'R',false,0,$x,$y);
            $y+=$h;
            
        }

        return $y;
    }
    public function sale_footer($y){
        $x=$this->left_margin;
        $this->SetFont($this->font_name,'',12);
        $h=6;
        $y+=6;
        $old_due=0;
        $sale_data=$this->general->getJsonFromString($this->data['sale']['data']);
        if(isset($sale_data['customer_closing_balance'])){
            $old_due=floatval($sale_data['customer_closing_balance']);
        }
        $new_due=$old_due+$this->data['sale']['total'];
        
        $footer_data=[];
        $footer_data[]=[
            'title'=>'Current receivable Amount',
            'amount'=> (float)$this->data['sale']['total']
        ];
        $footer_data[]=[
            'title'=>'Previous Receivable Amount',
            'amount'=> (float)$old_due
        ];
        $footer_data[]=[
            'title'=>'Total Receivable Amount',
            'amount'=> (float)$new_due
        ];

        foreach($footer_data as $f){
            $x=$this->left_margin;
            $w=57;
            $this->MultiCell($w,$h,$f['title'],0,'L',false,0,$x,$y);
            $x+=$w;
            $w=4;
            $this->MultiCell($w,$h,':',0,'L',false,0,$x,$y);
            $x+=$w;
            $w=20;
            $this->MultiCell($w,$h,$f['amount'],0,'R',false,0,$x,$y);
            $y+=$h;
        }

        $y=$this->getPageHeight()-40;
        $x=10;
        $this->MultiCell(53,0,'','T','L',false,0,$x,$y);
        $this->MultiCell(0,0,'RECEIVER`S SIGNATURE',0,'L',false,0,$x,$y);
        $x+=65;
        $this->MultiCell(41,0,'','T','L',false,0,$x,$y);
        $this->MultiCell(0,0,"MPO'S SIGNATURE",0,'L',false,0,$x,$y);
        $x+=60;
        $this->MultiCell(55,0,'','T','L',false,0,$x,$y);
        $this->MultiCell(90,0,'AUTHORIZED SIGNATURE',0,'L',false,0,$x,$y);
        $y+=12;
        $this->SetFont($this->font_name,'',6);
        $x+=35;
        $this->MultiCell(90,0,'Print at '.date('d/m/Y h:i A',TIME),0,'L',false,0,$x,$y);
        return $y;
    }
    public function gift_dist_header() {
        $x=$this->left_margin;
        $this->Image(ROOT_DIR.'/images/'.PROJECT.'/print_header.png',$x,5,190);        
        $y=35;
        $this->line(0,$y,$this->getPageWidth(),$y);
        $y+=5;
        $this->Image(ROOT_DIR.'/images/'.PROJECT.'/sample_invoice.png',$x,$y,190);        
        $y+=5;
        $w1=100;
        $column_1_width=35;
        $base_x=$x;
        $h=5;
        $user=$this->data['user'];
        $base=$this->data['base'];
        $base_title=$base['title'];
        $g=$this->data['g'];
        $this->MultiCell($w1,$h,'Invoice No',0,'L',false,0,$x,$y);
        $x+=$column_1_width;
        $this->MultiCell($w1,$h,': ' .$g['id'],0,'L',false,0,$x,$y);

        $x+=$column_1_width+$column_1_width;
        $this->MultiCell($w1,$h,'Invoice Date',0,'L',false,0,$x,$y);
        $x+=$column_1_width;
        $this->MultiCell($w1,$h,': ' .date('d/m/Y',$g['date']),0,'L',false,0,$x,$y);
        $y+=$h;
        $x=$base_x;
        $this->MultiCell($w1,$h,'MPO Name & Phone',0,'L',false,0,$x,$y);
        $x+=$column_1_width;
        $this->MultiCell($w1,$h,": ".$user['name']." ".$user['mobile'],0,'L',false,0,$x,$y);
        $y+=$h;
        $x=$base_x;
        $this->MultiCell($w1,$h,'Area',0,'L',false,0,$x,$y);
        $x+=$column_1_width;
        $this->MultiCell($w1,$h,": ".$base_title,0,'L',false,0,$x,$y);
        $y+=$h;
        
        return $y;
    }
    
    public function gift_dist_product($y,$with_tp=1) {
        $y+=10;
        $pgw=$this->getPageWidth();
        $x=$this->left_margin;
        $h=6;
        $w1=12;$w2=20;$w4=20;$w5=20;$w6=20;
        $w7=20;$w8=22;$w9=28;
        $table_width=$w1+$w2+$w5;
        if($with_tp==1){
            $table_width+=$w6+$w4;
            $w3=$pgw-$table_width-($this->left_margin*2);    
        }
        else{
            $w3=$pgw-$table_width-($this->left_margin*2);
        }
        $this->SetFillColor(192, 192, 192);
        $this->SetFont($this->font_name,'B',10);
        $this->MultiCell($w1,$h,'SN',1,'L',true,0,$x,$y);
        
        $x+=$w1; $this->MultiCell($w2,$h,'ID',1,'L',true,0,$x,$y);

        $x+=$w2; $this->MultiCell($w3,$h,'Product Name ',1,'L',true,0,$x,$y);
        $x+=$w3;
        if($with_tp==1){
            $this->MultiCell($w4,$h,'TP',1,'L',true,0,$x,$y);
            $x+=$w4; 
        }
        $this->MultiCell($w5,$h,'Qty',1,'L',true,0,$x,$y);        
        $x+=$w5; 
        if($with_tp==1){
            $this->MultiCell($w6,$h,'Total',1,'L',true,0,$x,$y);        
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
                $this->MultiCell($w4,$h,$d['tp'],0,'C',false,0,$x,$y);
                $x+=$w4;
            }
            $this->MultiCell($w5,$h,$d['quantity'],1,'C',false,0,$x,$y);

            

            $x+=$w5;
            if($with_tp==1){
                $this->MultiCell($w6,$h,'',1,'C',false,0,$x,$y);
                $this->MultiCell($w6*2,$h,$amount,0,'C',false,0,$x-($w6/2),$y);
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