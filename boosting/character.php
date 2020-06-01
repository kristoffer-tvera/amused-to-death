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
                            Dashboard
                        </h1>
                    </div>

                    <div class="row row-cards row-deck">

                        <div class="col-12">
                            <?php 
                            include_once './api/db.php';
                            include_once './api/db_helper.php';
                            $id = 0;
                            $ilvl = 0;
                            $name = "";
                            $class = 0;
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
                                    $class = $character["class"];
                                    $main = $character["main"];
                                    $created = $character["added_date"];
                                    $updated = $character["change_date"];
                                } else {
                                    $id = "";
                                }
                            }
                            ?>
                            <form action="/boosting/api/AddOrUpdateCharacter.php" method="post" class="card"
                                id="characterform">
                                <div class="card-header">
                                    <h3 class="card-title">Form elements</h3>
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
                                                <label class="form-label">Class</label>
                                                <select name="class" id="select-class"
                                                    class="form-control custom-select">
                                                    <option value="0"
                                                        <?php if($class == 0) echo "selected=\"selected\""?>>Druid
                                                    </option>
                                                    <option value="1"
                                                        <?php if($class == 1) echo "selected=\"selected\""?>>Paladin
                                                    </option>
                                                    <option value="2"
                                                        <?php if($class == 2) echo "selected=\"selected\""?>>Warrior
                                                    </option>
                                                    <option value="3"
                                                        <?php if($class == 3) echo "selected=\"selected\""?>>Demon
                                                        Hunter</option>
                                                    <option value="4"
                                                        <?php if($class == 4) echo "selected=\"selected\""?>>Hunter
                                                    </option>
                                                    <option value="5"
                                                        <?php if($class == 5) echo "selected=\"selected\""?>>Mage
                                                    </option>
                                                    <option value="6"
                                                        <?php if($class == 6) echo "selected=\"selected\""?>>Rogue
                                                    </option>
                                                    <option value="7"
                                                        <?php if($class == 7) echo "selected=\"selected\""?>>Death
                                                        Knight</option>
                                                    <option value="8"
                                                        <?php if($class == 8) echo "selected=\"selected\""?>>Priest
                                                    </option>
                                                    <option value="9"
                                                        <?php if($class == 9) echo "selected=\"selected\""?>>Warlock
                                                    </option>
                                                    <option value="10"
                                                        <?php if($class == 10) echo "selected=\"selected\""?>>Shaman
                                                    </option>
                                                    <option value="11"
                                                        <?php if($class == 11) echo "selected=\"selected\""?>>Monk
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">Main</label>
                                                <select name="main" id="select-main" class="form-control custom-select">
                                                    <option value="-1"
                                                        <?php if($main == 0) echo "selected=\"selected\"" ?>>None
                                                    </option>
                                                    <?php
                                                $characters = GetCharacters($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters);
                                                foreach($characters as $character):
                                                ?>
                                                    <option value="<?php echo $character["id"] ?>"
                                                        <?php if($main == $character["id"]) echo "selected=\"selected\"" ?>>
                                                        <?php echo $character["name"] ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">Created</label>
                                                <div class="form-control-plaintext"><?php echo $created ?></div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Updated</label>
                                                <div class="form-control-plaintext"><?php echo $updated ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-right">
                                    <div class="d-flex">
                                        <button type="submit" class="btn btn-primary ml-auto">Save</button>
                                    </div>
                                </div>
                            </form>

                        </div> <!-- col-12 -->

                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Raids</h3>
                                </div>
                                <div class="table-responsive">
                                    <table class="table card-table table-vcenter text-nowrap">
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
                                                        class="text-inherit"><?php echo $raid["name"] ?></a></td>
                                                <td>
                                                    <?php echo $raid["bosses"] ?>
                                                </td>
                                                <td>
                                                    <div class="custom-controls-stacked">
                                                        <label class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input"
                                                                <?php echo $raid["paid"] == 0 ? "" : "checked=\"checked\""?>
                                                                disabled readonly>
                                                            <span class="custom-control-label"></span>
                                                        </label>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach;} ?>

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
    <script>
        require(['jquery', 'selectize'], function ($, selectize) {
            $(document).ready(function () {
                $('#input-tags').selectize({
                    delimiter: ',',
                    persist: false,
                    create: function (input) {
                        return {
                            value: input,
                            text: input
                        }
                    }
                });

                $('#select-class').selectize({});
                $('#select-main').selectize({});
            });
        });

        require(['toastr'], function (toastr) {
            toastr.options.progressBar = true;
            toastr.options.positionClass = "toast-bottom-center";
            let form = document.querySelector('#characterform');
            if (form) {
                form.addEventListener("submit", function (evt) {
                    evt.preventDefault();

                    let formData = new FormData(form);
                    let xhr = new XMLHttpRequest();
                    xhr.open("POST", form.getAttribute('action'));
                    xhr.onreadystatechange = function () {
                        if (this.readyState == 4) {
                            if (this.status == 200) {
                                toastr.success('Character was saved', 'A2D Boosting')
                            } else {
                                toastr.error('Character was NOT saved', 'A2D Boosting')
                            }
                        }
                    };
                    xhr.send(formData)
                }, true);
            }
        });

    </script>
</body>

</html>
