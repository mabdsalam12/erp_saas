<?php
$general->pageHeader($rModule['name']);
?>
<?php
if(isset($_GET['year']) && isset($_GET['month'])&& isset($_GET['department'])){
    $year= intval($_GET['year']);
    $month= intval($_GET['month']);
    $department_id= intval($_GET['department']);
    $monthName = date('F', mktime(0, 0, 0, $month, 10));
    $allowance=$db->selectAll('salary_allowance','where isActive=1 order by title asc');
    $employees = $db->selectAll('employees', 'where department_id='.$department_id.' and isActive=1');
    $department=$db->get_rowData('employee_department','id',$department_id);
    $firstDayTimestamp = strtotime("$year-$month-01");
    $general->arrayIndexChange($employees, 'id');

    $employee_salary=$db->selectAll('employee_salary','where employee_id in('.implode(',',array_keys($employees)).') and salary_date='.$firstDayTimestamp);
    $general->arrayIndexChange($employee_salary, 'id');


    $employeeData=[];
    if(!empty($employee_salary)){
        $employee_allowance=$db->selectAll('employee_salary_allowance','where salary_id in('.implode(',',array_keys($employee_salary)).')');
        $allowanceData=[];

        foreach($employee_allowance as $ea){
            $allowanceData[$ea['salary_id']][$ea['salary_allowance_id']]=$ea;
        }

        foreach($employee_salary as $es){
            $employeeData[$es['employee_id']]=[
                'salary'=>$es['salary'],
                'generate_date' =>$es['generate_date'],
                'total' =>$es['total'],
                'allowance' =>[],
            ];

            if(isset($allowanceData[$es['id']])){
                $employeeData[$es['employee_id']]['allowance']=$allowanceData[$es['id']];
            }
        }
    }

    
    ?>
    <div class="white-box border-box">
        <div class="row"><div class="col-lg-12"><?php show_msg();?></div></div>
        <div class="row">
            <div class="col-sm-12 col-lg-12">
                <div class="col-sm-12 col-lg-12 text-center mt-4">
                    <h2>Employee Salary</h2>
                    <h3><?php echo "$monthName $year"; ?></h3>
                    <h3><?php echo $department['title']; ?></h3>
                    <input type="hidden" id="salary_year" value="<?=$year?>">
                    <input type="hidden" id="salary_month" value="<?=$month?>">
                    <input type="hidden" id="department_id" value="<?=$department_id?>">
                </div>
                <div class="col-sm-12 col-lg-12 mt-4">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th>Generate date</th>
                                <th>Basic Salary</th>
                                <?php
                                foreach($allowance as $a){
                                    ?>
                                    <th><?php echo $a['title']; ?></th>
                                    <?php
                                }
                                ?>
                                <th>Net Salary</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_column=count($allowance)+3;
                            foreach ($employees as $employee) {
                                $salary=[];
                                if(isset($employeeData[$employee['id']])){
                                    $salary=$employeeData[$employee['id']];
                                }
                                $salary_date=$firstDayTimestamp;
                                if(!empty($salary)){
                                    $salary_date=$salary['generate_date'];
                                }

                                ?>
                                <tr id="em_<?=$employee['id']?>" class="employee_row">
                                    <td>
                                        <input type="hidden" class="employee_id" value="<?=$employee['id']?>">
                                        <?php echo $employee['name']; ?>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control generate_date daterangepicker_e" value="<?php echo date('d-m-Y',$salary_date)?>">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control amount_td salary" value="<?php if(!empty($salary['salary'])){echo (float)$salary['salary'];}else{echo (float)$employee['salary'];}?>">

                                    </td>
                                    <?php
                                    $sa=[];
                                    if(isset($salary['allowance'])){
                                        $sa=$salary['allowance'];
                                    }
                                    foreach ($allowance as $a) {
                                        $amount=0;
                                        if(isset($sa[$a['id']])){
                                            $amount=$sa[$a['id']]['amount'];
                                        }
                                        ?>
                                        <td>
                                            <input type="hidden" class="employee_id" value="<?=$a['id']?>">
                                            <input type="text" class="form-control amount_td allowance" value="<?=(float)$amount?>">
                                        </td>
                                        <?php
                                    }
                                    ?>
                                    <td class="employee_total"><?php if(isset($salary['total'])){echo number_format($salary['total'],2);}?></td>
                                </tr>
                                <?php
                            }
                            ?>

                        </tbody>
                    </table>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" onclick="save_employee_salary()" class="btn btn-success">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
elseif(isset($_GET['year']) && isset($_GET['month'])){
    $year= intval($_GET['year']);
    $month= intval($_GET['month']);
    $monthName = date('F', mktime(0, 0, 0, $month, 10));
    //$allowance=$db->selectAll('salary_allowance','where isActive=1');
    $allowance=$db->selectAll('salary_allowance','where isActive=1 order by title asc');
    $employees = $db->selectAll('employees', 'where isActive=1');
    $departments=$db->selectAll('employee_department','where isActive=1');
    $general->arrayIndexChange($departments, 'id');
    $department_employee=[];
    $employee_ids=[];
    foreach ($employees as $employee) {
        if($employee['department_id']==0){
            continue;
        }
        $employee_ids[]=$employee['id'];
        if(!isset($department_employee[$employee['department_id']])){
            $department_employee[$employee['department_id']]=[];
        }
        $department_employee[$employee['department_id']][]=$employee['id'];
    }
    $general->arrayIndexChange($employees, 'id');
    $month_start_date=strtotime("$year-$month-01");
    $employee_salary = $db->selectAll('employee_salary', 'where employee_id in('.implode(',',$employee_ids).')' . ' and salary_date=' . $month_start_date);
    $general->arrayIndexChange($employee_salary, 'id');
    $employeeData=[];
    if(!empty($employee_salary)){
        $employee_allowance=$db->selectAll('employee_salary_allowance','where salary_id in('.implode(',',array_keys($employee_salary)).')');
        $allowanceData=[];

        foreach($employee_allowance as $ea){
            $allowanceData[$ea['salary_id']][$ea['salary_allowance_id']]=$ea;
        }

        foreach($employee_salary as $es){
            $employeeData[$es['employee_id']]=[
                'salary'=>$es['salary'],
                'total' =>$es['total'],
                'allowance' =>[],
            ];

            if(isset($allowanceData[$es['id']])){
                $employeeData[$es['employee_id']]['allowance']=$allowanceData[$es['id']];
            }
        }
    }
    
    ?>
    <div class="white-box border-box">
        <div class="row"><div class="col-lg-12"><?php show_msg();?></div></div>
        <div class="row">
            <div class="col-sm-12 col-lg-12">
                <div class="col-sm-12 col-lg-12 text-center mt-4">
                    <h2>Employee Salary</h2>
                    <h3><?php echo "$monthName $year"; ?></h3>
                </div>
                <div class="col-sm-12 col-lg-12 mt-4">

                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th class="amount_td">Basic Salary</th>
                                <?php
                                foreach($allowance as $a){
                                    ?>
                                    <th class="amount_td"><?php echo $a['title']; ?></th>
                                    <?php
                                }
                                ?>
                                <th>Net Salary</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_column=count($allowance)+3;
                            $total_salary=0;
                            $total_allowance=[];
                            $total_amount=0;
                            foreach($department_employee as $department_id=>$employee_ids){
                                $department=$departments[$department_id];
                                ?>
                                <tr>
                                <td colspan="<?php echo $total_column; ?>" class="text-center bg-light">
                                    <h4><?php echo $department['title']; ?></h4>
                                    <a class="btn btn-success" href="<?=$pUrl?>&year=<?=$year?>&month=<?=$month?>&department=<?=$department_id?>">Update</a>
                                </td>
                                <?php
                                $department_salary_total=0;
                                $department_allowance_total=[];
                                $department_total=0;
                                foreach ($employee_ids as $employee_id) {
                                    $employee=$employees[$employee_id];
                                    $salary=[];
                                    $salary_amount=0;
                                    if(isset($employeeData[$employee_id])){
                                        $salary=$employeeData[$employee_id];
                                    }
                                    if(!empty($salary)){
                                        $salary_amount=$salary['salary'];
                                    }
                                    $total_salary+=$salary_amount;
                                    $department_salary_total+=$salary_amount;
                                    $total=$salary_amount;
                                    ?>
                                    <tr>
                                        <td><?php echo $employee['name']; ?></td>
                                        <td class="amount_td"><?=$general->numberFormat($salary_amount)?></td>
                                        <?php
                                        $sa=[];
                                        if(isset($salary['allowance'])){
                                            $sa=$salary['allowance'];
                                        }
                                        foreach ($allowance as $a) {
                                            $amount=0;
                                            if(isset($sa[$a['id']])){
                                                $amount=$sa[$a['id']]['amount'];
                                            }
                                            if(!isset($department_allowance_total[$a['id']])){
                                                $department_allowance_total[$a['id']]=0;
                                            }
                                            if(!isset($total_allowance[$a['id']])){
                                                $total_allowance[$a['id']]=0;
                                            }
                                            $total+= $amount;
                                            $department_allowance_total[$a['id']]+=$amount;
                                            $total_allowance[$a['id']]+=$amount;
                                            ?>
                                            <td class="amount_td"><?=$general->numberFormat($amount)?></td>
                                            <?php
                                        }
                                        $department_total+=$total;
                                        ?>
                                        <td class="amount_td"><?php echo $general->numberFormat($total)?></td>
                                    </tr>
                                    <?php
                                }
                                $total_amount+=$department_total;
                                ?>
                                <tr>
                                <td class="text-right">Total</td>
                                <td class="amount_td"><b><?php echo $general->numberFormat($department_salary_total); ?></b></td>
                                <?php
                                foreach($allowance as $a){
                                    ?>
                                    <td class="amount_td"><b><?php echo $general->numberFormat($department_allowance_total[$a['id']]); ?></b></td>
                                    <?php
                                }
                                ?>
                                <td class="amount_td"><b><?php echo $general->numberFormat($department_total)?></b></td>
                            </tr>
                                <?php
                            }
                            ?>
                            <tr>
                                <td class="text-right">Total</td>
                                <td class="amount_td"><b><?php echo $general->numberFormat($total_salary); ?></b></td>
                                <?php
                                foreach($allowance as $a){
                                    ?>
                                    <td class="amount_td"><b><?php echo $general->numberFormat($total_allowance[$a['id']]); ?></b></td>
                                    <?php
                                }
                                ?>
                                <td class="amount_td"><b><?php echo $general->numberFormat($total_amount); ?></b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php
}
else if(isset($_GET['year'])){
    $year= intval($_GET['year']);
    ?>
    <div class="row"><div class="col-lg-12"><?php show_msg();?></div></div>
    <div class="row">

        <div class="col-sm-12 col-lg-12">
            <?php
            $months = [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
            ];
            ?>
            <div class="col-sm-12 col-lg-12 text-center mt-4">
                <!-- Month Buttons -->
                <?php foreach ($months as $number => $name): ?>
                    <a class="btn btn-success btn-lg m-2 px-4 py-3" href="<?=$pUrl?>&year=<?=$year?>&month=<?=$number?>">
                        <?php echo $name; ?>
                    </a>
                    <?php endforeach; ?>
            </div>

        </div>
    </div>
    <?php
}
else{
    ?>
    <div class="row"><div class="col-lg-12"><?php show_msg();?></div></div>
    <div class="row">

        <div class="col-sm-12 col-lg-12">
            <?php
            $currentYear = date('Y');
            $years = [$currentYear - 1, $currentYear, $currentYear + 1];
            ?>

            <div class="col-sm-12 col-lg-12">
                <?php foreach ($years as $year): ?>
                    <a class="btn btn-primary btn-lg m-2 px-5 py-3" href="<?=$pUrl?>&year=<?=$year?>"><?php echo $year; ?></a>
                    <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php
}
?>
<script type="text/javascript">
    $(document).ready(function () {
        $(".salary, .allowance").on("input", function () {
            let row = $(this).closest("tr"); // Get the current row
            let salary = parseFloat(row.find(".salary").val()) || 0; // Get salary value
            let totalAllowance = 0;

            // Loop through all allowances in the row
            row.find(".allowance").each(function () {
                totalAllowance += parseFloat($(this).val()) || 0;
            });

            let total = salary + totalAllowance; // Calculate total

            row.find(".employee_total").text(total.toFixed(2)); // Display total with 2 decimal places
        });
    });

    function save_employee_salary(){
        let salary_year=$('#salary_year').val();
        let salary_month=$('#salary_month').val();
        let department_id=$('#department_id').val();
        let employeesData = [];

        $(".employee_row").each(function () {
            let row = $(this);
            let employee = {
                id: row.find(".employee_id").val(),
                salary: row.find(".salary").val(),
                generate_date: row.find(".generate_date").val(),
                allowances: [],
            };

            // Collect allowances along with their IDs
            row.find("td").each(function () {
                let allowanceId = $(this).find(".employee_id").val();
                let allowanceAmount = $(this).find(".allowance").val();

                if (allowanceId !== undefined && allowanceAmount !== undefined) {
                    employee.allowances.push({
                        id: allowanceId,
                        amount: allowanceAmount
                    });
                }
            });

            employeesData.push(employee);
        });

        console.log(employeesData); // Debugging: Check the collected data in the console

        $.post(ajUrl,{save_employee_salary:1,employeesData:employeesData,salary_year:salary_year,salary_month:salary_month,department_id:department_id},function(data){
             swMessageFromJs(data.m);
        });


    }

</script>

