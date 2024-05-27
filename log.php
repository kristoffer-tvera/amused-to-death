<?php require './partials/_session_start.php'; ?>
<!doctype html>
<html lang="en" dir="ltr">
<?php
$title = "Log - A2D";
require './partials/_head.php';
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
                                Log
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Logevents</h3>
                    </div>
                    <table class="table card-table table-vcenter text-nowrap datatable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Time</th>
                                <th>Command</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include_once './api/secrets.php';
                            include_once './api/helper.php';
                            $log = GetLog($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_log);

                            foreach ($log as $event) :
                            ?>
                                <tr>
                                    <td>
                                        <span class="text-muted"> <?php echo $event['user']; ?> </span>
                                    </td>
                                    <td>
                                        <span class="text-muted"> <?php echo $event['date']; ?> </span>
                                    </td>
                                    <td>
                                        <?php echo $event['query']; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div> <!-- card -->

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
                [1, "desc"]
            ]
        });
    </script>
</body>

</html>