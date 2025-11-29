@extends('backend.layouts.master')

@section('title', 'Configuration Tarifaire SMS')

@section('content')
<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3>Tarification SMS</h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/admin/dashboard') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item">SMS</li>
                    <li class="breadcrumb-item active">Tarification</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h5>Grille Tarifaire (JSON)</h5>
                    <span>Configurez les prix par pays, opérateur et gateway.</span>
                </div>
                <div class="card-body">
                    <form action="<?= url('/admin/sms/pricing/update') ?>" method="POST">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Configuration JSON</label>
                            <textarea class="form-control code-editor" name="pricing_json" rows="20" style="font-family: monospace;"><?= $pricingGrid ?></textarea>
                            <small class="text-muted">
                                Format attendu :
                                <pre class="mt-2 bg-light p-2 rounded">
{
  "default": { "price": 15, "currency": "XOF" },
  "CI": {
    "default": 10,
    "networks": { "orange": 12, "mtn": 10 }
  }
}</pre>
                            </small>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="save"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
</script>
@endsection