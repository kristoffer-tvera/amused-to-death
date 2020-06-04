<?php 
    $nav[] = array('name' => 'Home', 'icon' => 'fe fe-home', 'href' => '');
    $nav[] = array('name' => 'Raids', 'icon' => 'fe fe-database', 'href' => 'raids/');
    $nav[] = array('name' => 'Characters', 'icon' => 'fe fe-users', 'href' => 'characters/');
    $nav[] = array('name' => 'Battle.net', 'icon' => 'fe fe-users', 'href' => 'bnet/');
    $including_filename = pathinfo(debug_backtrace()[0]['file'])['basename'];
?>
<div class="header collapse d-lg-flex p-0" id="headerMenuCollapse">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg order-lg-first">
                <ul class="nav nav-tabs border-0 flex-column flex-lg-row">
                    <?php foreach($nav as $navElement): ?>
                    <li class="nav-item">
                        <a href="/boosting/<?php echo $navElement["href"]; ?>" class="nav-link <?php if($including_filename == $navElement["href"]) echo "active" ?>"><i class="<?php echo $navElement["icon"]; ?>"></i><?php echo $navElement["name"]; ?></a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>