@extends('backend.layouts.master')

@section('title', $title ?? 'Admin')

@section('css')
<style>
/* Cards des modules */
.module-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.module-card:hover {
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
    transform: translateY(-3px);
}

/* Header des modules avec gradient */
.module-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-radius: 12px 12px 0 0;
}

.module-header:hover {
    background: linear-gradient(135deg, #5568d3 0%, #6a4094 100%);
}

.module-header h5 {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
    color: white;
    font-size: 1rem;
    font-weight: 600;
}

.module-header .badge {
    background: rgba(255,255,255,0.3);
    color: white;
    font-size: 0.8rem;
}

/* Body des modules */
.module-body {
    background: #fafbfc;
    max-height: 450px;
    overflow-y: auto;
    padding: 15px;
}

.module-body::-webkit-scrollbar {
    width: 6px;
}

.module-body::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.module-body::-webkit-scrollbar-thumb {
    background: #667eea;
    border-radius: 10px;
}

.module-body::-webkit-scrollbar-thumb:hover {
    background: #5568d3;
}

/* Items de permissions */
.permission-item {
    padding: 10px 12px;
    background: white;
    border-radius: 6px;
    border: 1px solid #e0e0e0;
    margin-bottom: 8px;
    transition: all 0.2s ease;
}

.permission-item:hover {
    background: #f0f7ff;
    border-color: #667eea;
    transform: translateX(3px);
}

.permission-item .form-check-input {
    margin-top: 2px;
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.permission-item .form-check-input:checked {
    background-color: #667eea;
    border-color: #667eea;
}

.permission-item .form-check-label {
    display: flex;
    flex-direction: column;
    cursor: pointer;
    margin-left: 8px;
}

.permission-name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 0.9rem;
}

.permission-description {
    color: #6c757d;
    font-size: 0.8rem;
    margin-top: 2px;
}

.module-stats {
    display: flex;
    gap: 10px;
    align-items: center;
}

/* Zone de recherche et filtres */
.search-filter-bar {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 20px;
}

#searchPermissions, #filterModule {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 10px 15px;
    transition: all 0.2s;
}

#searchPermissions:focus, #filterModule:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

/* Badge de compteur */
#selectedCount {
    font-size: 0.9rem;
    padding: 6px 12px;
    border-radius: 20px;
}

/* Highlight pour la recherche */
.highlight {
    background: linear-gradient(120deg, #ffeaa7 0%, #fdcb6e 100%);
    padding: 2px 4px;
    border-radius: 3px;
    font-weight: 700;
}
</style>
@endsection

@section('content')

<div class="container-fluid">
    <?php
    // Breadcrumb
    $breadcrumb = [
        ['label' => 'Dashboard', 'url' => '/admin/dashboard'],
        ['label' => 'Rôles', 'url' => '/admin/roles'],
        ['label' => 'Créer']
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
            component('card-start', ['card_title' => "Créer un nouveau rôle"]);
            ?>

            <form action="<?= url('/admin/roles/store') ?>" method="POST" id="roleForm">
                <?= csrf_field() ?>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="name" class="form-label">Nom du rôle <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control" required autofocus>
                        <small class="form-text text-muted">Exemple: Administrateur, Éditeur, etc.</small>
                    </div>

                    <div class="col-md-4">
                        <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                        <input type="text" name="slug" id="slug" class="form-control" required>
                        <small class="form-text text-muted">Exemple: admin, editor, etc.</small>
                    </div>

                    <div class="col-md-4">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="description" class="form-control" rows="1"></textarea>
                        <small class="form-text text-muted">Description optionnelle du rôle</small>
                    </div>
                </div>

                <hr class="my-4">

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            <i data-feather="shield"></i> Permissions
                            <span class="badge bg-primary ms-2" id="selectedCount">0 sélectionnée(s)</span>
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-success" id="selectAllBtn">
                                <i data-feather="check-square"></i> Tout sélectionner
                            </button>
                            <button type="button" class="btn btn-sm btn-warning" id="deselectAllBtn">
                                <i data-feather="square"></i> Tout désélectionner
                            </button>
                        </div>
                    </div>

                    <!-- Search and Filter -->
                    <div class="search-filter-bar">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" id="searchPermissions" placeholder="🔍 Rechercher une permission...">
                            </div>
                            <div class="col-md-6">
                                <select class="form-select" id="filterModule">
                                    <option value="">📦 Tous les modules</option>
                                    <?php foreach ($permissionsByModule as $module): ?>
                                        <option value="<?= htmlspecialchars($module['name']) ?>">
                                            <?= htmlspecialchars($module['name']) ?> (<?= count($module['permissions']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions by Module -->
                    <div id="permissionsContainer">
                        <div class="row g-3">
                            <?php if (!empty($permissionsByModule)): ?>
                                <?php foreach ($permissionsByModule as $moduleName => $module): ?>
                                    <div class="col-12 col-md-6 col-lg-4" data-module="<?= htmlspecialchars($moduleName) ?>">
                                        <div class="card module-card h-100">
                                            <div class="card-header module-header" onclick="toggleModule(this)">
                                                <h5>
                                                    <i data-feather="<?= htmlspecialchars($module['icon']) ?>"></i>
                                                    <?= htmlspecialchars($module['name']) ?>
                                                    <span class="badge"><?= count($module['permissions']) ?></span>
                                                </h5>
                                                <div class="module-stats">
                                                    <button type="button" class="btn btn-success btn-sm select-all-btn" onclick="event.stopPropagation(); toggleModulePermissions(this, '<?= htmlspecialchars($moduleName) ?>')">
                                                        <i data-feather="check-circle" style="width: 14px; height: 14px;"></i> Tout
                                                    </button>
                                                    <i data-feather="chevron-down" class="toggle-icon"></i>
                                                </div>
                                            </div>
                                            <div class="card-body module-body" style="display: block;">
                                                <?php foreach ($module['permissions'] as $permission): ?>
                                                    <div class="permission-item" data-permission-name="<?= strtolower($permission->name) ?>" data-permission-desc="<?= strtolower($permission->description ?? '') ?>">
                                                        <div class="form-check">
                                                            <input class="form-check-input permission-checkbox"
                                                                type="checkbox"
                                                                name="permissions[]"
                                                                value="<?= $permission->id ?>"
                                                                id="perm_<?= $permission->id ?>"
                                                                data-module="<?= htmlspecialchars($moduleName) ?>"
                                                                onchange="updateCount()">
                                                            <label class="form-check-label" for="perm_<?= $permission->id ?>">
                                                                <span class="permission-name"><?= htmlspecialchars($permission->name) ?></span>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="alert alert-warning">
                                        <i data-feather="alert-circle"></i> Aucune permission disponible
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i data-feather="save"></i> Créer le rôle
                    </button>
                    <a href="<?= url('/admin/roles') ?>" class="btn btn-secondary btn-lg">
                        <i data-feather="x"></i> Annuler
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
// Auto-generate slug from name
document.getElementById('name').addEventListener('input', function() {
    const slug = this.value.toLowerCase()
        .replace(/[éèê]/g, 'e')
        .replace(/[àâ]/g, 'a')
        .replace(/[ç]/g, 'c')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');
    document.getElementById('slug').value = slug;
});

// Toggle module visibility
function toggleModule(header) {
    const body = header.nextElementSibling;
    const icon = header.querySelector('.toggle-icon');

    if (body.style.display === 'none') {
        body.style.display = 'block';
        icon.setAttribute('data-feather', 'chevron-down');
    } else {
        body.style.display = 'none';
        icon.setAttribute('data-feather', 'chevron-right');
    }
    feather.replace();
}

// Toggle all permissions in a module
function toggleModulePermissions(btn, moduleName) {
    const checkboxes = document.querySelectorAll(`input[data-module="${moduleName}"]`);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);

    checkboxes.forEach(cb => {
        cb.checked = !allChecked;
    });

    updateCount();
}

// Select all permissions
document.getElementById('selectAllBtn').addEventListener('click', function() {
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = true);
    updateCount();
});

// Deselect all permissions
document.getElementById('deselectAllBtn').addEventListener('click', function() {
    document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
    updateCount();
});

// Update selected count
function updateCount() {
    const count = document.querySelectorAll('.permission-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = count + ' sélectionnée(s)';
}

// Search functionality
document.getElementById('searchPermissions').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const permissionItems = document.querySelectorAll('.permission-item');

    permissionItems.forEach(item => {
        const name = item.getAttribute('data-permission-name');
        const desc = item.getAttribute('data-permission-desc');
        const text = name + ' ' + desc;

        if (text.includes(searchTerm)) {
            item.style.display = '';
            // Highlight search term
            if (searchTerm) {
                const label = item.querySelector('.permission-name');
                const originalText = label.textContent;
                const regex = new RegExp(`(${searchTerm})`, 'gi');
                label.innerHTML = originalText.replace(regex, '<span class="highlight">$1</span>');
            }
        } else {
            item.style.display = 'none';
        }
    });

    // Hide empty modules
    document.querySelectorAll('.module-card').forEach(card => {
        const visibleItems = card.querySelectorAll('.permission-item[style=""]').length;
        card.style.display = visibleItems > 0 ? '' : 'none';
    });
});

// Filter by module
document.getElementById('filterModule').addEventListener('change', function() {
    const selectedModule = this.value;

    document.querySelectorAll('.module-card').forEach(card => {
        if (!selectedModule || card.getAttribute('data-module') === selectedModule) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
});

// Initialize
feather.replace();
updateCount();
</script>
@endsection
