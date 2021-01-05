<?php require './partials/_session_start.php'; ?>
<!doctype html>
<html lang="en" dir="ltr">
<?php 
	$title = "Boosting - A2D";
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
                                Dashboard
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Welcome</h3>
                    </div>
                    <div class="card-body">
                        <p> If you have a character in the guild, log in through discord (!mudkip in the raider-only
                            channel).</p>
                        <p> If you dont, then please feel free to <a href="/apply/" class="text-decoration-underline link-warning">apply to become a raider / part of
                                the guild</a>.</p>
                    </div>
                    <div class="card-footer">
                    </div>
                </div>

                <?php if(isset($_SESSION['auth'])):?>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">My Characters</h3>
                    </div>
                    <table class="table card-table table-vcenter text-nowrap datatable">
                        <thead>
                            <tr>
                                <th class="w-1">Id.</th>
                                <th>Name</th>
                                <th>Main</th>
                                <th>Class</th>
                                <th>ilvl</th>
                                <th>Server</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                    include_once './api/secrets.php';
                                    include_once './api/helper.php';
                                    $characters = GetMyCharacters($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters, $_SESSION['auth']);
                                    foreach($characters as $character):
                                    ?>
                            <tr>
                                <td>
                                    <span class="text-muted"> <?php echo $character['id']; ?> </span>
                                </td>
                                <td>
                                    <a href="/character/?id=<?php echo $character['id']; ?>"
                                        class="text-reset"><?php echo $character['name']; ?></a>
                                </td>
                                <td>
                                    <?php 
                                            if(!empty($character['main'])){
                                                foreach($characters as $mainCharacter){
                                                    if($mainCharacter['id'] == $character['main']){
                                                        ?>
                                    <a href="/character/?id=<?php echo $mainCharacter['id']; ?>"
                                        class="text-reset"><?php echo $mainCharacter['name']; ?></a>
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
                                    <?php echo $character['realm']; ?>
                                </td>
                                <td>
                                    <?php echo $character['change_date']; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                </div><!-- card -->
                <?php endif;?>

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
        });

    </script>
</body>

</html>
