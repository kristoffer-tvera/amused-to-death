<?php 
    $nav[] = array('name' => 'Home', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z"></path><polyline points="5 12 3 12 12 3 21 12 19 12"></polyline><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"></path><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"></path></svg>', 'href' => '');
    $nav[] = array('name' => 'Raids', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z"></path><ellipse cx="12" cy="6" rx="8" ry="3"></ellipse><path d="M4 6v6a8 3 0 0 0 16 0v-6"></path><path d="M4 12v6a8 3 0 0 0 16 0v-6"></path></svg>', 'href' => 'raids/');
    $nav[] = array('name' => 'Characters', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z"></path><circle cx="12" cy="7" r="4"></circle><path d="M5.5 21v-2a4 4 0 0 1 4 -4h5a4 4 0 0 1 4 4v2"></path></svg>', 'href' => 'characters/');
    if(isset($_SESSION['admin']) && $_SESSION['admin']) {
        $nav[] = array('name' => 'Battle.net', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z"></path><circle cx="12" cy="12" r="9"></circle><line x1="3.6" y1="9" x2="20.4" y2="9"></line><line x1="3.6" y1="15" x2="20.4" y2="15"></line><path d="M11.5 3a17 17 0 0 0 0 18"></path><path d="M12.5 3a17 17 0 0 1 0 18"></path></svg>', 'href' => 'bnet/');
        $nav[] = array('name' => 'Debug', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z"></path><line x1="12" y1="12" x2="12" y2="12.01"></line><path d="M12 2a4 10 0 0 0 -4 10a4 10 0 0 0 4 10a4 10 0 0 0 4 -10a4 10 0 0 0 -4 -10" transform="rotate(45 12 12)"></path><path d="M12 2a4 10 0 0 0 -4 10a4 10 0 0 0 4 10a4 10 0 0 0 4 -10a4 10 0 0 0 -4 -10" transform="rotate(-45 12 12)"></path></svg>', 'href' => 'debug/');
        $nav[] = array('name' => 'Log', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="icon icon-md" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z"></path><path d="M18.9 7a8 8 0 0 1 1.1 5v1a6 6 0 0 0 .8 3"></path><path d="M8 11a4 4 0 0 1 8 0v1a10 10 0 0 0 2 6"></path><path d="M12 11v2a14 14 0 0 0 2.5 8"></path><path d="M8 15a18 18 0 0 0 1.8 6"></path><path d="M4.9 19a22 22 0 0 1 -.9 -7v-1a8 8 0 0 1 12 -6.95"></path></svg>', 'href' => 'log/');
    }
    $including_filename = pathinfo(debug_backtrace()[0]['file'])['basename'];
?>

<header class="navbar navbar-expand-md navbar-dark">
    <div class="container-xl">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <a href="/" class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pr-0 pr-md-3">
            Amused to Death
        </a>
        <?php if(isset($_SESSION['auth']) && !empty($_SESSION['auth'])):?>
        <div class="navbar-nav flex-row order-md-last">
            <div class="nav-item">
                <span class="nav-link-title">
                    Hi, <?php echo $_SESSION['auth'] ?>
                </span>
            </div>
        </div>
        <?php endif;?>

        <div class="collapse navbar-collapse" id="navbar-menu">
            <div class="d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center">
                <ul class="navbar-nav">

                    <?php foreach($nav as $navElement): ?>

                    <li class="nav-item <?php if(strpos($_SERVER['REQUEST_URI'], $navElement["href"]) !== false  && strlen($_SERVER['REQUEST_URI']) >= strlen($navElement["href"])) echo "active" ?>">
                        <a class="nav-link" href="/boosting/<?php echo $navElement["href"]; ?>">
                            <span class="nav-link-icon d-md-none d-lg-inline-block">
                                <?php echo $navElement["icon"]; ?>
                            </span>

                            <span class="nav-link-title">
                                </i><?php echo $navElement["name"]; ?>
                            </span>
                        </a>
                    </li>
                    <?php endforeach; ?>

                </ul>
            </div>
        </div>
    </div>
</header>
