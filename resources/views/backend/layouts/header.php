<!-- Page Header Start-->
<div class="page-header">
  <div class="header-wrapper row m-0">
    <form class="form-inline search-full col" action="#" method="get">
      <div class="form-group w-100">
        <div class="Typeahead Typeahead--twitterUsers">
          <div class="u-posRelative">
            <input class="demo-input Typeahead-input form-control-plaintext w-100" type="text" placeholder="Search Cuba .." name="q" title="" autofocus>
            <div class="spinner-border Typeahead-spinner" role="status"><span class="sr-only">Loading...</span></div><i class="close-search" data-feather="x"></i>
          </div>
          <div class="Typeahead-menu"></div>
        </div>
      </div>
    </form>
    <div class="header-logo-wrapper col-auto p-0">
      <div class="logo-wrapper"><a href="<?= url("/") ?>"><img class="img-fluid" src="<?= url() ?>/assets/images/logo/logo.png" alt=""></a></div>
      <div class="toggle-sidebar"><i class="status_toggle middle sidebar-toggle" data-feather="align-center"></i></div>
    </div>
    <div class="left-header col-xxl-5 col-xl-6 col-lg-5 col-md-4 col-sm-3 p-0">
      <div class="notification-slider">
        <div class="d-flex h-100"> <img src="<?= url() ?>/assets/images/giftools.gif" alt="gif">
          <h6 class="mb-0 f-w-400"><span class="font-primary">Don't Miss Out! </span><span class="f-light">Out new update has been release.</span></h6><i class="icon-arrow-top-right f-light"></i>
        </div>
        <div class="d-flex h-100"><img src="<?= url() ?>/assets/images/giftools.gif" alt="gif">
          <h6 class="mb-0 f-w-400"><span class="f-light">Something you love is now on sale! </span></h6><a class="ms-1" href="https://1.envato.market/3GVzd" target="_blank">Buy now !</a>
        </div>
      </div>
    </div>
    <div class="nav-right col-xxl-7 col-xl-6 col-md-7 col-8 pull-right right-header p-0 ms-auto">
      <ul class="nav-menus">
        
        <li class="language-nav">
          <?php component('language-selector') ?>
        </li>
        <li> <span class="header-search">
            <svg>
              <use href="<?= url() ?>/assets/svg/icon-sprite.svg#search"></use>
            </svg></span></li>
        <li class="onhover-dropdown">
          <svg>
            <use href="<?= url() ?>/assets/svg/icon-sprite.svg#star"></use>
          </svg>
          <div class="onhover-show-div bookmark-flip">
            <div class="flip-card">
              <div class="flip-card-inner">
                <div class="front">
                  <h6 class="f-18 mb-0 dropdown-title">Bookmark</h6>
                  <ul class="bookmark-dropdown">
                    <li>
                      <div class="row">
                        <div class="col-4 text-center">
                          <div class="bookmark-content">
                            <div class="bookmark-icon"><i data-feather="file-text"></i></div><span>Forms</span>
                          </div>
                        </div>
                        <div class="col-4 text-center">
                          <div class="bookmark-content">
                            <div class="bookmark-icon"><i data-feather="user"></i></div><span>Profile</span>
                          </div>
                        </div>
                        <div class="col-4 text-center">
                          <div class="bookmark-content">
                            <div class="bookmark-icon"><i data-feather="server"></i></div><span>Tables</span>
                          </div>
                        </div>
                      </div>
                    </li>
                    <li class="text-center"><a class="flip-btn f-w-700" id="flip-btn" href="javascript:void(0)">Add New Bookmark</a></li>
                  </ul>
                </div>
                <div class="back">
                  <ul>
                    <li>
                      <div class="bookmark-dropdown flip-back-content">
                        <input type="text" placeholder="search...">
                      </div>
                    </li>
                    <li><a class="f-w-700 d-block flip-back" id="flip-back" href="javascript:void(0)">Back</a></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </li>
        <li>
          <div class="mode">
            <svg>
              <use href="<?= url() ?>/assets/svg/icon-sprite.svg#moon"></use>
            </svg>
          </div>
        </li>
        <li class="cart-nav onhover-dropdown">
          <div class="cart-box">
            <svg>
              <use href="<?= url() ?>/assets/svg/icon-sprite.svg#stroke-ecommerce"></use>
            </svg><span class="badge rounded-pill badge-success">2</span>
          </div>
          <div class="cart-dropdown onhover-show-div">
            <h6 class="f-18 mb-0 dropdown-title">Cart</h6>
            <ul>
              <li>
                <div class="media"><img class="img-fluid b-r-5 me-3 img-60" src="<?= url() ?>/assets/images/other-images/cart-img.jpg" alt="">
                  <div class="media-body"><span>Furniture Chair for Home</span>
                    <div class="qty-box">
                      <div class="input-group"><span class="input-group-prepend">
                          <button class="btn quantity-left-minus" type="button" data-type="minus" data-field="">-</button></span>
                        <input class="form-control input-number" type="text" name="quantity" value="1"><span class="input-group-prepend">
                          <button class="btn quantity-right-plus" type="button" data-type="plus" data-field="">+</button></span>
                      </div>
                    </div>
                    <h6 class="font-primary">$500</h6>
                  </div>
                  <div class="close-circle"><a class="bg-danger" href="#"><i data-feather="x"></i></a></div>
                </div>
              </li>
              <li>
                <div class="media"><img class="img-fluid b-r-5 me-3 img-60" src="<?= url() ?>/assets/images/other-images/cart-img.jpg" alt="">
                  <div class="media-body"><span>Furniture Chair for Home</span>
                    <div class="qty-box">
                      <div class="input-group"><span class="input-group-prepend">
                          <button class="btn quantity-left-minus" type="button" data-type="minus" data-field="">-</button></span>
                        <input class="form-control input-number" type="text" name="quantity" value="1"><span class="input-group-prepend">
                          <button class="btn quantity-right-plus" type="button" data-type="plus" data-field="">+</button></span>
                      </div>
                    </div>
                    <h6 class="font-primary">$500.00</h6>
                  </div>
                  <div class="close-circle"><a class="bg-danger" href="#"><i data-feather="x"></i></a></div>
                </div>
              </li>
              <li class="total">
                <h6 class="mb-0">Order Total : <span class="f-right">$1000.00</span></h6>
              </li>
              <li class="text-center"><a class="d-block mb-3 view-cart f-w-700" href="<?= url("ecommerce/cart") ?>">Go to your cart</a><a class="btn btn-primary view-checkout" href="<?= url("ecommerce/checkout") ?>">Checkout</a></li>
            </ul>
          </div>
        </li>
        <li class="onhover-dropdown">
          <div class="notification-box">
            <svg>
              <use href="<?= url() ?>/assets/svg/icon-sprite.svg#notification"></use>
            </svg><span class="badge rounded-pill badge-secondary">4 </span>
          </div>
          <div class="onhover-show-div notification-dropdown">
            <h6 class="f-18 mb-0 dropdown-title">Notitications </h6>
            <ul>
              <li class="b-l-primary border-4">
                <p>Delivery processing <span class="font-danger">10 min.</span></p>
              </li>
              <li class="b-l-success border-4">
                <p>Order Complete<span class="font-success">1 hr</span></p>
              </li>
              <li class="b-l-secondary border-4">
                <p>Tickets Generated<span class="font-secondary">3 hr</span></p>
              </li>
              <li class="b-l-warning border-4">
                <p>Delivery Complete<span class="font-warning">6 hr</span></p>
              </li>
              <li><a class="f-w-700" href="#">Check all</a></li>
            </ul>
          </div>
        </li>
        <li class="onhover-dropdown">
          <?php
          // Get current user's wallet balance
          $userId = $_SESSION['user']['id'] ?? null;
          $balance = 0;
          $currency = 'XOF';
          if ($userId) {
            $walletService = new \Modules\Wallet\Services\WalletService();
            $balance = $walletService->getBalance($userId);
            $wallet = $walletService->getWallet($userId);
            if ($wallet) {
              $currency = $wallet->currency;
            }
          }
          ?>
          <div class="wallet-badge">
            <a href="<?= url('/admin/wallet') ?>" class="d-flex align-items-center text-decoration-none">
              <svg class="me-2" width="20" height="20">
                <use href="<?= url() ?>/assets/svg/icon-sprite.svg#dollar-sign"></use>
              </svg>
              <span class="badge badge-light-success" style="font-size: 14px; padding: 8px 12px;">
                <?= number_format($balance, 0) ?> <?= $currency ?>
              </span>
            </a>
          </div>
        </li>
        <li class="profile-nav onhover-dropdown pe-0 py-0">
          <div class="media profile-media">
            <?php
            $currentUser = \Modules\Users\Models\User::find($_SESSION['user_id'] ?? 0);
            $avatarUrl = $currentUser && $currentUser->avatar ? url($currentUser->avatar) : url('assets/images/dashboard/profile.png');
            $displayName = $currentUser ? ($currentUser->first_name ? $currentUser->first_name . ' ' . ($currentUser->last_name ?? '') : $currentUser->username) : 'User';
            ?>
            <img class="b-r-10" src="<?= $avatarUrl ?>" alt="Profile" style="width: 40px; height: 40px; object-fit: cover;">
            <div class="media-body">
              <span><?= htmlspecialchars($displayName) ?></span>
              <p class="mb-0 font-roboto">Admin <i class="middle fa fa-angle-down"></i></p>
            </div>
          </div>
          <ul class="profile-dropdown onhover-show-div">
            <li><a href="<?= url('/admin/profile') ?>"><i data-feather="user"></i><span>My Profile</span></a></li>
            <li><a href="<?= url('/admin/profile/change-password') ?>"><i data-feather="lock"></i><span>Change Password</span></a></li>
            <li><a href="<?= url('/admin/api-keys') ?>"><i data-feather="key"></i><span>API Keys</span></a></li>
            <li><a href="<?= url('/logout') ?>"><i data-feather="log-out"></i><span>Logout</span></a></li>
          </ul>
        </li>
      </ul>
    </div>
    <script class="result-template" type="text/x-handlebars-template">
      <div class="ProfileCard u-cf">
            <div class="ProfileCard-avatar"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-airplay m-0"><path d="M5 17H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-1"></path><polygon points="12 15 17 21 7 21 12 15"></polygon></svg></div>
            <div class="ProfileCard-details">
            <div class="ProfileCard-realName">lath</div>
            </div>
            </div>
          </script>
    <script class="empty-template" type="text/x-handlebars-template"><div class="EmptyMessage">Your search turned up 0 results. This most likely means the backend is down, yikes!</div></script>
  </div>
</div>
<!-- Page Header Ends -->