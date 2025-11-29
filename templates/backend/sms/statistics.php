@extends('backend.layouts.master')

@section('title', 'SMS Statistics')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Statistiques SMS</h3>
            </div>
            <div class="col-6">
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

    <!-- Statistics Chart -->
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <h5>Évolution des Envois SMS</h5>
                    <span>Du <?= date('d/m/Y', strtotime($from)) ?> au <?= date('d/m/Y', strtotime($to)) ?></span>
                </div>
                <div class="card-body">
                    <canvas id="smsChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i data-feather="send" class="text-primary" style="width: 48px; height: 48px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Total Envoyés</h6>
                            <h3 class="mb-0"><?= array_sum($chartData['sent']) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i data-feather="check-circle" class="text-success" style="width: 48px; height: 48px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Livrés</h6>
                            <h3 class="mb-0"><?= array_sum($chartData['delivered']) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i data-feather="x-circle" class="text-danger" style="width: 48px; height: 48px;"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">Échecs</h6>
                            <h3 class="mb-0"><?= array_sum($chartData['failed']) ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Initialize Feather Icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }

    // Chart Data from PHP
    const chartData = <?= json_encode($chartData) ?>;

    // Create Chart
    const ctx = document.getElementById('smsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                    label: 'Envoyés',
                    data: chartData.sent,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4
                },
                {
                    label: 'Livrés',
                    data: chartData.delivered,
                    borderColor: 'rgb(54, 162, 235)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    tension: 0.4
                },
                {
                    label: 'Échecs',
                    data: chartData.failed,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endsection