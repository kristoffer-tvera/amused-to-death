<?php require './partials/_session_start.php'; ?>
<!doctype html>
<html lang="en" dir="ltr">
<?php
$title = "Apply - A2D";
require './partials/_head.php';
?>

<body class="antialiased" data-bs-theme="dark">
    <style>
        input:valid,
        textarea:valid,
        form:valid {
            border: 2px solid green;
        }

        input:invalid,
        textarea:invalid,
        form:invalid {
            border: 2px solid red;
        }
    </style>
    <div class="page">
        <?php require './partials/_nav.php'; ?>
        <div class="content">
            <div class="container-xl">
                <!-- Page title -->
                <div class="page-header">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <h2 class="page-title">
                                Apply here
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Anything you type here WILL be stored and made available to the guild.
                            Dont disclose anything that you feel is private.</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!isset($_SESSION["apply"])) : ?>
                            <form action="/api/ProcessApplication.php" method="POST" class="p-4">

                                <input type="hidden" name="return" value="/apply/">
                                <input type="hidden" name="pepe" value="true">

                                <div class="form-group">
                                    <label class="form-label">Character-name</label>
                                    <input type="text" class="form-control" name="name" placeholder="" required="required">
                                </div>
                                <hr />

                                <div class="form-group">
                                    <label class="form-label">Server</label>
                                    <input list="realms" type="text" class="form-control" name="server" placeholder="Realm (lowercase, shorthand, eg stormscale, defias-brotherhood)" required="required">
                                    <?php include('./partials/_realmlist.php') ?>
                                </div>
                                <hr />

                                <div class="form-group">
                                    <label class="form-label">Discord-tag (important so we can reach you!) </label>
                                    <input type="text" class="form-control" name="btag" placeholder="example#1234" required="required">
                                </div>
                                <hr />

                                <div class="form-group">
                                    <label class="form-label">Primary spec</label>
                                    <input type="text" class="form-control" name="spec" placeholder="" required="required">
                                </div>
                                <hr />

                                <div class="form-group">
                                    <label class="form-label">Raid UI screenshot</label>
                                    <input type="text" class="form-control" name="ui" placeholder="" required="required">
                                </div>
                                <hr />

                                <div class="form-group">
                                    <label class="form-label">Introduce yourself (name/age), and tell us what made you consider applying to Amused to Death?</label>
                                    <textarea rows="6" class="form-control" name="reason" placeholder="" required="required"></textarea>
                                </div>
                                <hr />

                                <div class="form-group">
                                    <label class="form-label">Tell us about your last guild. Why are you choosing us over
                                        them?
                                        Do you have any references?</label>
                                    <textarea rows="6" class="form-control" name="history" placeholder="" required="required"></textarea>
                                </div>
                                <hr />

                                <div class="form-group">
                                    <label class="form-label">What secondary specs or alternative characters do you feel you
                                        play at a similar level to your main? Do you have sufficient gear to be raid viable
                                        with
                                        these specs/characters? What recent experience do you have playing them?</label>
                                    <textarea rows="6" class="form-control" name="alts" placeholder="" required="required"></textarea>
                                </div>
                                <hr />

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary ml-auto">Send</button>
                                </div>
                            </form>
                        <?php else : ?>
                            <div class="form-group">
                                <p class="form-label">We have received an application from you. If you wish to change or update it, please follow <a href="<?php echo $_SESSION["apply"] ?>" class="text-reset" style="text-decoration: underline">this link</a>. The discord
                                    can be found in the navbar.</p>

                            </div>
                        <?php endif; ?>

                    </div> <!-- card-body -->
                </div> <!-- card -->

            </div> <!-- container -->
            <?php require './partials/_footer.php' ?>
        </div> <!-- content -->
    </div> <!-- page -->
    <?php require './partials/_scripts.php' ?>
    <script>
        setTimeout(function() {
            let pepe = document.querySelector('input[name="pepe"]');
            if (pepe) {
                pepe.value = "meme";
            }
        }, 6000);
    </script>
</body>

</html>