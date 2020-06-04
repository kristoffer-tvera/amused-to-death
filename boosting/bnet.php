<?php session_start(); ?>
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
                            Blizzard API Authentication status
                        </h1>
                    </div>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">API Status</h3>
                            </div>
                            <div class="card-body">
                            <?php 
                            $token = '';
                            if(isset($_SESSION['token'])) $token = $_SESSION['token'];

                            if(!empty($token)):
                            ?>
                            <p> You have a active token! </p>
                            <?php else: ?>
                            <p>You do NOT have an active access-token.</p>
                            <p> <a href="/boosting/api/BattleNetToken/?return=/boosting/bnet/">Click here</a> to get a new one.</p> 
                            <?php endif;?>

                            <?php 
                            $timeLeft = ($_SESSION['token_create'] + $_SESSION['token_expire'])-time();
                            if($timeLeft > 0): ?>
                                <p> Your token has <?php echo $timeLeft ?> seconds left </p>
                            <?php else: ?>
                                <p> Your token has expired. </p>
                                <p> <a href="/boosting/api/BattleNetToken/?return=/boosting/bnet/">Click here</a> to get a new one.</p> 
                            <?php endif;?>
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
