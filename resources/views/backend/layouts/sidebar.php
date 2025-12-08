<!-- Page Sidebar Start-->
<div class="sidebar-wrapper" data-sidebar-layout="stroke-svg">
    <div>
        <div class="logo-wrapper"><a href="<?= route('admin.default_dashboard') ?>"><img class="img-fluid for-light"
                    src="<?= site_logo() ?>" alt="<?= htmlspecialchars(site_name()) ?>"><img class="img-fluid for-dark"
                    src="<?= site_logo_dark() ?>" alt="<?= htmlspecialchars(site_name()) ?>"></a>
            <div class="back-btn"><i class="fa-solid fa-angle-left"></i></div>
            <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid">
                </i></div>
        </div>
        <div class="logo-icon-wrapper"><a href="<?= route('admin.default_dashboard') ?>"><img class="img-fluid"
                    src="<?= site_logo_icon() ?>" alt="<?= htmlspecialchars(site_name()) ?>"></a></div>
        <nav class="sidebar-main">
            <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
            <div id="sidebar-menu">
                <ul class="sidebar-links" id="simple-bar">
                    <li class="back-btn"><a href="<?= route('admin.default_dashboard') ?>"><img class="img-fluid"
                                src="<?= site_logo_icon() ?>" alt="<?= htmlspecialchars(site_name()) ?>"></a>
                        <div class="mobile-back text-end"><span>Back</span><i class="fa-solid fa-angle-right ps-2"
                                aria-hidden="true"></i></div>
                    </li>
                    <li class="pin-title sidebar-main-title">
                        <div>
                            <h6>Pinned</h6>
                        </div>
                    </li>

                    <?php \App\Core\View\SidebarService::render(); ?>

                    <li class="sidebar-main-title">
                        <div>
                            <h6 class="lan-1">General</h6>
                        </div>
                    </li>

                </ul>
            </div>
            <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
        </nav>
    </div>
</div>
<!-- Page Sidebar Ends-->