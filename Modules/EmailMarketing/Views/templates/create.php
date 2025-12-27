@extends('backend.layouts.master')

@section('title', 'Create Email Template')

@section('content')

<div class="container-fluid">
    <div class="page-title">
        <div class="row">
            <div class="col-6">
                <h3><?= $title ?></h3>
            </div>
            <div class="col-6">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= url('/') ?>"><i data-feather="home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/email-marketing') ?>">Email Marketing</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('/admin/email-marketing/templates') ?>">Templates</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <?php component('alerts'); ?>
</div>

<div class="container-fluid">
    <form method="POST" action="<?= url('/admin/email-marketing/templates/store') ?>">
        <?= csrf_field() ?>
        <div class="row">
            <div class="col-md-9">
                <div class="card">
                    <div class="card-header">
                        <h5>Template Content</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Template Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required
                                placeholder="Enter template name">
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <input type="text" class="form-control" id="description" name="description"
                                placeholder="Brief description of this template">
                        </div>

                        <div class="form-group">
                            <label for="html_content">HTML Content <span class="text-danger">*</span></label>
                            <div class="alert alert-info">
                                <small>
                                    <strong>Tip:</strong> Use variables like <code>&#123;&#123;first_name&#125;&#125;</code>, <code>&#123;&#123;last_name&#125;&#125;</code>, <code>&#123;&#123;email&#125;&#125;</code> for personalization.
                                </small>
                            </div>
                            <textarea id="html_content" name="html_content" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card">
                    <div class="card-header">
                        <h5>Template Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select class="form-control" id="category" name="category">
                                <option value="">-- Select Category --</option>
                                <option value="newsletter">Newsletter</option>
                                <option value="promotional">Promotional</option>
                                <option value="transactional">Transactional</option>
                                <option value="welcome">Welcome</option>
                                <option value="follow-up">Follow-up</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_active"
                                    name="is_active" value="1" checked>
                                <label class="custom-control-label" for="is_active">
                                    Active
                                </label>
                            </div>
                            <small class="form-text text-muted">Only active templates can be used in campaigns</small>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Quick Templates</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Start with a basic template:</p>
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-block mb-2"
                                onclick="loadBasicTemplate()">
                                <i data-feather="file-text"></i> Basic Newsletter
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success btn-block mb-2"
                                onclick="loadPromoTemplate()">
                                <i data-feather="tag"></i> Promotional
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info btn-block"
                                onclick="loadWelcomeTemplate()">
                                <i data-feather="smile"></i> Welcome Email
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i data-feather="save"></i> Create Template
                        </button>
                        <a href="<?= url('/admin/email-marketing/templates') ?>" class="btn btn-secondary btn-block">
                            <i data-feather="x"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    feather.replace();

    // Initialize TinyMCE
    tinymce.init({
        selector: '#html_content',
        height: 500,
        plugins: 'code preview link image lists table wordcount',
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code preview',
        menubar: 'file edit view insert format tools table',
        content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }',
        setup: function(editor) {
            editor.ui.registry.addButton('insertVariable', {
                text: 'Insert Variable',
                onAction: function() {
                    const variable = prompt('Enter variable name (e.g., first_name):');
                    if (variable) {
                        editor.insertContent('{' + '{' + variable + '}' + '}');
                    }
                }
            });
        }
    });

    // Quick template functions
    function loadBasicTemplate() {
        tinymce.get('html_content').setContent(`
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Newsletter</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f4f4f4; padding: 20px; text-align: center;">
        <h1 style="color: #333;">Hello &#123;&#123;first_name&#125;&#125;!</h1>
    </div>
    <div style="padding: 20px; background-color: #fff;">
        <h2>Welcome to Our Newsletter</h2>
        <p>This is a basic newsletter template. Customize it to match your brand!</p>
        <p>Best regards,<br>The Team</p>
    </div>
    <div style="background-color: #f4f4f4; padding: 10px; text-align: center; font-size: 12px; color: #666;">
        <p>&copy; 2024 Your Company. All rights reserved.</p>
    </div>
</body>
</html>
        `);
    }

    function loadPromoTemplate() {
        tinymce.get('html_content').setContent(`
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Special Offer</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px; text-align: center; color: white;">
        <h1 style="margin: 0; font-size: 32px;">🎉 Special Offer Just For You, &#123;&#123;first_name&#125;&#125;!</h1>
        <p style="font-size: 18px; margin: 10px 0;">Limited Time: 50% OFF</p>
    </div>
    <div style="padding: 30px; background-color: #fff;">
        <h2>Don't Miss Out!</h2>
        <p>We're offering an exclusive 50% discount on all products. This offer won't last long!</p>
        <div style="text-align: center; margin: 30px 0;">
            <a href="#" style="background-color: #667eea; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                Shop Now
            </a>
        </div>
    </div>
</body>
</html>
        `);
    }

    function loadWelcomeTemplate() {
        tinymce.get('html_content').setContent(`
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="text-align: center; padding: 40px 20px; background-color: #f8f9fa;">
        <h1 style="color: #28a745; margin: 0;">👋 Welcome, &#123;&#123;first_name&#125;&#125;!</h1>
        <p style="font-size: 18px; color: #666;">We're thrilled to have you on board</p>
    </div>
    <div style="padding: 30px; background-color: #fff;">
        <h2>Getting Started</h2>
        <p>Thank you for joining us! Here's what you can do next:</p>
        <ul>
            <li>Complete your profile</li>
            <li>Explore our features</li>
            <li>Connect with the community</li>
        </ul>
        <div style="text-align: center; margin: 30px 0;">
            <a href="#" style="background-color: #28a745; color: white; padding: 15px 40px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                Get Started
            </a>
        </div>
    </div>
</body>
</html>
        `);
    }
</script>
@endsection