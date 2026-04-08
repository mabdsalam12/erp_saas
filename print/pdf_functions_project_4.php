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
        $this->SetAlpha(0.1);
        $this->Image(ROOT_DIR.'/images/'.PROJECT.'/logo_big.png', 40, 80, 130 );
        $this->SetAlpha(0.1);
        $this->SetAlpha(1);
        $this->data=$data;
    }
    public function sale_header() {
        $x=$this->left_margin;
        $this->Image(ROOT_DIR.'/images/'.PROJECT.'/logo_big.png',$x,5,30);
        $y=4;
        $x+=50;
        $this->SetFont($this->font_name,'B',40);
        
        $this->MultiCell(0,0,'RAJ BEKARY',0,'L',false,0,$x,$y);
        $x-=15;
        $y+=15;
        $this->SetFont($this->font_name,'I',10);
        $this->MultiCell(300,0,'Office Address :Kandail, Gurudashpur, Natore.Phone :01711-412267',0,'L',false,0,$x,$y);
        $y+=5;
        $x+=40;
        $this->MultiCell(300,0,'Quality is our pride',0,'L',false,0,$x,$y);
        $y+=5;
        $x=$this->left_margin;
        $this->line(0,$y,$this->getPageWidth(),$y);
        $y+=1;
        if($this->data['challan']==0){
            $image_path=ROOT_DIR.'/images/'.PROJECT.'/sale_invoice.png';
        }
        else{
            $image_path=ROOT_DIR.'/images/'.PROJECT.'/challan.png';
        }
        $this->Image($image_path,$x,$y,190);
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
        $this->MultiCell($w1,$h,'Owner name',0,'L',false,0,$x,$y);
        
        $x+=$column_1_width;
        $this->MultiCell($w1,$h,': ' .$c['owner_name'],0,'L',false,0,$x,$y);
        $x+=$column_2_width;
        $this->MultiCell($w1,$h,'Due Date',0,'L',false,0,$x,$y);
        $x+=$column_1_width;
        $this->MultiCell($w1,$h,': ' .date('d/m/y',$s['collection_date']),0,'L',false,0,$x,$y);
        $y+=$line_h;
        $x=$base_x;
        $this->MultiCell($w1,$h,'Address',0,'L',false,0,$x,$y);
        $x+=$column_1_width;
        $this->MultiCell($w1,$h,': ' .$c['address'],0,'L',false,0,$x,$y);
        $x+=$column_2_width;

        
        $this->MultiCell($w1,$h,'Sold By',0,'L',false,0,$x,$y);
        $x+=$column_1_width;
        $this->MultiCell($w1,$h,": $user[username]",0,'L',false,0,$x,$y);
        $y+=$address_height;




        $x=$base_x;
        $this->MultiCell(0,$h,'Mobile No',0,'L',false,0,$x,$y);
        $x+=$column_1_width;
        $this->MultiCell(0,$h,": ".$c['mobile'],0,'L',false,0,$x,$y);
        $x+=$column_2_width;
        $type=$this->data['sale']['pay_type']==PAY_TYPE_CASH?"Cash":"Credit";
        
        $this->MultiCell($w1,$h,'Sales Mode',0,'L',false,0,$x,$y);
        $x+=$column_1_width;
        $this->MultiCell($w1,$h,": $type",0,'L',false,0,$x,$y);
        $y+=$line_h;

        $x=$base_x;
        $this->MultiCell($w1,$h,'MPO Name & PH N',0,'L',false,0,$x,$y);
        $x+=$column_1_width;
        $this->MultiCell($w1,$h,": ".$mpo['name']." ".$mpo['mobile'],0,'L',false,0,$x,$y);

        $x+=$column_2_width;

        
        $this->MultiCell($w1,$h,'Base',0,'L',false,0,$x,$y);
        $x+=$column_1_width;
        $this->MultiCell($w1,$h,": $base_title",0,'L',false,0,$x,$y);
        $y+=$line_h;


        $x=$base_x;
        

        return $y;
    }
    public function sale_product($y,$with_tp=1) {
        $y+=3;
        $pgw=$this->getPageWidth();
        $x=$this->left_margin;
        $h=6;$w1=8;$w3=30;$w4=30;$w5=20;$w6=16;$w7=20;$w8=22;$w9=28;

        if($this->data['challan']==0){
            $table_width=$w1+$w4+$w4+$w5+$w8+$w9;
            $margin=$this->left_margin*2;
            $w2=$pgw-($table_width+$margin);
        }
        else{
            $table_width=$w1+$w3+$w5+$w9;
            $margin=$this->left_margin*2;
            $w2=$pgw-($table_width+$margin);
        }
        
        
    //    echo $pgw.' '.$table_width.' '.$margin.' '.$w3;exit; 
        $this->SetFont($this->font_name,'B',8);
        //$this->SetFillColor(249,249,249); // Grey
        // set color for background
        $this->SetFillColor(192, 192, 192);
        $this->MultiCell($w1,$h,'SL',1, 'L',true,0,$x,$y);
        $x+=$w1;

        $this->MultiCell($w2,$h,'PRODUCTS NAME',1,'L',true,0,$x,$y);
        $x+=$w2;
        if($this->data['challan']==0){
            $label='UNIT ';
        }
        else{
            $label='CARTOON/BOX';
        }
        $this->MultiCell($w3,$h,'',1, 'L',true,0,$x,$y);
        $this->MultiCell($w3+10,$h,$label,0,'L',false,0,$x,$y);
        $x+=$w3;

        if($this->data['challan']==0){
            $this->MultiCell($w4,$h,'',1,'L',true,0,$x,$y);
            $this->MultiCell($w4,$h,'TP',0,'L',false,0,$x,$y);
            $x+=$w4; 
        }
        
        $this->MultiCell($w5,$h,'',1,'L',false,0,$x,$y);
        $this->MultiCell($w5,$h,'QUANTITY',0,'L',true,0,$x,$y);

        $x+=$w5;
        // $this->MultiCell($w6,$h,'',1,'L',true,0,$x,$y);
        // $this->MultiCell($w6+20,$h,'FREE QUA',0,'L',false,0,$x,$y);
        // $x+=$w6; 
        
        // $this->MultiCell($w7,$h,'',1,'L',true,0,$x,$y);
        // $this->MultiCell($w7+20,$h,'TOTAL QUA',0,'L',false,0,$x,$y);
        // $x+=$w7; 
        if($this->data['challan']==0){
            $this->MultiCell($w8,$h,'',1,'L',true,0,$x,$y);
            $this->MultiCell($w8+20,$h,'TP VALUE',0,'L',false,0,$x,$y);
            $x+=$w8;

            $this->MultiCell($w9,$h,'',1,'L',true,0,$x,$y);
            $this->MultiCell($w9+10,$h,'TOTAL VALUE',0,'L',false,0,$x,$y);
        }
        else{
            $this->MultiCell($w9,$h,'',1,'L',true,0,$x,$y);
            $this->MultiCell($w9+10,$h,'REMARKS',0,'L',false,0,$x,$y);
        }
        $y+=$h;
        $box_before_y=$y;
        $serial=1;
        $x=$this->left_margin;
        
       // $y+=1;
        $this->SetFont($this->font_name,'',12);
        $box_height=0;
        $total_quantity=0;
        foreach($this->data['details'] as $d){
            $total_quantity+=$d['sale_qty'];
            if($this->data['challan']==0&&$y>230){
                $this->sale_footer($y);
                
                $this->AddPage();
                $y=20;
            }
            elseif($this->data['challan']==1&&$y>130){
                $this->AddPage();
                $y=20;
            }
            $box_height=$h;
            $box_height=6;
            $x=$this->left_margin;
            $this->MultiCell($w1,$box_height,'',1,'',false,0,$x,$y);
            $x+=$w1;
            $this->MultiCell($w2,$box_height,'',1,'L',false,0,$x,$y);
            $x+=$w2;
            $this->MultiCell($w3,$box_height,'',1,'L',false,0,$x,$y);
            $x+=$w3;
            if($this->data['challan']==0){
                $this->MultiCell($w4,$box_height,'',1,'L',false,0,$x,$y);
                $x+=$w4;
            }
            $this->MultiCell($w5,$box_height,'',1,'L',false,0,$x,$y);
            $x+=$w5;
            if($this->data['challan']==0){
                $this->MultiCell($w8,$box_height,'',1,'C',false,0,$x,$y);
                $x+=$w8;
            }
            $this->MultiCell($w9,$box_height,'',1,'L',false,0,$x,$y);
            $y+=1;
            $p= $d['product'];
            $x=$this->left_margin;
            $this->SetFont($this->font_name,'',8);
            
            $this->MultiCell($w1,$h,$serial++,0,'L',false,0,$x,$y);
            $x+=$w1;
            
            $this->MultiCell($w2*2,$h,$p['code'].' '.$p['title'],0,'L',false,0,$x,$y);
            $x+=$w2;

            
            $this->MultiCell($w4*2,$h,$this->data['units'][$p['unit_id']]['title'],0,'L',false,0,$x,$y);
            $x+=$w3;
            if($this->data['challan']==0){
                $this->MultiCell($w4,$h,(float)$d['unit_price'],0,'C',false,0,$x,$y);
                $x+=$w4;
            }
            $this->MultiCell($w5,$h,(float)$d['sale_qty'],0,'C',false,0,$x,$y);
            $x+=$w5;
            if($this->data['challan']==0){
                $this->MultiCell($w8*2,$h,(float)$d['sub_total'],0,'C',false,0,$x-($w7/2),$y);
                $x+=$w8;  
                $this->MultiCell($w9*2,$h,(float)$d['total'],0,'C',false,0,$x-($w9/2),$y);
            }
            else{
                $this->MultiCell($w9*2,$h,'',0,'C',false,0,$x-($w9/2),$y);
            }
            $y+=$h;
            $y-=1;
        }
        $x=$this->left_margin;
        
        $x=$this->left_margin;
        $box_height=6;
        $this->MultiCell($w1,$box_height,'',1,'',true,0,$x,$y);
        $x+=$w1;
        $this->MultiCell($w2,$box_height,'',1,'L',true,0,$x,$y);
        $x+=$w2;
        $this->MultiCell($w3,$box_height,'',1,'L',true,0,$x,$y);
        $x+=$w3;
        if($this->data['challan']==0){
            $this->MultiCell($w4,$box_height,'',1,'L',true,0,$x,$y);
            $x+=$w4;
        }
        $this->MultiCell($w5,$box_height,'',1,'L',true,0,$x,$y);
        $x+=$w5;
        // $this->MultiCell($w6,$box_height,'',1,'L',true,0,$x,$y);
        // $x+=$w6;
        // $this->MultiCell($w7,$box_height,'',1,'C',true,0,$x,$y);
        // $x+=$w7;
        if($this->data['challan']==0){
            $this->MultiCell($w8,$box_height,'',1,'C',true,0,$x,$y);
            $x+=$w8;
        }
        $this->MultiCell($w9,$box_height,'',1,'L',true,0,$x,$y);
        $x=$this->left_margin;
        $y+=1;
        $this->SetFont($this->font_name,'B',10);
        $this->MultiCell($w1,$h,'',0,'L',false,0,$x,$y);
        $x+=$w1;

        $this->MultiCell($w2*2,$h,'Total products ='.count($this->data['details']),0,'L',false,0,$x,$y);
        $x+=$w2;
        if($this->data['challan']==0){
            $this->MultiCell($w3*2,$h,'',0,'L',false,0,$x,$y);
        }
        else{
            $this->MultiCell($w3*2,$h,'Total quantity =',0,'L',false,0,$x,$y);
        }
        $x+=$w3;
        if($this->data['challan']==0){
            $this->MultiCell($w4,$h,'',0,'C',false,0,$x,$y);
            $x+=$w4;
        }
        if($this->data['challan']==0){
        $this->MultiCell($w5,$h,'',0,'C',false,0,$x,$y);
        }
        else{
            $this->MultiCell($w5,$h,(float)$total_quantity,0,'C',false,0,$x,$y);
        }
        $x+=$w5;
        // $this->MultiCell($w6,$h,'',0,'C',false,0,$x,$y);
        // $x+=$w6;
        // $this->MultiCell($w7,$h,'',0,'C',false,0,$x,$y);
        // $x+=$w7;
        if($this->data['challan']==0){
            $this->MultiCell($w8*2,$h,(float)$this->data['sale']['sub_total'],0,'C',false,0,$x-($w7/2),$y);
            $x+=$w8;
            $this->MultiCell($w9*2,$h,(float)$this->data['sale']['total'],0,'C',false,0,$x-($w9/2),$y);
        //$y+=$h;
        }
        $this->SetFont($this->font_name,'',10);
        $y+=$box_height;
        $x=$this->left_margin;
        if($this->data['challan']==0){
        $amount_in_word=strtolower(trim($this->general->convertNumberToWords($this->data['sale']['total'])));
        //var_dump($amount_in_word);exit;
        $this->MultiCell(150,0,'TAKA IN WORD: '.ucfirst($amount_in_word).' taka only.',0,'L',false,0,$x-1,$y);
        }

        //$this->SetFont($this->font_name,'B',11);
        $h=4;
        $s=$this->data['sale'];
        $footer_data=[];
        if($this->data['challan']==0){
            $footer_data[]=[
                'title'=>'SUB TOTAL AMOUNT           :',
                'amount'=> (float)$s['sub_total']
            ];
            $footer_data[]=[
                'title'=>'DISCOUNT                             :',
                'amount'=> $this->general->numberFormat($s['discount']+$s['extra_discount'])
            ];
            if($s['VAT']>0){
                $footer_data[]=[
                    'title'=>'VAT',
                    'amount'=> (float)$s['VAT']
                ];
            }
            
            $footer_data[]=[
                'title'=>'TOTAL PAYABLE AMOUNT  :',
                'amount'=> (float)$s['total']
            ];
        }
        foreach($footer_data as $f){
            $x=$this->left_margin;
            $x+=80;
            $this->MultiCell(200,$h,$f['title'],0,'L',false,0,$x+$w8,$y+2);
            $x+=$w6;
            $x+=20;
            $x+=$w7;
            $x+=$w8;
            $this->MultiCell(0,$h,(float)$f['amount'],0,'R',false,0,$x-($w9/2),$y+2);
            $y+=$h;
            
        }
        //$y+=$h;

        return $y;
    }
    public function sale_footer($y){
        $x=$this->left_margin;
        $this->SetFont($this->font_name,'',12);
        $h=6;
        $y+=6;
        if($this->data['challan']==0){
            $old_due=0;
            $sale_data=$this->general->getJsonFromString($this->data['sale']['data']);
            if(isset($sale_data['customer_closing_balance'])){
                $old_due=floatval($sale_data['customer_closing_balance']);
            }
            $new_due=$old_due+$this->data['sale']['total'];
            $this->MultiCell(0,$h,'Current receivable Amount     : '. (float)$this->data['sale']['total'],0,'L',false,0,$x,$y);
            $y+=$h;
            $this->MultiCell(0,$h,'Previous Receivable Amount  : '.(float)$old_due,0,'L',false,0,$x,$y);
            $y+=$h;
            $this->MultiCell(0,$h,'Total Receivable Amount        : '. (float)$new_due,0,'L',false,0,$x,$y);
        }
        $y=$this->getPageHeight()-20;
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
