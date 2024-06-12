<?php require './partials/_session_start.php'; ?>
<!doctype html>
<html lang="en" dir="ltr">
<?php
$title = "Applications - A2D";
require './partials/_head.php';
?>

<body class="antialiased" data-bs-theme="dark">
    <div class="page">
        <?php require './partials/_nav.php'; ?>
        <div class="content">
            <div class="container-xl container-fix">

                <!-- Page title -->
                <div class="page-header">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <h2 class="page-title">
                                Applications
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Showing latest first</h3>
                    </div>
                    <table class="table card-table table-vcenter text-nowrap datatable" data-order='[[ 3, "desc" ]]' data-page-length='25'>
                        <thead>
                            <tr>
                                <th class="w-1">Id</th>
                                <th>Name</th>
                                <th>Server</th>
                                <th>Spec</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include_once './api/secrets.php';
                            include_once './api/helper.php';
                            $apps = GetApps($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_app);
                            foreach ($apps as $app) :
                            ?>
                                <tr>
                                    <td><span class="text-muted"><?php echo $app['id']; ?></span></td>
                                    <td><a href="/app/?id=<?php echo $app['id']; ?>" class="text-reset"><?php echo $app['name']; ?></a></td>
                                    <td>
                                        <?php echo $app['server']; ?>
                                    </td>
                                    <td>
                                        <?php echo $app['spec']; ?>
                                    </td>
                                    <td>
                                        <?php echo $app['change_date']; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div><!-- card -->

            </div><!-- container-xl -->
            <?php require './partials/_footer.php' ?>
        </div><!-- content -->
    </div><!-- page -->
    <?php require './partials/_scripts.php' ?>
    <script>
        let table = new DataTable('.datatable');
    </script>
</body>

</html>