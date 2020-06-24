<?php session_start(); ?>
<!doctype html>
<html lang="en" dir="ltr">
<?php 
	$title = "Character - A2D";
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
                                Dashboard
                            </h2>
                        </div>
                    </div>
                </div>

                <?php 
                include_once './api/db.php';
                include_once './api/db_helper.php';
                $id = 0;
                $ilvl = 0;
                $name = "";
                $realm = "draenor";
                $class = 0;
                $role_tank = 0;
                $role_heal = 0;
                $role_dps = 0;
                $main = 0;
                $created = date("d-m-Y H:i:s");
                $updated = date("d-m-Y H:i:s");

                if(isset($_GET['id'])){
                    $id = htmlspecialchars($_GET["id"]);
                    $character = GetCharacter($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters, $id);
                    if(!empty($character)){
                        $id = $character["id"];
                        $ilvl = $character["ilvl"];
                        $name = $character["name"];
                        $realm = $character["realm"];
                        $class = $character["class"];
                        $role_tank = $character["role_tank"];
                        $role_heal = $character["role_heal"];
                        $role_dps = $character["role_dps"];
                        $main = $character["main"];
                        $created = $character["added_date"];
                        $updated = $character["change_date"];
                    } else {
                        $id = "";
                    }
                }
                ?>
                <form action="/boosting/api/AddOrUpdateCharacter.php" method="post" class="card" id="characterform">
                    <div class="card-header">
                        <h3 class="card-title">Character</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">Id</label>
                                    <div class="form-control-plaintext"><?php echo $id ?></div>
                                    <input type="hidden" name="id" value="<?php echo $id ?>" />
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Ilvl</label>
                                    <div class="form-control-plaintext"><?php echo $ilvl ?></div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="name" placeholder="Name.."
                                        required="required" value="<?php echo $name ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Realm</label>
                                    <input list="realms" type="text" class="form-control" name="realm"
                                        placeholder="Realm (lowercase, shorthand, eg draenor, defias-brotherhood)"
                                        required="required" value="<?php echo $realm ?>">
                                    <?php include('./partials/_realmlist.php') ?>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Class</label>
                                    <select name="class" id="select-class" class="form-control custom-select">
                                        <option value="0" <?php if($class == 0) echo "selected=\"selected\""?>>Druid
                                        </option>
                                        <option value="1" <?php if($class == 1) echo "selected=\"selected\""?>>Paladin
                                        </option>
                                        <option value="2" <?php if($class == 2) echo "selected=\"selected\""?>>Warrior
                                        </option>
                                        <option value="3" <?php if($class == 3) echo "selected=\"selected\""?>>Demon
                                            Hunter</option>
                                        <option value="4" <?php if($class == 4) echo "selected=\"selected\""?>>Hunter
                                        </option>
                                        <option value="5" <?php if($class == 5) echo "selected=\"selected\""?>>Mage
                                        </option>
                                        <option value="6" <?php if($class == 6) echo "selected=\"selected\""?>>Rogue
                                        </option>
                                        <option value="7" <?php if($class == 7) echo "selected=\"selected\""?>>Death
                                            Knight</option>
                                        <option value="8" <?php if($class == 8) echo "selected=\"selected\""?>>Priest
                                        </option>
                                        <option value="9" <?php if($class == 9) echo "selected=\"selected\""?>>Warlock
                                        </option>
                                        <option value="10" <?php if($class == 10) echo "selected=\"selected\""?>>Shaman
                                        </option>
                                        <option value="11" <?php if($class == 11) echo "selected=\"selected\""?>>Monk
                                        </option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Main</label>
                                    <select name="main" id="select-main" class="form-control custom-select">
                                        <option value="-1" <?php if($main == 0) echo "selected=\"selected\"" ?>>None
                                        </option>
                                        <?php
                                                $characters = GetCharacters($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters);
                                                usort($characters, function($a, $b) {return strcmp($a["name"], $b["name"]);});
                                                foreach($characters as $character):
                                                ?>
                                        <option value="<?php echo $character["id"] ?>"
                                            <?php if($main == $character["id"]) echo "selected=\"selected\"" ?>>
                                            <?php echo $character["name"] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="role_tank" name="role_tank"
                                            value="1" <?php if($role_tank == 1) echo "checked" ?>>
                                        <label class="form-check-label" for="role_tank">Tank</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="role_heal" name="role_heal"
                                            value="1" <?php if($role_heal == 1) echo "checked" ?>>
                                        <label class="form-check-label" for="role_heal">Healer</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="role_dps" name="role_dps"
                                            value="1" <?php if($role_dps == 1) echo "checked" ?>>
                                        <label class="form-check-label" for="role_dps">Dps</label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Created</label>
                                    <div class="form-control-plaintext"><?php echo $created ?></div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Updated</label>
                                    <div class="form-control-plaintext"><?php echo $updated ?></div>
                                </div>

                                <?php 
                                                $token = '';
                                                if(isset($_SESSION['token'])) $token = $_SESSION['token'];
                                            ?>

                                <?php 
                                            if(!empty($id) && ($_SESSION['token_create'] + $_SESSION['token_expire'])-time() > 0):
                                            ?>
                                <div class="form-group">
                                    <div class="form-control-plaintext"><a class="btn btn-success"
                                            href="/boosting/api/BattleNetUpdateCharacter/?id=<?php echo $id ?>&return=/boosting/character/?id=<?php echo $id ?>">Update
                                            ilvl</a></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <div class="d-flex">
                            <input type="hidden" name="return" value="/boosting/character/" />
                            <button type="submit" class="btn btn-primary ml-auto">Save</button>
                        </div>
                    </div>
                </form><!-- card -->

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Raids</h3>
                    </div>
                    <table class="table card-table table-vcenter text-nowrap datatable">
                        <thead>
                            <tr>
                                <th class="w-1">Id</th>
                                <th>Name</th>
                                <th>Bosses</th>
                                <th>Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                include_once './api/db.php';
                                include_once './api/db_helper.php';

                                $raids = GetAttendanceForCharacter($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_attendance, $dbtable_raids, $id);
                                
                                if(!empty($raids)){
                                foreach($raids as $raid):
                                ?>
                            <tr>
                                <td>
                                    <span class="text-muted"><?php echo $raid["raidId"] ?></span></td>
                                <td>
                                    <a href="/boosting/raid/?id=<?php echo $raid["raidId"] ?>"
                                        class="text-reset text-decoration-underline"><?php echo $raid["name"] ?></a>
                                </td>
                                <td>
                                    <?php echo $raid["bosses"] ?>
                                </td>
                                <td>
                                    <div class="custom-controls-stacked">
                                        <label class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input"
                                                <?php echo $raid["paid"] == 0 ? "" : "checked=\"checked\""?> disabled
                                                readonly>
                                            <span class="custom-control-label"></span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach;} ?>

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
        });

    </script>
</body>

</html>
