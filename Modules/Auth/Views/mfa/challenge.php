@extends('backend.layouts.master')

@section('title', 'MFA Challenge')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card mt-5">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4">Two-Factor Authentication</h3>

                    <?php component('alerts'); ?>

                    <form method="POST" action="<?= url('/auth/mfa/verify') ?>">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label for="method" class="form-label">Select Method</label>
                            <select class="form-control" id="method" name="method" required>
                                <?php foreach ($methods as $method): ?>
                                    <option value="<?= $method['type'] ?>">
                                        <?= ucfirst($method['type']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="code" class="form-label">Verification Code</label>
                            <input type="text" class="form-control" id="code" name="code" required autofocus>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Verify</button>
                    </form>

                    <div class="text-center mt-3">
                        <button class="btn btn-link" onclick="sendOtp()">Send new code (SMS/Email)</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function sendOtp() {
        const method = document.getElementById('method').value;
        fetch('<?= url('/auth/mfa/send-otp') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'method=' + method + '&<?= csrf_field() ?>'
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            });
    }
</script>
@endsection