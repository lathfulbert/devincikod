@extends('backend.layouts.master')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-sm-6">
                <h3>Dashboard</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">
                        <i data-feather="home"></i>
                    </a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Container-fluid starts -->
<div class="container-fluid default-dashboard">
    <div class="row widget-grid">
        <!-- Welcome Card -->
        <div class="col-xxl-4 col-sm-6 box-col-6">
            <div class="card profile-box">
                <div class="card-body">
                    <div class="d-flex media-wrapper justify-content-between">
                        <div class="flex-grow-1">
                            <div class="greeting-user">
                                <h2 class="f-w-600">Bienvenue</h2>
                                <p>Voici un aperçu de votre système aujourd'hui</p>
                                <div class="whatsnew-btn">
                                    <a class="btn btn-outline-white" href="/admin/profile">Voir le profil</a>
                                </div>
                            </div>
                        </div>
                        <div>
                            <div class="clockbox">
                                <svg id="clock" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600">
                                    <g id="face">
                                        <circle class="circle" cx="300" cy="300" r="253.9"></circle>
                                        <path class="hour-marks" d="M300.5 94V61M506 300.5h32M300.5 506v33M94 300.5H60M411.3 107.8l7.9-13.8M493 190.2l13-7.4M492.1 411.4l16.5 9.5M411 492.3l8.9 15.3M189 492.3l-9.2 15.9M107.7 411L93 419.5M107.5 189.3l-17.1-9.9M188.1 108.2l-9-15.6"></path>
                                        <circle class="mid-circle" cx="300" cy="300" r="16.2"></circle>
                                    </g>
                                    <g id="hour">
                                        <path class="hour-hand" d="M300.5 298V142"></path>
                                        <circle class="sizing-box" cx="300" cy="300" r="253.9"></circle>
                                    </g>
                                    <g id="minute">
                                        <path class="minute-hand" d="M300.5 298V67"></path>
                                        <circle class="sizing-box" cx="300" cy="300" r="253.9"></circle>
                                    </g>
                                    <g id="second">
                                        <path class="second-hand" d="M300.5 350V55"></path>
                                        <circle class="sizing-box" cx="300" cy="300" r="253.9"></circle>
                                    </g>
                                </svg>
                            </div>
                            <div class="badge f-10 p-0" id="txt"></div>
                        </div>
                    </div>
                    <div class="cartoon">
                        <img class="img-fluid" src="{{ asset('assets/images/dashboard/cartoon.svg') }}" alt="vector women with laptop">
                    </div>
                </div>
            </div>
        </div>

        <!-- Widgets Utilisateurs -->
        <?php
        // Afficher les widgets du module Users
        echo widget('users.total', [
            'stat_card_class' => 'card widget-1'
        ]);
        ?>

        <?php
        echo widget('users.active', [
            'stat_card_class' => 'card widget-1'
        ]);
        ?>

        <!-- Widgets SMS -->
        <?php
        echo widget('sms.total', [
            'stat_card_class' => 'card widget-1'
        ]);
        ?>

        <?php
        echo widget('sms.sender_names', [
            'stat_card_class' => 'card widget-1'
        ]);
        ?>

        <!-- Widget Santé du Système -->
        <div class="col-xxl-4 col-lg-6 box-col-6">
            <?php echo widget('admin.system_health'); ?>
        </div>

        <!-- Widget Statut SMS (Liste) -->
        <div class="col-xxl-4 col-lg-6 box-col-6">
            <?php echo widget('sms.status'); ?>
        </div>

        <!-- Widget Activité Récente -->
        <div class="col-xxl-4 col-lg-6 box-col-6">
            <?php echo widget('admin.recent_activity'); ?>
        </div>

        <!-- Section: Tous les widgets par module -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Widgets par Module</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="widgetTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                                <i data-feather="users"></i> Utilisateurs
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="sms-tab" data-bs-toggle="tab" data-bs-target="#sms" type="button" role="tab">
                                <i data-feather="send"></i> SMS
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin" type="button" role="tab">
                                <i data-feather="settings"></i> Administration
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content" id="widgetTabsContent">
                        <div class="tab-pane fade show active" id="users" role="tabpanel">
                            <div class="mt-4">
                                <?php echo module_widgets('Users', ['columns' => 3]); ?>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="sms" role="tabpanel">
                            <div class="mt-4">
                                <?php echo module_widgets('SmsCore', ['columns' => 3]); ?>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="admin" role="tabpanel">
                            <div class="mt-4">
                                <?php echo module_widgets('Admin', ['columns' => 2]); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget API Test Section -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Test API Widgets</h5>
                    <p class="text-muted">Tester l'API REST des widgets</p>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <button class="btn btn-primary w-100 mb-2" onclick="testWidgetAPI()">
                                <i data-feather="download"></i> Charger tous les widgets
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-info w-100 mb-2" onclick="testSingleWidget()">
                                <i data-feather="layers"></i> Charger un widget unique
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-warning w-100 mb-2" onclick="testBatchWidgets()">
                                <i data-feather="package"></i> Charger en batch
                            </button>
                        </div>
                    </div>
                    <div class="mt-3">
                        <h6>Résultat de l'API :</h6>
                        <pre id="apiResult" class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">Les résultats de l'API s'afficheront ici...</pre>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<!-- Container-fluid Ends -->

@endsection

@section('scripts')
<script src="{{ asset('assets/js/clock.js') }}"></script>
<script>
// Test de l'API Widget
async function testWidgetAPI() {
    try {
        const response = await fetch('/api/widgets');
        const data = await response.json();
        document.getElementById('apiResult').textContent = JSON.stringify(data, null, 2);
    } catch (error) {
        document.getElementById('apiResult').textContent = 'Erreur: ' + error.message;
    }
}

async function testSingleWidget() {
    try {
        const response = await fetch('/api/widgets/users.total');
        const data = await response.json();
        document.getElementById('apiResult').textContent = JSON.stringify(data, null, 2);
    } catch (error) {
        document.getElementById('apiResult').textContent = 'Erreur: ' + error.message;
    }
}

async function testBatchWidgets() {
    try {
        const response = await fetch('/api/widgets/batch', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                widgets: ['users.total', 'users.active', 'sms.total']
            })
        });
        const data = await response.json();
        document.getElementById('apiResult').textContent = JSON.stringify(data, null, 2);
    } catch (error) {
        document.getElementById('apiResult').textContent = 'Erreur: ' + error.message;
    }
}

// Auto-refresh des widgets toutes les 5 minutes
setInterval(() => {
    console.log('Auto-refresh des widgets...');
    location.reload();
}, 300000); // 5 minutes
</script>
@endsection
