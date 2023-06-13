<?php
require 'imports.php';
session_check($db_connection);
require 'head.php';
require 'navigation.php';
$is_admin = false;
$finance_reports = [];

if ($_SESSION['type']=='admin'){
    $is_admin = true;

    $reports = $db_connection->query("SELECT YEAR(created_at) AS year, MONTHNAME(created_at) AS month,
       SUM(CASE WHEN amount >= 0 THEN amount ELSE 0 END) AS income,
       SUM(CASE WHEN amount < 0 THEN amount ELSE 0 END) AS expense
FROM finance
GROUP BY YEAR(created_at), MONTH(created_at), month;")->fetch_all(MYSQLI_ASSOC);
    foreach ($reports as $report){
        if (!array_key_exists($report['year'], $finance_reports)){
            $finance_reports[$report['year']] = [];
        }

        $finance_reports[$report['year']][$report['month']] = ['expense'=>$report['expense'], 'income'=>$report['income']];
    }
}else{
    print_error(new Exception("Unauthorised Access"), 'Oops, you seems less privileged!', 'This page is only meant for admin users');
    echo '</div>';
    include 'foot.php';
    exit();
}
?>

<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h6 mb-0 text-gray-800">REPORTS</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-<?=THEME?>">Monthly Finance</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6">
                            <select id="year" class="form-control">
                                <option selected value="">---SELECT YEAR---</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <select id="month" class="form-control">
                                <option selected value="">---SELECT MONTH---</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-3">Income: </div>
                        <div class="col-md-3" id="income-report"></div>
                        <div class="col-md-3">Expense: </div>
                        <div class="col-md-3" id="expense-report"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->


<div class="modal fade" id="financeModal" tabindex="-1" role="dialog" aria-labelledby="financeModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="financeModalLabel">Add/Edit Record</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="api.php/finance" id="finance-form" method="post" enctype="multipart/form-data">
                </form>
            </div>
        </div>
    </div>
</div>



</div>
<?php require_once 'foot.php'; ?>
<script>
    const financial_report = <?=json_encode($finance_reports)?>;

    let options = '<option selected value="">---SELECT YEAR---</option>';
    let years = Object.keys(financial_report);
    for(i=0; i<years.length; i++){
        options += '<option value="' + years[i] + '">' + years[i] + '</option>';
    }
    document.getElementById('year').innerHTML = options

    $('#year').on('change', function (e){
        let year = document.getElementById('year').value
        let options = '<option selected value="">---SELECT MONTH---</option>';
        let months = Object.keys(financial_report[year]);
        for(i=0; i<months.length; i++){
            options += '<option value="' + months[i] + '">' + months[i] + '</option>';
        }
        document.getElementById('month').innerHTML = options
    });

    $('#month').on('change', function (e){
        let year = document.getElementById('year').value
        let month = document.getElementById('month').value
        document.getElementById('income-report').innerText = '₹' + financial_report[year][month]['income']
        document.getElementById('expense-report').innerText = '₹' + (parseFloat(financial_report[year][month]['expense']) * -1).toString()
    });

</script>
</body>

</html>