<?php
require 'imports.php';
session_check($db_connection);
require 'head.php';
require 'navigation.php';
$is_admin = false;

$data = $db_connection->query("SELECT CONCAT(YEAR(created_at), ' ', MONTHNAME(created_at)) AS month, SUM(charge) AS charge, SUM(contribution) AS contribution FROM events GROUP BY YEAR(created_at), MONTH(created_at), month;")->fetch_all(MYSQLI_ASSOC);
$counts = $db_connection->query("SELECT payment_status, COUNT(*) AS count FROM events GROUP BY payment_status;")->fetch_all(MYSQLI_ASSOC);
$labels = [];
$charges = [];
$contributions = [];
foreach ($data as $monthly){
    $labels[] = $monthly['month'];
    $charges[] = $monthly['charge'];
    $contributions[] = $monthly['contribution'];
}

$event_counts = ['total'=>0, 'success'=>0, 'dispute'=>0, 'pending'=>0];
foreach ($counts as $count){
    $event_counts[$count['payment_status']] = $count['count'];
    $event_counts['total'] += $count['count'];
}

if ($_SESSION['type']=='admin'){
    $is_admin = true;
}
?>

    <!-- Begin Page Content -->
    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h6 mb-0 text-gray-800">DASHBOARD</h1>
            <?=$is_admin?'<a href="#" class="d-none d-sm-inline-block btn btn-sm btn-'.THEME.' shadow-sm" onclick="sync_streams()"><i
                    class="fa fa-sync fa-sm text-white-50"></i> Sync Streams</a>':''?>
            <?=$is_admin?'<a href="#" class="d-none d-sm-inline-block btn btn-sm btn-'.THEME.' shadow-sm" onclick="sync_events()"><i
                    class="fa fa-sync fa-sm text-white-50"></i> Sync Events</a>':''?>
        </div>


        <div class="row">

            <!-- Earnings (Monthly) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Events</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?=$event_counts['total']?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-video fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Earnings (Annual) Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Contributed Events</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?=$event_counts['success']?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tasks Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Contribution Pending</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?=$event_counts['pending']?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-hourglass fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Requests Card Example -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Contribution Disputed</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?=$event_counts['dispute']?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-exclamation-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">

            <div class="col-xl-12 col-lg-12">

                <!-- Area Chart -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-<?=THEME?>">Earning Vs Contribution</h6>
                    </div>
                    <div class="card-body">
                        <div class="chart-area">
                            <canvas id="myAreaChart"></canvas>
                        </div>
                    </div>
                </div>

            </div>

            </div>

    </div>
    <!-- /.container-fluid -->


    <div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalLabel">Add/Edit Event</h5>
                    <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="api.php/events" id="event-form" method="post" enctype="multipart/form-data">
                    </form>
                </div>
            </div>
        </div>
    </div>



    </div>
<?php require_once 'foot.php'; ?>
    <script src="ui/extras/chart.js/Chart.min.js"></script>
    <script>
        const labels = <?=json_encode($labels)?>;
        const charges = <?=json_encode($charges)?>;
        const contributions = <?=json_encode($contributions)?>;


        // Set new default font family and font color to mimic Bootstrap's default styling
        Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
        Chart.defaults.global.defaultFontColor = '#858796';

        function number_format(number, decimals, dec_point, thousands_sep) {
            // *     example: number_format(1234.56, 2, ',', ' ');
            // *     return: '1 234,56'
            number = (number + '').replace(',', '').replace(' ', '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function(n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }

        // Area Chart Example
        var ctx = document.getElementById("myAreaChart");
        var myLineChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: "Earnings",
                    lineTension: 0.3,
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointBorderColor: "rgba(78, 115, 223, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: charges,
                }, {
                    label: "Contributions",
                    lineTension: 0.3,
                    backgroundColor: "rgba(78,223,151,0.05)",
                    borderColor: "rgb(78,223,119)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(78,223,119, 1)",
                    pointBorderColor: "rgba(78, 223, 119, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(78, 223, 119, 1)",
                    pointHoverBorderColor: "rgba(78, 223, 119, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: contributions,
                }],
            },
            options: {
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        left: 10,
                        right: 25,
                        top: 25,
                        bottom: 0
                    }
                },
                scales: {
                    xAxes: [{
                        time: {
                            unit: 'date'
                        },
                        gridLines: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            maxTicksLimit: 7
                        }
                    }],
                    yAxes: [{
                        ticks: {
                            maxTicksLimit: 5,
                            padding: 10,
                            // Include a dollar sign in the ticks
                            callback: function(value, index, values) {
                                return '₹' + number_format(value);
                            }
                        },
                        gridLines: {
                            color: "rgb(234, 236, 244)",
                            zeroLineColor: "rgb(234, 236, 244)",
                            drawBorder: false,
                            borderDash: [2],
                            zeroLineBorderDash: [2]
                        }
                    }],
                },
                legend: {
                    display: false
                },
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    titleMarginBottom: 10,
                    titleFontColor: '#6e707e',
                    titleFontSize: 14,
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    intersect: false,
                    mode: 'index',
                    caretPadding: 10,
                    callbacks: {
                        label: function(tooltipItem, chart) {
                            var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                            return datasetLabel + ': ₹' + number_format(tooltipItem.yLabel);
                        }
                    }
                }
            }
        });



    </script>
</body>
</html>
