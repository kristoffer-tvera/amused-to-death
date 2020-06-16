<?php session_start(); ?>
<!doctype html>
<html lang="en" dir="ltr">
<?php 
	$title = "Battle.net - A2D";
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
                            Blizzard API Authentication status
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">API Status</h3>
                    </div>
                    <div class="card-body">

                    <p>Battle.net tokens are only used for updating <span class="bold">ilvl</span> of your already imported characters (for now)!

                    <?php 
                    $token = '';
                    $timeLeft = 0;
                    if(isset($_SESSION['token'])) {
                        $token = $_SESSION['token'];
                        $timeLeft = ($_SESSION['token_create'] + $_SESSION['token_expire'])-time();
                    }

                    if(empty($token)):
                    ?>
                    <p>You do NOT have an active access-token.</p>
                    <p> <a href="/boosting/api/BattleNetToken/?return=/boosting/bnet/">Click here</a> to get a new one.</p> 
                    <?php elseif($timeLeft > 0):?>
                    <p> Your token has <?php echo $timeLeft ?> seconds left </p>
                    <?php else: ?>
                        <p> Your token has expired. </p>
                        <p> <a href="/boosting/api/BattleNetToken/?return=/boosting/bnet/">Click here</a> to get a new one.</p> 
                    <?php endif;?>
                    
                    </div> <!-- card-body -->
                </div> <!-- card -->
            </div> <!-- container -->
        <?php require './partials/_footer.php' ?>
        </div> <!-- content -->
    </div> <!-- page -->
    <?php require './partials/_scripts.php' ?>
</body>

</html>
