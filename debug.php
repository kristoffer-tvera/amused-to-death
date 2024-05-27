<?php require './partials/_session_start.php'; ?>
<!doctype html>
<html lang="en" dir="ltr">
<?php
$title = "Debug - A2D";
require './partials/_head.php';
$command = '';
if (isset($_GET["command"])) {
    $command = $_GET["command"];
}

if ($command == "destroy_session") {
    session_destroy();
}
?>

<body class="antialiased" data-bs-theme="dark">
    <div class="page">
        <?php require './partials/_nav.php'; ?>
        <div class="content">
            <div class="container-xl">

                <!-- Page title -->
                <div class="page-header">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <h2 class="page-title">
                                Debug
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Session</h3>
                    </div>
                    <div class="card-body">
                        <p>Session Keys:</p>
                        <ul>
                            <?php if (isset($_SESSION)) : foreach ($_SESSION as $key => $value) : ?>
                                    <li><?php echo $key ?></li>
                            <?php endforeach;
                            endif; ?>
                        </ul>
                        <a href="?command=destroy_session" class="btn btn-danger"> Destroy session <a>

                                <hr />
                    </div> <!-- card-body -->
                </div> <!-- card -->

            </div> <!-- container -->
            <?php require './partials/_footer.php' ?>
        </div> <!-- content -->
    </div> <!-- page -->
    <?php require './partials/_scripts.php' ?>
</body>

</html>