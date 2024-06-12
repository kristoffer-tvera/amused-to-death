<?php require './partials/_session_start.php'; ?>
<!doctype html>
<html lang="en" dir="ltr">
<?php
$title = "Application - A2D";
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
            <div class="container-xl container-fix">
                <!-- Page title -->
                <div class="page-header">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <h2 class="page-title">
                                Review application
                            </h2>
                        </div>
                    </div>
                </div>

                <?php
                include_once './api/secrets.php';
                include_once './api/helper.php';

                $id = $_GET["id"];
                $id = htmlspecialchars(strip_tags($id));

                $app = GetApp($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_app, $id);

                $auth = '';
                if (isset($_GET["auth"]) && !empty($_GET["auth"])) {
                    $auth = strtolower(htmlspecialchars($_GET["auth"]));
                }

                $verified = $auth == $app["auth"];

                ?>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Only the author has access to change/modify.</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($verified) : ?>
                            <form action="/api/ProcessApplication.php" method="POST" class="p-4">
                                <h1 class="mb-6"> We have ALREADY recieved your application. You can make changes to it here if you want (bookmark this page). </h1>
                                <input type="hidden" name="return" value="/apply/">
                                <input type="hidden" name="pepe" value="true">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($app["id"]); ?>">
                                <input type="hidden" name="auth" value="<?php echo htmlspecialchars($auth); ?>">

                                <div class="form-group">
                                    <label class="form-label">Character-name</label>
                                    <input type="text" class="form-control" name="name" placeholder="" value="<?php echo htmlspecialchars($app["name"]); ?>" required="required">
                                </div>
                                <hr />

                                <div class="form-group">
                                    <label class="form-label">Server</label>
                                    <input list="realms" type="text" class="form-control" name="server" placeholder="Realm (lowercase, shorthand, eg stormscale, defias-brotherhood)" value="<?php echo htmlspecialchars($app["server"]); ?>" required="required">
                                    <?php include('./partials/_realmlist.php') ?>
                                </div>
                                <hr />

                                <div class="form-group">
                                    <label class="form-label">Discord-tag (important so we can reach you!) </label>
                                    <input type="text" class="form-control" name="btag" placeholder="example#1234" value="<?php echo htmlspecialchars($app["btag"]); ?>" required="required">
                                </div>
                                <hr />

                                <div class="form-group">
                                    <label class="form-label">Primary spec</label>
                                    <input type="text" class="form-control" name="spec" placeholder="" value="<?php echo htmlspecialchars($app["spec"]); ?>" required="required">
                                </div>
                                <hr />

                                <div class="form-group">
                                    <label class="form-label">Raid UI screenshot</label>
                                    <input type="text" class="form-control" name="ui" placeholder="" value="<?php echo htmlspecialchars($app["ui"]); ?>" required="required">
                                </div>
                                <hr />

                                <div class="form-group">
                                    <label class="form-label">What made you consider applying to Amused to Death?</label>
                                    <textarea rows="6" class="form-control" name="reason" placeholder="" required="required"> <?php echo htmlspecialchars($app["reason"]); ?> </textarea>
                                </div>
                                <hr />

                                <div class="form-group">
                                    <label class="form-label">Tell us about your last guild. Why are you choosing us over
                                        them?
                                        Do you have any references?</label>
                                    <textarea rows="6" class="form-control" name="history" placeholder="" required="required"> <?php echo htmlspecialchars($app["history"]); ?> </textarea>
                                </div>
                                <hr />

                                <div class="form-group">
                                    <label class="form-label">What secondary specs or alternative characters do you feel you
                                        play at a similar level to your main? Do you have sufficient gear to be raid viable
                                        with
                                        these specs/characters? What recent experience do you have playing them?</label>
                                    <textarea rows="6" class="form-control" name="alts" placeholder="" required="required"> <?php echo htmlspecialchars($app["alts"]); ?> </textarea>
                                </div>
                                <hr />

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary ml-auto">Update</button>
                                </div>
                            </form>
                        <?php else : ?>
                            <div class="mb-4">
                                <h2>Character name</h2>
                                <p style="white-space: pre-wrap;"><?php echo htmlspecialchars($app["name"]); ?></p>
                            </div>
                            <div class="mb-4">
                                <h2>Server</h2>
                                <p style="white-space: pre-wrap;"><?php echo htmlspecialchars($app["server"]); ?></p>
                            </div>
                            <hr>
                            <div class="mb-4">
                                <h2><a href="https://www.warcraftlogs.com/character/eu/<?php echo htmlspecialchars($app["server"]); ?>/<?php echo htmlspecialchars($app["name"]); ?>" class="text-reset" style="text-decoration: underline;" target="_blank" rel="noopener noreferrer">Warcraftlogs</a></h2>
                            </div>
                            <div class="mb-4">
                                <h2><a href="https://raider.io/characters/eu/<?php echo htmlspecialchars($app["server"]); ?>/<?php echo htmlspecialchars($app["name"]); ?>" class="text-reset" style="text-decoration: underline;" target="_blank" rel="noopener noreferrer">Raider.io</a></h2>
                            </div>
                            <div class="mb-4">
                                <h2><a href="https://worldofwarcraft.com/en-gb/character/eu/<?php echo htmlspecialchars($app["server"]); ?>/<?php echo htmlspecialchars($app["name"]); ?>" class="text-reset" style="text-decoration: underline;" target="_blank" rel="noopener noreferrer">Armory</a></h2>
                            </div>
                            <div class="mb-4">
                                <h2><a href="https://www.wowprogress.com/character/eu/<?php echo htmlspecialchars($app["server"]); ?>/<?php echo htmlspecialchars($app["name"]); ?>" class="text-reset" style="text-decoration: underline;" target="_blank" rel="noopener noreferrer">WoWProgress</a></h2>
                            </div>
                            <hr>
                            <div class="mb-4">
                                <h2>Discord-tag</h2>
                                <p style="white-space: pre-wrap;"><?php echo htmlspecialchars($app["btag"]); ?></p>
                            </div>
                            <div class="mb-4">
                                <h2>Main-spec</h2>
                                <p style="white-space: pre-wrap;"><?php echo htmlspecialchars($app["spec"]); ?></p>
                            </div>
                            <hr>
                            <div class="mb-4">
                                <h2>UI</h2>
                                <?php $ui = htmlspecialchars($app["ui"]);
                                if (str_ends_with($ui, ".png") || str_ends_with($ui, ".jpg")) :  ?>
                                    <img src="<?php echo $ui; ?>" alt="ui-screenshot" class="w-100" />
                                <?php else : ?>
                                    <p style="white-space: pre-wrap;"><?php echo $ui; ?></p>
                                <?php endif; ?>
                            </div>
                            <hr>
                            <div class="mb-4">
                                <h2>Reason for applying</h2>
                                <p style="white-space: pre-wrap;"><?php echo htmlspecialchars($app["reason"]); ?></p>
                            </div>
                            <hr>
                            <div class="mb-4">
                                <h2>Guild history</h2>
                                <p style="white-space: pre-wrap;"><?php echo htmlspecialchars($app["history"]); ?></p>
                            </div>
                            <hr>
                            <div class="mb-4">
                                <h2>Alts</h2>
                                <p style="white-space: pre-wrap;"><?php echo htmlspecialchars($app["alts"]); ?></p>
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
        }, 3000);
    </script>
</body>

</html>