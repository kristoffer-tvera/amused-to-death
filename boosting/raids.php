<!doctype html>
<html lang="en" dir="ltr">

<?php 
	$title = "Boosting - A2D";
	require './partials/_head.php';
?>

<body class="">
    <div class="page">
        <div class="page-main">
            <?php require './partials/_nav.php'; ?>
            <div class="my-3 my-md-5">
                <div class="container">
                    <div class="page-header">
                        <h1 class="page-title">
                            Raids
                        </h1>
                    </div>

                    <div class="row row-cards row-deck">

                        <div class="col-12">
                            <div class="card">
                                <a href="/boosting/raid/" class="card-header text-default justify-content-center">
                                    <h3 class="card-title">New raid</h3>
                                </a>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Raids</h3>
                                </div>
                                <div class="table-responsive">
                                    <table class="table card-table table-vcenter text-nowrap">
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
                                            include_once './api/db.php';
                                            include_once './api/db_helper.php';
                                            $raids = GetRaids($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_raids);
                                            foreach($raids as $raid):
                                             ?>
                                            <tr>
                                                <td><span class="text-muted"><?php echo $raid['id']; ?></span></td>
                                                <td><a href="/boosting/raid/?id=<?php echo $raid['id']; ?>"
                                                        class="text-inherit"><?php echo $raid['name']; ?></a></td>
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
                                </div> <!-- table-responsive -->
                            </div> <!-- card -->
                        </div> <!-- col-12 -->

                    </div> <!-- row -->
                </div> <!-- container -->
            </div> <!-- my-3 my-md-5 -->
        </div> <!-- page-main -->
        <?php require './partials/_footer.php' ?>
    </div> <!-- page -->
</body>

</html>
