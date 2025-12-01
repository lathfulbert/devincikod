@extends('backend.layouts.master')

@section('title', $title ?? 'API Settings')

@section('content')

<div class="container-fluid">
    <?php
    // Breadcrumb
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'Settings', 'url' => '/admin/settings'],
        ['label' => 'API Settings']
    ];
    component('breadcrumb');
    ?>
</div>

<div class="container-fluid">
    <!-- Messages Flash -->
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <?php
            $card_title = "API Keys Configuration";
            component('card-start');
            ?>

            <form action="<?= url('/admin/settings/api/update') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="alert alert-info">
                    <i data-feather="info"></i>
                    <strong>Note:</strong> Configure your third-party API keys here. These keys will be used for various integrations.
                </div>

                <!-- OpenAI API -->
                <div class="mb-4">
                    <h5>OpenAI API</h5>
                    <div class="mb-3">
                        <label for="openai_api_key" class="form-label">OpenAI API Key</label>
                        <div class="input-group">
                            <input type="password" name="openai_api_key" id="openai_api_key"
                                   class="form-control"
                                   value="<?= htmlspecialchars($settings['openai_api_key'] ?? '') ?>"
                                   placeholder="sk-...">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('openai_api_key')">
                                <i data-feather="eye"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">
                            Get your API key from <a href="https://platform.openai.com/api-keys" target="_blank">OpenAI Platform</a>
                        </small>
                    </div>
                </div>

                <hr>

                <!-- Google API -->
                <div class="mb-4">
                    <h5>Google APIs</h5>
                    <div class="mb-3">
                        <label for="google_api_key" class="form-label">Google API Key</label>
                        <div class="input-group">
                            <input type="password" name="google_api_key" id="google_api_key"
                                   class="form-control"
                                   value="<?= htmlspecialchars($settings['google_api_key'] ?? '') ?>"
                                   placeholder="AIza...">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('google_api_key')">
                                <i data-feather="eye"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">
                            Get your API key from <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud Console</a>
                        </small>
                    </div>
                </div>

                <hr>

                <!-- Payment Gateways -->
                <div class="mb-4">
                    <h5>Payment Gateways</h5>

                    <div class="mb-3">
                        <label for="stripe_api_key" class="form-label">Stripe API Key</label>
                        <div class="input-group">
                            <input type="password" name="stripe_api_key" id="stripe_api_key"
                                   class="form-control"
                                   value="<?= htmlspecialchars($settings['stripe_api_key'] ?? '') ?>"
                                   placeholder="sk_live_...">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('stripe_api_key')">
                                <i data-feather="eye"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">
                            Get your API key from <a href="https://dashboard.stripe.com/apikeys" target="_blank">Stripe Dashboard</a>
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="paypal_client_id" class="form-label">PayPal Client ID</label>
                        <div class="input-group">
                            <input type="password" name="paypal_client_id" id="paypal_client_id"
                                   class="form-control"
                                   value="<?= htmlspecialchars($settings['paypal_client_id'] ?? '') ?>"
                                   placeholder="AX...">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('paypal_client_id')">
                                <i data-feather="eye"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">
                            Get your Client ID from <a href="https://developer.paypal.com/dashboard/applications" target="_blank">PayPal Developer</a>
                        </small>
                    </div>
                </div>

                <hr>

                <!-- SMS & Maps -->
                <div class="mb-4">
                    <h5>Other Services</h5>

                    <div class="mb-3">
                        <label for="sms_api_key" class="form-label">SMS API Key</label>
                        <div class="input-group">
                            <input type="password" name="sms_api_key" id="sms_api_key"
                                   class="form-control"
                                   value="<?= htmlspecialchars($settings['sms_api_key'] ?? '') ?>"
                                   placeholder="Enter SMS service API key">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('sms_api_key')">
                                <i data-feather="eye"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">API key for SMS service integration</small>
                    </div>

                    <div class="mb-3">
                        <label for="map_api_key" class="form-label">Map API Key (Google Maps)</label>
                        <div class="input-group">
                            <input type="password" name="map_api_key" id="map_api_key"
                                   class="form-control"
                                   value="<?= htmlspecialchars($settings['map_api_key'] ?? '') ?>"
                                   placeholder="AIza...">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('map_api_key')">
                                <i data-feather="eye"></i>
                            </button>
                        </div>
                        <small class="form-text text-muted">For maps and geolocation features</small>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i> Save API Settings
                    </button>
                    <a href="<?= url('/admin/settings') ?>" class="btn btn-secondary">
                        <i data-feather="x"></i> Cancel
                    </a>
                </div>
            </form>

            <?php component('card-end'); ?>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    feather.replace();

    function togglePassword(fieldId) {
        const field = document.getElementById(fieldId);
        const icon = event.currentTarget.querySelector('i');

        if (field.type === 'password') {
            field.type = 'text';
            icon.setAttribute('data-feather', 'eye-off');
        } else {
            field.type = 'password';
            icon.setAttribute('data-feather', 'eye');
        }

        feather.replace();
    }
</script>
@endsection
