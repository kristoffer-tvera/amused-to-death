<?php require './partials/_session_start.php'; ?>
<!doctype html>
<html lang="en" dir="ltr">
<?php 
	$title = "Characters - A2D";
	require './partials/_head.php';
?>

<body class="antialiased theme-dark">
    <div class="page">
        <?php require './partials/_nav.php'; ?>
        <div class="content">
            <div class="container-xl">

                <!-- Page title -->
                <div class="page-header">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <h2 class="page-title">
                                Characters
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <a href="/character/" class="card-header text-reset justify-content-center">
                        <h3 class="card-title">New character</h3>
                    </a>
                </div>


                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Characters</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table card-table table-vcenter text-nowrap datatable">
                            <thead>
                                <tr>
                                    <th class="w-1">Id.</th>
                                    <th>Name</th>
                                    <th>Main</th>
                                    <th>Class</th>
                                    <th>ilvl</th>
                                    <th>Created</th>
                                    <th>Updated</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    include_once './api/secrets.php';
                                    include_once './api/helper.php';
                                    $characters = GetCharacters($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters);

                                    foreach($characters as $character):
                                    ?>
                                <tr>
                                    <td>
                                        <span class="text-muted"> <?php echo $character['id']; ?> </span>
                                    </td>
                                    <td>
                                        <a href="/character/?id=<?php echo $character['id']; ?>"
                                            class="text-reset text-decoration-underline"><?php echo $character['name']; ?></a>
                                    </td>
                                    <td>
                                        <?php 
                                                if(!empty($character['main'])){
                                                    foreach($characters as $playerCharacter){
                                                        if($playerCharacter['id'] == $character['main']){
                                                            ?>
                                        <a href="/character/?id=<?php echo $playerCharacter['id']; ?>"
                                            class="text-reset text-decoration-underline"><?php echo $playerCharacter['name']; ?></a>
                                        <?php
                                                        }
                                                    }
                                                }
                                            ?>
                                    </td>
                                    <td>
                                        <?php echo ClassFromId($character['class']); ?>
                                    </td>
                                    <td>
                                        <?php echo $character['ilvl']; ?>
                                    </td>
                                    <td>
                                        <?php echo $character['added_date']; ?>
                                    </td>
                                    <td>
                                        <?php echo $character['change_date']; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div> <!-- table-responsive -->
                </div> <!-- card -->

                <?php 
                    $players = array(); 
                    foreach($characters as $character){
                        if(empty($character["main"])) $players[] = $character;
                    }
                ?>

                <div class="page-header" data-players-header>
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <h2 class="page-title">
                                Players (<?php echo sizeof($players) ?>)
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <?php
                        foreach($players as $player):
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="row row-sm align-items-center">
                                    <div class="col-auto pr-0">
                                        <span class="avatar avatar-md"
                                            style="background-image: url(/assets/images/classes/<?php echo $player['class']; ?>.png)"></span>
                                    </div>
                                    <div class="col d-flex">
                                        <h3 class="mb-0 mr-1">
                                            <a href="/character/?id=<?php echo $player['id']; ?>"
                                                title="Ilvl: <?php echo $player['ilvl']; ?>"
                                                class="text-reset text-decoration-underline"><?php echo $player["name"] ?></a>
                                            <?php if($player['role_tank'] == 1): ?>
                                            <span class="avatar avatar-sm"
                                                style="background-image: url(/assets/images/roles/role_tank.png)">
                                            </span>
                                            <?php endif; ?>
                                            <?php if($player['role_heal'] == 1): ?>
                                            <span class="avatar avatar-sm"
                                                style="background-image: url(/assets/images/roles/role_heal.png)">
                                            </span>
                                            <?php endif; ?>
                                            <?php if($player['role_dps'] == 1): ?>
                                            <span class="avatar avatar-sm"
                                                style="background-image: url(/assets/images/roles/role_dps.png)">
                                            </span>
                                            <?php endif; ?>
                                        </h3>

                                        <a href="https://worldofwarcraft.com/en-gb/character/eu/<?php echo $player["realm"] ?>/<?php echo $player["name"] ?>"
                                            target="_blank" rel="noopener" class="px-1 border-left"><span
                                                class="avatar avatar-sm"
                                                style="background-image: url(/assets/images/external-sources/warcraft.png)"></a>
                                        <a href="https://www.warcraftlogs.com/character/eu/<?php echo $player["realm"] ?>/<?php echo $player["name"] ?>"
                                            target="_blank" rel="noopener" class="px-1"><span class="avatar avatar-sm"
                                                style="background-image: url(/assets/images/external-sources/warcraftlogs.png)"></a>
                                        <a href="https://raider.io/characters/eu/<?php echo $player["realm"] ?>/<?php echo $player["name"] ?>"
                                            target="_blank" rel="noopener" class="px-1"><span class="avatar avatar-sm"
                                                style="background-image: url(/assets/images/external-sources/raiderio.png)"></a>
                                    </div>
                                </div>
                                <hr class="my-3" />
                                <div class="row align-items-center mt-4">
                                    <?php 
                                        $alts = array();
                                        foreach($characters as $character){
                                            if($character["main"] == $player['id']) $alts[] = $character;
                                        }

                                        foreach($alts as $alt):
                                        ?>
                                    <div class="ml-2 row w-100">
                                        <div class="col-auto px-0">
                                            <span class="avatar avatar-sm"
                                                style="background-image: url(/assets/images/classes/<?php echo $alt['class']; ?>.png)"></span>
                                        </div>
                                        <div class="col-auto d-flex px-1">
                                            <h3 class="mb-0 mr-1">
                                                <a href="/character/?id=<?php echo $alt['id']; ?>"
                                                    title="Ilvl: <?php echo $alt['ilvl']; ?>"
                                                    class="text-reset text-decoration-underline"><?php echo $alt["name"] ?></a>
                                                <?php if($alt['role_tank'] == 1): ?>
                                                <span class="avatar avatar-sm"
                                                    style="background-image: url(/assets/images/roles/role_tank.png)">
                                                </span>
                                                <?php endif; ?>
                                                <?php if($alt['role_heal'] == 1): ?>
                                                <span class="avatar avatar-sm"
                                                    style="background-image: url(/assets/images/roles/role_heal.png)">
                                                </span>
                                                <?php endif; ?>
                                                <?php if($alt['role_dps'] == 1): ?>
                                                <span class="avatar avatar-sm"
                                                    style="background-image: url(/assets/images/roles/role_dps.png)">
                                                </span>
                                                <?php endif; ?>
                                            </h3>
                                            <a href="https://worldofwarcraft.com/en-gb/character/eu/<?php echo $alt["realm"] ?>/<?php echo $alt["name"] ?>"
                                                target="_blank" rel="noopener" class="px-1 border-left"><span
                                                    class="avatar avatar-sm"
                                                    style="background-image: url(/assets/images/external-sources/warcraft.png)"></a>
                                            <a href="https://www.warcraftlogs.com/character/eu/<?php echo $alt["realm"] ?>/<?php echo $alt["name"] ?>"
                                                target="_blank" rel="noopener" class="px-1"><span
                                                    class="avatar avatar-sm"
                                                    style="background-image: url(/assets/images/external-sources/warcraftlogs.png)"></a>
                                            <a href="https://raider.io/characters/eu/<?php echo $alt["realm"] ?>/<?php echo $alt["name"] ?>"
                                                target="_blank" rel="noopener" class="px-1"><span
                                                    class="avatar avatar-sm"
                                                    style="background-image: url(/assets/images/external-sources/raiderio.png)"></a>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div><!-- card-body -->
                        </div><!-- card -->
                    </div> <!-- col-md-6 col-lg-4 -->
                    <?php endforeach; ?>
                </div><!-- row -->

            </div><!-- container-xl -->
            <?php require './partials/_footer.php' ?>
        </div><!-- content -->
    </div><!-- page -->
    <?php require './partials/_scripts.php' ?>
    <script>
        $('.datatable').DataTable({
            "lengthChange": false,
            "searching": false,
            "pageLength": 20,
            "dom": "<'table-responsive' tr>" +
                "<'card-footer d-flex align-items-center'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            "order": [
                [6, "desc"]
            ]
        });

    </script>
</body>

</html>
