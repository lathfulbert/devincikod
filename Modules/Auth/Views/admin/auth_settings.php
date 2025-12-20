@extends('backend.layouts.master')

@section('title', 'Paramètres de sécurité Auth')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Configuration de la sécurité d'authentification</h4>
                </div>
                <div class="card-body">
                    <form method="post" action="<?=  url('/admin/auth/settings/update') ?>">
                        <?php if (function_exists('csrf_field')) echo csrf_field(); ?>
                        <?php if (empty($_SESSION['_csrf_token'])) { $_SESSION['_csrf_token'] = bin2hex(random_bytes(32)); } ?>
                        <input type="hidden" name="_token" value="<?= htmlspecialchars($_SESSION['_csrf_token']) ?>">
                        <div class="mb-3 row">
                            <label class="col-sm-5 col-form-label">Max login retries</label>
                            <div class="col-sm-7">
                                <input type="number" class="form-control" name="max_login_retries" min="1" value="<?= htmlspecialchars(get_setting('max_login_retries', $settings, 5)) ?>">
                                <small class="form-text text-muted">Nombre maximum de tentatives de connexion avant blocage temporaire.</small>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-5 col-form-label">Lockout period (minutes)</label>
                            <div class="col-sm-7">
                                <input type="number" class="form-control" name="lockout_period" min="1" value="<?= htmlspecialchars(get_setting('lockout_period', $settings, 15)) ?>">
                                <small class="form-text text-muted">Durée du blocage temporaire après dépassement des tentatives.</small>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-5 col-form-label">Max Lockouts</label>
                            <div class="col-sm-7">
                                <input type="number" class="form-control" name="max_lockouts" min="1" value="<?= htmlspecialchars(get_setting('max_lockouts', $settings, 3)) ?>">
                                <small class="form-text text-muted">Nombre maximal de blocages avant blocage prolongé du compte.</small>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-5 col-form-label">Password Reset Retries</label>
                            <div class="col-sm-7">
                                <input type="number" class="form-control" name="password_reset_retries" min="1" value="<?= htmlspecialchars(get_setting('password_reset_retries', $settings, 3)) ?>">
                                <small class="form-text text-muted">Nombre de tentatives autorisées pour la réinitialisation du mot de passe.</small>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label class="col-sm-5 col-form-label">IP/Range/User Blacklists</label>
                            <div class="col-sm-7">
                                <textarea class="form-control" name="blacklist" rows="4" placeholder='{"ips":["127.0.0.1"],"users":["admin"]}'><?= htmlspecialchars(get_setting('blacklist', $settings, '')) ?></textarea>
                                <small class="form-text text-muted">Format JSON : {"ips": ["1.2.3.4", "192.168.1."], "users": ["user1"]}</small>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-success">Enregistrer tous les paramètres</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
function get_setting($key, $settings, $default = '') {
    foreach ($settings as $s) {
        if (is_array($s) && isset($s['key']) && $s['key'] === $key) return $s['value'];
        if (is_object($s) && isset($s->key) && $s->key === $key) return $s->value;
    }
    // fallback : utiliser la méthode statique si disponible
    if (class_exists('Modules\\Auth\\Models\\AuthSetting')) {
        return \Modules\Auth\Models\AuthSetting::getValue($key, $default);
    }
    return $default;
}
?>
@endsection
