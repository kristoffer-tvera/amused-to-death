<?php session_start(); ?>
<!doctype html>
<html lang="en" dir="ltr">
<?php 
	$title = "Apply - A2D";
    require './partials/_head.php';
?>

<body class="antialiased theme-dark">
    <style>
        input:valid, textarea:valid, form:valid {
            border: 2px solid green;
        }

        input:invalid, textarea:invalid, form:invalid {
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
                        <h3 class="card-title">Anything you type here WILL be stored and made avilable to the guild. Dont disclose anything that you feel is private.</h3>
                    </div>
                    <div class="card-body">
                        <?php if(!isset($_SESSION["apply"])): ?>
                        <form action="" method="POST" class="p-4">

                            <input type="hidden" name="return" value="./apply/">
                    
                            <div class="form-group">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="name" placeholder="" required="required">
                            </div>
                            <hr />

                            <div class="form-group">
                                <label class="form-label">Battle-tag (important so we can reach you!) </label>
                                <input type="text" class="form-control" name="btag" placeholder="example#1234"
                                    required="required">
                            </div>
                            <hr />

                            <div class="form-group">
                                <label class="form-label">Primary spec</label>
                                <input type="text" class="form-control" name="spec" placeholder="" required="required">
                            </div>
                            <hr />

                            <div class="form-group">
                                <label class="form-label">Link to your Armory profile</label>
                                <input type="text" class="form-control" name="armory" placeholder="" required="required">
                            </div>
                            <hr />

                            <div class="form-group">
                                <label class="form-label">Link to your Warcraftlogs</label>
                                <input type="text" class="form-control" name="warcraftlogs" placeholder=""
                                    required="required">
                            </div>
                            <hr />

                            <div class="form-group">
                                <label class="form-label">Raid UI screenshot</label>
                                <input type="text" class="form-control" name="ui" placeholder="" required="required">
                            </div>
                            <hr />

                            <div class="form-group">
                                <label class="form-label">What addons do you use?</label>
                                <textarea rows="6" class="form-control" name="addons" placeholder="" required="required"></textarea>
                            </div>
                            <hr />

                            <div class="form-group">
                                <label class="form-label">What made you consider applying to Amused to Death?</label>
                                <textarea rows="6" class="form-control" name="reason" placeholder="" required="required"></textarea>
                            </div>
                            <hr />

                            <div class="form-group">
                                <label class="form-label">Tell us about your last guild. Why are you choosing us over them?
                                    Do you have any references?</label>
                                <textarea rows="6" class="form-control" name="history" placeholder="" required="required"></textarea>
                            </div>
                            <hr />

                            <div class="form-group">
                                <label class="form-label">What secondary specs or alternative characters do you feel you
                                    play at a similar level to your main? Do you have sufficient gear to be raid viable with
                                    these specs/characters? What recent experience do you have playing them?</label>
                                <textarea rows="6" class="form-control" name="alts" placeholder="" required="required"></textarea>
                            </div>
                            <hr />

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary ml-auto">Send</button>
                            </div>
                        </form>
                        <?php else: ?> 
                            <div class="form-group">
                                <p class="form-label">We have recieved an application from you. If you wish to change, revoke, or have this deleted, please contact anyone on our discord for help. The discord can be found in the navbar.</p>
                                    
                            </div>
                        <?php endif; ?> 

                    </div> <!-- card-body -->
                </div> <!-- card -->

                <div class="card">
                    <div class="card-body">
                        <pre>
                        <?php
                        $fields = [];
                        foreach($_POST as $key => $value){
                            if($key == "return") continue;
                        
                            $fields[] = [
                                "name" => $key,
                                "value" => htmlspecialchars($value),
                                "inline" => false
                            ];
                        }

                        echo var_dump($fields);
                        ?>
                        </pre>
                    </div>
                </div>

            </div> <!-- container -->
            <?php require './partials/_footer.php' ?>
        </div> <!-- content -->
    </div> <!-- page -->
    <?php require './partials/_scripts.php' ?>
    <script>
    </script>
</body>

</html>
