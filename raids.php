<?php
include_once './partials/_session_start.php';
include_once './partials/_auth_required.php';
?>
<!doctype html>
<html lang="en" dir="ltr">
<?php
$title = "Raids - A2D";
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
                                Raids
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <a href="/raid/" class="card-header text-reset justify-content-center">
                        <h3 class="card-title">New raid</h3>
                    </a>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Raids</h3>
                    </div>
                    <table class="table card-table table-vcenter text-nowrap datatable" data-order='[[ 3, "desc" ]]' data-page-length='25'>
                        <thead>
                            <tr>
                                <th class="w-1">Id.</th>
                                <th>Name</th>
                                <th>Gold</th>
                                <th>Created</th>
                                <th>Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include_once './api/secrets.php';
                            include_once './api/helper.php';
                            $raids = GetRaids($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_raids);
                            foreach ($raids as $raid) :
                            ?>
                                <tr>
                                    <td><span class="text-muted"><?php echo $raid['id']; ?></span></td>
                                    <td><a href="/raid/?id=<?php echo $raid['id']; ?>" class="text-reset"><?php echo $raid['name']; ?></a></td>
                                    <td>
                                        <?php echo $raid['gold']; ?>
                                    </td>
                                    <td>
                                        <?php echo $raid['added_date']; ?>
                                    </td>
                                    <td>
                                        <?php echo $raid['change_date']; ?>
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