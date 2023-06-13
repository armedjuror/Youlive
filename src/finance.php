<?php
require 'imports.php';
session_check($db_connection);
require 'head.php';
require 'navigation.php';
$is_admin = false;
$finance_reports = [];

if ($_SESSION['type']=='admin'){
    $is_admin = true;
    $records = $db_connection->query("SELECT u.id as user_id, f.id as id, f.description, f.amount, u.name as counterparty, f.counterparty as actual_counterparty, f.method, u2.name as created_by, f.created_at FROM finance f LEFT JOIN users u on u.id=f.counterparty LEFT JOIN users u2 ON u2.id=f.created_by ")->fetch_all(MYSQLI_ASSOC);
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
            <h1 class="h6 mb-0 text-gray-800">FINANCE</h1>
            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-<?=THEME?> shadow-sm" onclick="triggerFinanceRecordForm(this, 'add', '', '', '', '', '<?=$_SESSION['id']?>', '<?=$_SESSION['channel_id']?>')"><i
                    class="fa fa-plus fa-sm text-white-50"></i> Add Record</a>
        </div>

        <div class="row">

            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Income</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">₹<span id="income">000,000</span></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-arrow-down fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Earnings (Annual) Card Example -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Expense</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">₹<span id="expense">000,000</span></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-arrow-up fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tasks Card Example -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Balance</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><span id="balance">000,000</span></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-rupee-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="dataTable" class="table table-hover">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Created By</th>
                            <th>Counterparty</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Created At</th>
                            <th>Operation</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $index=1;
                        $income = 0;
                        $expense = 0;
                        $balance = 0;
                        foreach ($records as $record){
                            $amount = 0;
                            if ($record['amount']>=0){
                                $class = 'text-success';
                                $amount = $record['amount'];
                                $income += $record['amount'];
                            }else{
                                $class = 'text-danger';
                                $amount = ((float)$record['amount'])*-1;
                                $expense += $amount;
                            }

                            $counter_party = $record['counterparty']?:$record['actual_counterparty'];
                            echo    '   <tr id="'.$record['id'].'-row">
                                            <td id="'.$record['id'].'-index">'.$index.'</td>
                                            <td id="'.$record['id'].'-created_by">'.$record['created_by'].'</td>
                                            <td id="'.$record['id'].'-counterparty">'.$counter_party.'</td>
                                            <td id="'.$record['id'].'-description">'.$record['description'].'</td>
                                            <td class="'.$class.'" id="'.$record['id'].'-amount">'.$amount.'</td>
                                            <td id="'.$record['id'].'-method">'.$record['method'].'</td>
                                            <td id="'.$record['id'].'-created_at">'.$record['created_at'].'</td>
                                            <td id="'.$record['id'].'-operation">
                                                   <i class="fa fa-edit" title="Edit Event"  onclick="triggerFinanceRecordForm(this, \'edit\', \''. $record['id'] .'\', \'' . addslashes($record['description']) . '\', \'' . addslashes($record['amount']) . '\', \'' . addslashes($record['method']) . '\')"></i>
                                                   <i class="fa fa-trash" title="Delete Stream"  onclick="triggerFinanceRecordForm(this, \'delete\', \''. $record['id'] .'\')"></i>
                                            </td>
                               </tr>';
                            $index++;
                        }
                        $balance = $income - $expense;
                        echo '<script>
                                document.getElementById("income").innerText = '.$income.';
                                document.getElementById("expense").innerText = '.$expense.';
                                document.getElementById("balance").innerText = '.$balance.';
                             </script>';
                        ?>
                        </tbody>
                    </table>
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
</body>

</html>