<?php session_start(); ?>
<!doctype html>
<html lang="en" dir="ltr">
<?php 
	$title = "Debug - A2D";
    require './partials/_head.php';
    $command = '';
    if(isset($_GET["command"])){
        $command = $_GET["command"];
    }

    if($command == "destroy_session"){
        session_destroy();
    }
?>

<body class="">
    <div class="page">
        <div class="page-main">
            <?php require './partials/_nav.php'; ?>
            <div class="my-3 my-md-5">
                <div class="container">
                    <div class="page-header">
                        <h1 class="page-title">
                            Debug
                        </h1>
                    </div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Session</h3>
                            </div>
                            <div class="card-body">
                                <?php echo '<pre>';
                                var_dump($_SESSION);
                                echo '</pre>'; ?>
                            </div> <!-- card-body -->
                        </div> <!-- card -->
                    </div> <!-- col-12 -->



                </div> <!-- row -->
            </div> <!-- container -->
        </div> <!-- my-3 my-md-5 -->
    </div> <!-- page-main -->
    <?php require './partials/_footer.php' ?>
    </div> <!-- page -->

    <script>
    </script>
</body>

</html>
