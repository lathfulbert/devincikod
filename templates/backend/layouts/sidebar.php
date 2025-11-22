<div class="sidebar-wrapper" sidebar-layout="stroke-svg">
  <div>
    <div class="logo-wrapper"><a href="<?= url("/") ?>"><img class="img-fluid for-light" src="<?= url() ?>/assets/images/logo/logo.png" alt=""><img class="img-fluid for-dark" src="<?= url() ?>/assets/images/logo/logo_dark.png" alt=""></a>
      <div class="back-btn"><i class="fa fa-angle-left"></i></div>
      <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="grid"> </i></div>
    </div>
    <div class="logo-icon-wrapper"><a href="<?= url("/") ?>"><img class="img-fluid" src="<?= url() ?>/assets/images/logo/logo-icon.png" alt=""></a></div>
    <nav class="sidebar-main">
      <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
      <div id="sidebar-menu">
        <ul class="sidebar-links" id="simple-bar">
          <li class="back-btn"><a href="<?= url("/") ?>"><img class="img-fluid" src="<?= url() ?>/assets/images/logo/logo-icon.png" alt=""></a>
            <div class="mobile-back text-end"><span>Back</span><i class="fa fa-angle-right ps-2" aria-hidden="true"></i></div>
          </li>
          <li class="pin-title sidebar-main-title">
                    <div> 
                      <h6>Pinned</h6>
                    </div>
                  </li>
          <li class="sidebar-main-title">
            <div>
              <h6 class="lan-1">General</h6>
            </div>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
            <label class="badge badge-light-primary">5</label><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-home"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-home"></use>
              </svg><span class="lan-3">Dashboard</span></a>
            <ul class="sidebar-submenu">
              <li><a class="lan-4" href="<?= url("dashboards/default-dashboard") ?>">Default</a></li>
              <li><a class="lan-5" href="<?= url("dashboards/ecommerce") ?>">Ecommerce</a></li>
              <li><a href="<?= url("dashboards/online_course") ?>">Online course</a></li>
              <li><a href="<?= url("dashboards/crypto_dashboard") ?>">Crypto</a></li>
              <li><a href="<?= url("dashboards/social_dashboard") ?>">Social</a></li>
            </ul>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-widget"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-widget"></use>
              </svg><span class="lan-6">Widgets</span></a>
            <ul class="sidebar-submenu">
              <li><a href="<?= url("Widgets/general-widget") ?>">General</a></li>
              <li><a href="<?= url("Widgets/chart-widget") ?>">Chart</a></li>
            </ul>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-layout"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-layout"></use>
              </svg><span class="lan-7">Page layout</span></a>
            <ul class="sidebar-submenu">
              <li><a href="<?= url("page-layout/box-layout") ?>">Boxed</a></li>
              <li><a href="<?= url("page-layout/layout-rtl") ?>">RTL</a></li>
              <li><a href="<?= url("page-layout/layout-dark") ?>">Dark Layout</a></li>
              <li><a href="<?= url("page-layout/hide-on-scroll") ?>">Hide Nav Scroll</a></li>
              <li><a href="<?= url("page-layout/footer-light") ?>">Footer Light</a></li>
              <li><a href="<?= url("page-layout/footer-dark") ?>">Footer Dark</a></li>
              <li><a href="<?= url("page-layout/footer-fixed") ?>">Footer Fixed</a></li>
            </ul>
          </li>
          <li class="sidebar-main-title">
            <div>
              <h6 class="lan-8">Applications</h6>
            </div>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
            <label class="badge badge-light-secondary">New</label><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-project"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-project"></use>
              </svg> <span> Project </span> </a>
            <ul class="sidebar-submenu">
              <li><a href="<?= url("projects/project-list") ?>">Project List</a></li>
              <li><a href="<?= url("projects/project-create") ?>">Create new</a></li>
            </ul>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="<?= url("file-manager") ?>">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-file"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-file"></use>
              </svg><span>File manager</span></a></li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i>
            <label class="badge badge-light-danger">Latest </label><a class="sidebar-link sidebar-title link-nav" href="<?= url("kanban") ?>">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-board"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-board"></use>
              </svg><span>kanban Board</span></a>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-ecommerce"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-ecommerce"></use>
              </svg><span>Ecommerce</span></a>
            <ul class="sidebar-submenu">
              <li><a href="<?= url("ecommerce/product") ?>">Product</a></li>
              <li><a href="<?= url("ecommerce/detailed-product-page") ?>">Product page</a></li>
              <li><a href="<?= url("ecommerce/detailed-products-list") ?>">Product list</a></li>
              <li><a href="<?= url("ecommerce/payment-details") ?>">Payment Details</a></li>
              <li><a href="<?= url("ecommerce/order-history") ?>">Order History</a></li>
              <li><a href="<?= url("ecommerce/invoice-template") ?>">Invoice</a></li>
              <li><a href="<?= url("ecommerce/cart") ?>">Cart</a></li>
              <li><a href="<?= url("ecommerce/list-wish") ?>">Wishlist</a></li>
              <li><a href="<?= url("ecommerce/checkout") ?>">Checkout</a></li>
              <li><a href="<?= url("ecommerce/pricing") ?>">Pricing </a></li>
            </ul>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-email"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-email"></use>
              </svg><span>Email</span></a>
            <ul class="sidebar-submenu">
              <li><a href="<?= url("email/email-application") ?>">Email App</a></li>
              <li><a href="<?= url("email/email-compose") ?>">Email Compose</a></li>
            </ul>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-chat"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-chat"></use>
              </svg><span>Chat</span></a>
            <ul class="sidebar-submenu">
              <li><a href="<?= url("chat/chat-app") ?>">Chat App</a></li>
              <li><a href="<?= url("chat/video-chat") ?>">Video chat</a></li>
            </ul>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-user"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-user"></use>
              </svg><span>Users</span></a>
            <ul class="sidebar-submenu">
              <li><a href="<?= url("user/user-profile") ?>">Users Profile</a></li>
              <li><a href="<?= url("user/edit-profile") ?>">Users Edit</a></li>
              <li><a href="<?= url("user/user-cards") ?>">Users Cards</a></li>
            </ul>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="<?= url("bookmark") ?>">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-bookmark"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-bookmark"> </use>
              </svg><span>Bookmarks</span></a></li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="<?= url("contacts") ?>">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-contact"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-contact"> </use>
              </svg><span>Contacts</span></a></li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="<?= url("task") ?>">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-task"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-task"> </use>
              </svg><span>Tasks</span></a></li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="<?= url("calendar-basic") ?>">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-calendar"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-calender"></use>
              </svg><span>Calendar</span></a></li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="<?= url("social-app") ?>">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-social"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-social"> </use>
              </svg><span>Social App</span></a></li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="<?= url("to-do") ?>">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-to-do"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-to-do"> </use>
              </svg><span>To-Do</span></a></li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="<?= url("search") ?>">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-search"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-search"> </use>
              </svg><span>Search Result</span></a></li>
          <li class="sidebar-main-title">
            <div>
              <h6>Forms & Table</h6>
            </div>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-form"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-form"> </use>
              </svg><span>Forms</span></a>
            <ul class="sidebar-submenu">
              <li><a class="submenu-title" href="#">Form Controls<span class="sub-arrow"><i class="fa fa-angle-right"></i></span></a>
                <ul class="nav-sub-childmenu submenu-content">
                  <li><a href="<?= url("forms/form-validation") ?>">Form Validation</a></li>
                  <li><a href="<?= url("forms/base-input") ?>">Base Inputs</a></li>
                  <li><a href="<?= url("forms/radio-checkbox-control") ?>">Checkbox & Radio</a></li>
                  <li><a href="<?= url("forms/input-group") ?>">Input Groups</a></li>
                  <li><a href="<?= url("forms/megaoptions") ?>">Mega Options</a></li>
                </ul>
              </li>
              <li><a class="submenu-title" href="#">Form Widgets<span class="sub-arrow"><i class="fa fa-angle-right"></i></span></a>
                <ul class="nav-sub-childmenu submenu-content">
                  <li><a href="<?= url("forms/datepicker") ?>">Datepicker</a></li>
                  <li><a href="<?= url("forms/time-picker") ?>">Timepicker</a></li>
                  <li><a href="<?= url("forms/datetimepicker") ?>">Datetimepicker</a></li>
                  <li><a href="<?= url("forms/daterangepicker") ?>">Daterangepicker</a></li>
                  <li><a href="<?= url("forms/touchspin") ?>">Touchspin</a></li>
                  <li><a href="<?= url("forms/select2") ?>">Select2</a></li>
                  <li><a href="<?= url("forms/switch") ?>">Switch</a></li>
                  <li><a href="<?= url("forms/typeahead") ?>">Typeahead</a></li>
                  <li><a href="<?= url("forms/clipboard") ?>">Clipboard</a></li>
                </ul>
              </li>
              <li><a class="submenu-title" href="#">Form layout<span class="sub-arrow"><i class="fa fa-angle-right"></i></span></a>
                <ul class="nav-sub-childmenu submenu-content">
                  <li><a href="<?= url("forms/default-form") ?>">Default Forms</a></li>
                  <li><a href="<?= url("forms/form-wizard") ?>">Form Wizard 1</a></li>
                  <li><a href="<?= url("forms/second-form-wizard") ?>">Form Wizard 2</a></li>
                  <li><a href="<?= url("forms/third-form-wizard") ?>">Form Wizard 3</a></li>
                </ul>
              </li>
            </ul>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-table"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-table"></use>
              </svg><span>Tables</span></a>
            <ul class="sidebar-submenu">
              <li><a class="submenu-title" href="#">Bootstrap Tables<span class="sub-arrow"><i class="fa fa-angle-right"></i></span></a>
                <ul class="nav-sub-childmenu submenu-content">
                  <li><a href="<?= url("tables/bootstrap-basic-table") ?>">Basic Tables</a></li>
                  <li><a href="<?= url("tables/table-components") ?>">Table components</a></li>
                </ul>
              </li>
              <li><a class="submenu-title" href="#">Data Tables<span class="sub-arrow"><i class="fa fa-angle-right"></i></span></a>
                <ul class="nav-sub-childmenu submenu-content">
                  <li><a href="<?= url("tables/datatable-basic-init") ?>">Basic Init</a></li>
                  <li><a href="<?= url("tables/datatable-API") ?>">API</a></li>
                  <li><a href="<?= url("tables/datatable-data-source") ?>">Data Sources</a></li>
                </ul>
              </li>
              <li><a href="<?= url("tables/datatable-ext-autofill") ?>">Ex. Data Tables</a></li>
              <li><a href="<?= url("tables/jsgrid-table") ?>">Js Grid Table </a></li>
            </ul>
          </li>
          <li class="sidebar-main-title">
            <div>
              <h6>Components</h6>
            </div>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-Bonus-ui"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-Bonus-ui"></use>
              </svg><span>Ui Kits</span></a>
            <ul class="sidebar-submenu">
              <li><a href="<?= url("ui-kits/typography") ?>">Typography</a></li>
              <li><a href="<?= url("ui-kits/avatars") ?>">Avatars</a></li>
              <li><a href="<?= url("ui-kits/helper-classes") ?>">helper classes</a></li>
              <li><a href="<?= url("ui-kits/grid") ?>">Grid</a></li>
              <li><a href="<?= url("ui-kits/tag-pills") ?>">Tag & pills</a></li>
              <li><a href="<?= url("ui-kits/progress-bar") ?>">Progress</a></li>
              <li><a href="<?= url("ui-kits/modal") ?>">Modal</a></li>
              <li><a href="<?= url("ui-kits/alert") ?>">Alert</a></li>
              <li><a href="<?= url("ui-kits/popover") ?>">Popover</a></li>
              <li><a href="<?= url("ui-kits/tooltip") ?>">Tooltip</a></li>
              <li><a href="<?= url("ui-kits/loader") ?>">Spinners</a></li>
              <li><a href="<?= url("ui-kits/dropdown") ?>">Dropdown</a></li>
              <li><a href="<?= url("ui-kits/according") ?>">Accordion</a></li>
              <li><a class="submenu-title" href="#">Tabs<span class="sub-arrow"><i class="fa fa-angle-right"></i></span></a>
                <ul class="nav-sub-childmenu submenu-content">
                  <li><a href="<?= url("ui-kits/tab-bootstrap") ?>">Bootstrap Tabs</a></li>
                  <li><a href="<?= url("ui-kits/tab-material") ?>">Line Tabs</a></li>
                </ul>
              </li>
              <li><a href="<?= url("ui-kits/box-shadow") ?>">Shadow</a></li>
              <li><a href="<?= url("ui-kits/list") ?>">Lists</a></li>
            </ul>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-bonus-kit"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-bonus-kit"></use>
              </svg><span>Bonus Ui</span></a>
            <ul class="sidebar-submenu">
              <li><a href="<?= url("Bonus-ui/scrollable") ?>">Scrollable</a></li>
              <li><a href="<?= url("Bonus-ui/tree") ?>">Tree view</a></li>
              <li><a href="<?= url("Bonus-ui/bootstrap-notify") ?>">Bootstrap Notify</a></li>
              <li><a href="<?= url("Bonus-ui/rating") ?>">Rating</a></li>
              <li><a href="<?= url("Bonus-ui/dropzone") ?>">dropzone</a></li>
              <li><a href="<?= url("Bonus-ui/tour") ?>">Tour</a></li>
              <li><a href="<?= url("Bonus-ui/sweet-alert2") ?>">SweetAlert2</a></li>
              <li><a href="<?= url("Bonus-ui/animated-modal") ?>">Animated Modal</a></li>
              <li><a href="<?= url("Bonus-ui/owl-carousel") ?>">Owl Carousel</a></li>
              <li><a href="<?= url("Bonus-ui/ribbons") ?>">Ribbons</a></li>
              <li><a href="<?= url("Bonus-ui/pagination") ?>">Pagination</a></li>
              <li><a href="<?= url("Bonus-ui/breadcrumb") ?>">Breadcrumb</a></li>
              <li><a href="<?= url("Bonus-ui/range-slider") ?>">Range Slider</a></li>
              <li><a href="<?= url("Bonus-ui/image-cropper") ?>">Image cropper</a></li>
              <li><a href="<?= url("Bonus-ui/sticky") ?>">Sticky</a></li>
              <li><a href="<?= url("Bonus-ui/basic-card") ?>">Basic Card</a></li>
              <li><a href="<?= url("Bonus-ui/creative-card") ?>">Creative Card</a></li>
              <li><a href="<?= url("Bonus-ui/tabbed-card") ?>">Tabbed Card</a></li>
              <li><a href="<?= url("Bonus-ui/dragable-card") ?>">Draggable Card</a></li>
              <li><a class="submenu-title" href="#">Timeline<span class="sub-arrow"><i class="fa fa-angle-right"></i></span></a>
                <ul class="nav-sub-childmenu submenu-content">
                  <li><a href="<?= url("Bonus-ui/timeline-v-1") ?>">Timeline 1</a></li>
                  <li><a href="<?= url("Bonus-ui/timeline-v-2") ?>">Timeline 2</a></li>
                </ul>
              </li>
            </ul>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-builders"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-builders"></use>
              </svg><span>Builders</span></a>
            <ul class="sidebar-submenu">
              <li><a href="<?= url("builders/form-builder-1") ?>"> Form Builder 1</a></li>
              <li><a href="<?= url("builders/form-builder-2") ?>"> Form Builder 2</a></li>
              <li><a href="<?= url("builders/pagebuild") ?>">Page Builder</a></li>
              <li><a href="<?= url("builders/button-builder") ?>">Button Builder</a></li>
            </ul>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-animation"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-animation"></use>
              </svg><span>Animation</span></a>
            <ul class="sidebar-submenu">
              <li><a href="<?= url("animations/animate") ?>">Animate</a></li>
              <li><a href="<?= url("animations/scroll-reval") ?>">Scroll Reveal</a></li>
              <li><a href="<?= url("animations/AOS") ?>">AOS animation</a></li>
              <li><a href="<?= url("animations/tilt") ?>">Tilt Animation</a></li>
              <li><a href="<?= url("animations/wow") ?>">Wow Animation</a></li>
            </ul>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-icons"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-icons"></use>
              </svg><span>Icons</span></a>
            <ul class="sidebar-submenu">
              <li><a href="<?= url("icons/flag-icon") ?>">Flag icon</a></li>
              <li><a href="<?= url("icons/font-awesome") ?>">Fontawesome Icon</a></li>
              <li><a href="<?= url("icons/ico-icon") ?>">Ico Icon</a></li>
              <li><a href="<?= url("icons/themify-icon") ?>">Themify Icon</a></li>
              <li><a href="<?= url("icons/feather-icon") ?>">Feather icon</a></li>
              <li><a href="<?= url("icons/whether-icon") ?>">Whether Icon</a></li>
            </ul>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-button"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-botton"></use>
              </svg><span>Buttons</span></a>
            <ul class="sidebar-submenu">
              <li><a href="<?= url("buttons/default-buttons") ?>">Default Style</a></li>
              <li><a href="<?= url("buttons/flat-buttons") ?>">Flat Style</a></li>
              <li><a href="<?= url("buttons/edge-buttons") ?>">Edge Style</a></li>
              <li><a href="<?= url("buttons/raised-button") ?>">Raised Style</a></li>
              <li><a href="<?= url("buttons/group-button") ?>">Button Group</a></li>
            </ul>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-charts"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-charts"></use>
              </svg><span>Charts</span></a>
            <ul class="sidebar-submenu">
              <li><a href="<?= url("charts/echarts") ?>">Echarts</a></li>
              <li><a href="<?= url("charts/chart-apex") ?>">Apex Chart</a></li>
              <li><a href="<?= url("charts/chart-google") ?>">Google Chart</a></li>
              <li><a href="<?= url("charts/chart-sparkline") ?>">Sparkline chart</a></li>
              <li><a href="<?= url("charts/chart-flot") ?>">Flot Chart</a></li>
              <li><a href="<?= url("charts/chart-knob") ?>">Knob Chart</a></li>
              <li><a href="<?= url("charts/chart-morris") ?>">Morris Chart</a></li>
              <li><a href="<?= url("charts/chartjs") ?>">Chatjs Chart</a></li>
              <li><a href="<?= url("charts/chartist") ?>">Chartist Chart</a></li>
              <li><a href="<?= url("charts/chart-peity") ?>">Peity Chart</a></li>
            </ul>
          </li>
          <li class="sidebar-main-title">
            <div>
              <h6>Pages</h6>
            </div>
          </li>    
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="<?= url("sample-page") ?>">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-sample-page"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-sample-page"></use>
              </svg><span>Sample page</span></a></li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="<?= url("internationalization") ?>">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-internationalization"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-internationalization"></use>
              </svg><span>Internationalization</span></a></li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="#" target="_blank">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-starter-kit"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-starter-kit"></use>
              </svg><span>Starter kit</span></a></li>
          <li class="mega-menu"><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-others"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-others"></use>
              </svg><span>Others</span></a>
            <div class="mega-menu-container menu-content">
              <div class="container-fluid">
                <div class="row">
                  <div class="col mega-box">
                    <div class="link-section">
                      <div class="submenu-title">
                        <h5>Error Page</h5>
                      </div>
                      <ul class="submenu-content opensubmegamenu">
                        <li><a href="<?= url("error-pages/error-400") ?>">Error 400</a></li>
                        <li><a href="<?= url("error-pages/error-401") ?>">Error 401</a></li>
                        <li><a href="<?= url("error-pages/error-403") ?>">Error 403</a></li>
                        <li><a href="<?= url("error-pages/error-404") ?>">Error 404</a></li>
                        <li><a href="<?= url("error-pages/error-500") ?>">Error 500</a></li>
                        <li><a href="<?= url("error-pages/error-503") ?>">Error 503</a></li>
                      </ul>
                    </div>
                  </div>
                  <div class="col mega-box">
                    <div class="link-section">
                      <div class="submenu-title">
                        <h5> Authentication</h5>
                      </div>
                      <ul class="submenu-content opensubmegamenu">
                        <li><a href="<?= url("authentications/login") ?>" target="_blank">Login Simple</a></li>
                        <li><a href="<?= url("authentications/login_one") ?>" target="_blank">Login with bg image</a></li>
                        <li><a href="<?= url("authentications/login_two") ?>" target="_blank">Login with image two </a></li>
                        <li><a href="<?= url("authentications/login-bs-validation") ?>" target="_blank">Login With validation</a></li>
                        <li><a href="<?= url("authentications/login-bs-tt-validation") ?>" target="_blank">Login with tooltip</a></li>
                        <li><a href="<?= url("authentications/login-sa-validation") ?>" target="_blank">Login with sweetalert</a></li>
                        <li><a href="<?= url("authentications/sign-up") ?>" target="_blank">Register Simple</a></li>
                        <li><a href="<?= url("authentications/sign-up-one") ?>" target="_blank">Register with Bg Image </a></li>
                        <li><a href="<?= url("authentications/sign-up-two") ?>" target="_blank">Register with image two</a></li>
                        <li><a href="<?= url("authentications/sign-up-wizard") ?>" target="_blank">Register wizard</a></li>
                        <li><a href="<?= url("authentications/unlock") ?>">Unlock User</a></li>
                        <li><a href="<?= url("authentications/forget-password") ?>">Forget Password</a></li>
                        <li><a href="<?= url("authentications/reset-password") ?>">Reset Password</a></li>
                        <li><a href="<?= url("authentications/maintenance") ?>">Maintenance</a></li>
                      </ul>
                    </div>
                  </div>
                  <div class="col mega-box">
                    <div class="link-section">
                      <div class="submenu-title">
                        <h5>Coming Soon</h5>
                      </div>
                      <ul class="submenu-content opensubmegamenu">
                        <li><a href="<?= url("comming-soons/comingsoon") ?>">Coming Simple</a></li>
                        <li><a href="<?= url("comming-soons/comingsoon-bg-video") ?>">Coming with Bg video</a></li>
                        <li><a href="<?= url("comming-soons/comingsoon-bg-img") ?>">Coming with Bg Image</a></li>
                      </ul>
                    </div>
                  </div>
                  <div class="col mega-box">
                    <div class="link-section">
                      <div class="submenu-title">
                        <h5>Email templates</h5>
                      </div>
                      <ul class="submenu-content opensubmegamenu">
                        <li><a href="<?= url("email-templates/basic-template") ?>">Basic Email</a></li>
                        <li><a href="<?= url("email-templates/email-header") ?>">Basic With Header</a></li>
                        <li><a href="<?= url("email-templates/template-email") ?>">Ecomerce Template</a></li>
                        <li><a href="<?= url("email-templates/template-email-2") ?>">Email Template 2</a></li>
                        <li><a href="<?= url("email-templates/ecommerce-templates") ?>">Ecommerce Email</a></li>
                        <li><a href="<?= url("email-templates/email-order-success") ?>">Order Success</a></li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </li>
          <li class="sidebar-main-title">
            <div>
              <h6>Miscellaneous</h6>
            </div>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-gallery"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-gallery"></use>
              </svg><span>Gallery</span></a>
            <ul class="sidebar-submenu">
              <li><a href="<?= url("Gallery/gallery") ?>">Gallery Grid</a></li>
              <li><a href="<?= url("Gallery/description-gallery") ?>">Gallery Grid Desc</a></li>
              <li><a href="<?= url("Gallery/masonry-gallery") ?>">Masonry Gallery</a></li>
              <li><a href="<?= url("Gallery/description-masonry-gallery") ?>">Masonry with Desc</a></li>
              <li><a href="<?= url("Gallery/gallery-hover") ?>">Hover Effects</a></li>
            </ul>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-blog"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-blog"></use>
              </svg><span>Blog</span></a>
            <ul class="sidebar-submenu">
              <li><a href="<?= url("blog/blog-details") ?>">Blog Details</a></li>
              <li><a href="<?= url("blog/single-blog") ?>">Blog Single</a></li>
              <li><a href="<?= url("blog/add-post") ?>">Add Post</a></li>
            </ul>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="<?= url("faq") ?>">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-faq"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-faq"></use>
              </svg><span>FAQ</span></a></li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-job-search"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-job-search"></use>
              </svg><span>Job Search</span></a>
            <ul class="sidebar-submenu">
              <li><a href="<?= url("job-search/job-cards-view") ?>">Cards view</a></li>
              <li><a href="<?= url("job-search/job-list-view") ?>">List View</a></li>
              <li><a href="<?= url("job-search/job-details") ?>">Job Details</a></li>
              <li><a href="<?= url("job-search/job-apply") ?>">Apply</a></li>
            </ul>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-learning"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-learning"></use>
              </svg><span>Learning</span></a>
            <ul class="sidebar-submenu">
              <li><a href="<?= url("learning/learning-list-view") ?>">Learning List</a></li>
              <li><a href="<?= url("learning/learning-detailed") ?>">Detailed Course</a></li>
            </ul>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-maps"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-maps"></use>
              </svg><span>Maps</span></a>
            <ul class="sidebar-submenu">
              <li><a href="<?= url("map/map-js") ?>">Maps JS</a></li>
              <li><a href="<?= url("map/vector-map") ?>">Vector Maps</a></li>
            </ul>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title" href="#">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-editors"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-editors"></use>
              </svg><span>Editors</span></a>
            <ul class="sidebar-submenu">
              <li><a href="<?= url("editors/summernote") ?>">Summer Note</a></li>
              <li><a href="<?= url("editors/ckeditor") ?>">CK editor</a></li>
              <li><a href="<?= url("editors/simple-MDE") ?>">MDE editor</a></li>
              <li><a href="<?= url("editors/ace-code-editor") ?>">ACE code editor </a></li>
            </ul>
          </li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="<?= url("knowledgebase") ?>">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-knowledgebase"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-knowledgebase"></use>
              </svg><span>Knowledgebase</span></a></li>
          <li class="sidebar-list"><i class="fa fa-thumb-tack"></i><a class="sidebar-link sidebar-title link-nav" href="<?= url("support-ticket") ?>">
              <svg class="stroke-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-support-tickets"></use>
              </svg>
              <svg class="fill-icon">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#fill-support-tickets"></use>
              </svg><span>Support Ticket</span></a></li>
        </ul>
      </div>
      <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
    </nav>
  </div>
</div>