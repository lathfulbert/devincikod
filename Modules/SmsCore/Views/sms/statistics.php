@extends('backend.layouts.master')

@section('title', 'SMS Statistics')

@section('css')
<link rel="stylesheet" type="text/css" href="<?= asset('assets/css/vendors/animate.css') ?>">
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Statistiques SMS</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dashboard') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">SMS</li>
                    <li class="breadcrumb-item active">Statistiques</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>

    <!-- Date Range Filter -->
    <div class="row mb-3">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="<?= url('/admin/sms/statistics') ?>" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Date Début</label>
                            <input type="date" class="form-control" name="from" value="<?= $from ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date Fin</label>
                            <input type="date" class="form-control" name="to" value="<?= $to ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="search"></i> Filtrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row">
        <div class="col-xxl-3 col-sm-6 box-col-6">
            <div class="card o-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-light-primary rounded p-3">
                                <i data-feather="send" class="text-primary" style="width: 32px; height: 32px;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Total Envoyés</h6>
                            <h3 class="mb-0 text-primary"><?= number_format($totalSent) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-sm-6 box-col-6">
            <div class="card o-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-light-success rounded p-3">
                                <i data-feather="check-circle" class="text-success" style="width: 32px; height: 32px;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Livrés</h6>
                            <h3 class="mb-0 text-success"><?= number_format($totalDelivered) ?></h3>
                            <?php if ($totalSent > 0): ?>
                                <small class="text-muted"><?= round(($totalDelivered / $totalSent) * 100, 1) ?>% taux de livraison</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-sm-6 box-col-6">
            <div class="card o-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-light-danger rounded p-3">
                                <i data-feather="x-circle" class="text-danger" style="width: 32px; height: 32px;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">Échoués</h6>
                            <h3 class="mb-0 text-danger"><?= number_format($totalFailed) ?></h3>
                            <?php if ($totalSent > 0): ?>
                                <small class="text-muted"><?= round(($totalFailed / $totalSent) * 100, 1) ?>% taux d'échec</small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3 col-sm-6 box-col-6">
            <div class="card o-hidden">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-light-warning rounded p-3">
                                <i data-feather="clock" class="text-warning" style="width: 32px; height: 32px;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 text-muted">En attente</h6>
                            <h3 class="mb-0 text-warning"><?= number_format($totalPending) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="header-top d-flex justify-content-between align-items-center">
                        <h5>Évolution des Envois SMS</h5>
                        <span class="text-muted">Du <?= date('d/m/Y', strtotime($from)) ?> au <?= date('d/m/Y', strtotime($to)) ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <div id="sms-chart"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- ApexCharts -->
<script src="<?= asset('assets/js/chart/apex-chart/apex-chart.js') ?>"></script>

<script>
if (typeof feather !== 'undefined') {
    feather.replace();
}

// SMS Statistics Chart with ApexCharts
var options = {
    series: [{
        name: 'Envoyés',
        data: <?= $chartSent ?>
    }, {
        name: 'Livrés',
        data: <?= $chartDelivered ?>
    }, {
        name: 'Échoués',
        data: <?= $chartFailed ?>
    }],
    chart: {
        type: 'area',
        height: 350,
        toolbar: {
            show: true,
            offsetY: -20
        },
        offsetY: 10
    },
    dataLabels: {
        enabled: false
    },
    stroke: {
        curve: 'smooth',
        width: 2
    },
    xaxis: {
        categories: <?= $chartLabels ?>,
        title: {
            text: 'Date'
        }
    },
    yaxis: {
        title: {
            text: 'Nombre de SMS'
        }
    },
    colors: ['#7366ff', '#54ba4a', '#ff5370'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.7,
            opacityTo: 0.3,
            stops: [0, 90, 100]
        }
    },
    tooltip: {
        shared: true,
        intersect: false,
        y: {
            formatter: function (val) {
                return val + " SMS"
            }
        }
    },
    legend: {
        position: 'bottom',
        horizontalAlign: 'center',
        offsetY: 5
    }
};

var chart = new ApexCharts(document.querySelector("#sms-chart"), options);
chart.render();
</script>
@endsection
