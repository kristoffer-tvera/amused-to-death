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
                            Add/Update raid
                        </h1>
                    </div>

                    <div class="col-12">
                        <?php 
                        include_once './api/db.php';
                        include_once './api/db_helper.php';
                        $id = "";
                        $name = "";
                        $gold = 0;
                        $created = date("d-m-Y H:i:s");
                        $updated = date("d-m-Y H:i:s");

                        if(isset($_GET['id'])){
                            $id = htmlspecialchars($_GET["id"]);
                            $raid = GetRaid($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_raids, $id);
                            if(!empty($raid)){
                                $id = $raid["id"];
                                $name = $raid["name"];
                                $gold = $raid["gold"];
                                $created = $raid["added_date"];
                                $updated = $raid["change_date"];
                            }
                        }
                        ?>
                        <form action="/boosting/api/AddOrUpdateRaid.php" method="post" class="card"
                            id="AddOrUpdateRaid">
                            <div class="card-header">
                                <h3 class="card-title">Raid</h3>
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
                                            <label class="form-label">Name</label>
                                            <input type="text" class="form-control" name="name" placeholder="Name.."
                                                value="<?php echo $name ?>">
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">Gold</label>
                                            <input type="number" class="form-control" name="gold" placeholder="Gold.."
                                                value="<?php echo $gold ?>">
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
                                    <input type="hidden" name="return" value="/boosting/raid/" />
                                    <button type="submit" class="btn btn-primary ml-auto">Save</button>
                                </div>
                            </div>
                        </form>

                    </div> <!-- col-12 -->

                    <?php if(!empty($raid)): ?>

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Players</h3>
                            </div>
                            <div class="card-body">
                                <div class="row d-none d-lg-flex py-2 text-muted">
                                    <div class="col-1 col-lg-1">Id</div>
                                    <div class="col-6 col-lg-3">Name</div>
                                    <div class="col-5 col-lg-3">Class</div>
                                    <div class="col-6 col-lg-2">Bosses</div>
                                    <div class="col-6 col-lg-1">Paid</div>
                                    <div class="col-6 col-lg-1"></div>
                                    <div class="col-6 col-lg-1"></div>
                                </div>
                                <?php 
                                        include_once './api/db.php';
                                        include_once './api/db_helper.php';

                                        $attendees = GetAttendanceForRaid($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_attendance, $dbtable_characters, $id);

                                        if(!empty($attendees)){
                                        foreach($attendees as $attendee):
                                        ?>
                                <form class="row border-top py-2 update-attendance"
                                    action="/boosting/api/UpdateAttendance.php">
                                    <div class="col-1 col-lg-1 my-2 my-lg-0">
                                        <span class="text-muted"><?php echo $attendee["id"]?></span>
                                        <input type="hidden" name="character"
                                            value="<?php echo $attendee["characterId"]?>" />
                                        <input type="hidden" name="raid" value="<?php echo $attendee["raidId"]?>" />
                                    </div>
                                    <div class="col-6 col-lg-3 my-2 my-lg-0">
                                        <a href="/boosting/character/?id=<?php echo $attendee["characterId"]?>"
                                            class="text-inherit"><?php echo $attendee["name"]?></a>
                                    </div>
                                    <div class="col-5 col-lg-3 my-2 my-lg-0">
                                        <?php echo ClassFromId($attendee["class"]) ?>

                                    </div>
                                    <div class="col-6 col-lg-2 my-2 my-lg-0">
                                        <label class="mb-0 w-100">
                                            <input type="number" name="bosses" class="w-100"
                                                value="<?php echo $attendee["bosses"]?>" />
                                        </label>
                                    </div>
                                    <div class="col-6 col-lg-1 my-2 my-lg-0">
                                        <input type="checkbox" name="paid" value="1"
                                            <?php echo $attendee["paid"] == 0 ? "" : "checked=\"checked\""?>
                                            style="height: 22px; width: 22px;">
                                        <span class="d-inline-block d-lg-none align-top"> (Paid) </span>
                                    </div>
                                    <div class="col-6 col-lg-1 my-2 my-lg-0">
                                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                    </div>
                                    <div class="col-6 col-lg-1 my-2 my-lg-0">
                                        <a href="/boosting/api/DeleteAttendanceForCharacter.php?characterId=<?php echo $attendee["characterId"]?>&raidId=<?php echo $id ?>&return=/boosting/raid.php&returnId=<?php echo $id ?>"
                                            class="btn btn-danger btn-sm">Remove</a>
                                    </div>
                                </form>

                                <?php endforeach;} ?>

                            </div> <!-- table-body -->
                        </div> <!-- card -->
                    </div> <!-- col-12 -->

                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Add players</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <form action="/boosting/api/AddAttendance.php" method="post" id="AddAttendance">
                                            <div class="form-group">
                                                <label class="form-label">Main</label>
                                                <select name="character" id="select-character"
                                                    class="form-control custom-select">
                                                    <?php
                                                $characters = GetCharacters($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters);
                                                foreach($characters as $character):
                                                    $alreadyInTheRaid = false;
                                                    foreach($attendees as $attendee){
                                                        if($attendee["id"] == $character["id"]){
                                                            $alreadyInTheRaid = true;
                                                        }
                                                    }

                                                    if($alreadyInTheRaid) continue;
                                                ?>
                                                    <option value="<?php echo $character["id"] ?>">
                                                        <?php echo $character["name"] ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <input type="hidden" name="raid" value="<?php echo $id ?>" />
                                                <input type="hidden" name="bosses" value="0" />
                                                <input type="hidden" name="return" value="/boosting/raid.php" />
                                            </div>
                                            <div class="form-group">
                                                <input class="btn btn-primary" type="submit" value="Add">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- card -->
                    </div> <!-- col-12 -->
                    <?php endif; ?>
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

                $('#select-character').selectize({});
            });
        });

        require(['toastr'], function (toastr) {
            toastr.options.progressBar = true;
            toastr.options.positionClass = "toast-bottom-center";

            var UpdateAttendance = document.querySelectorAll('form.update-attendance');
            if (UpdateAttendance && UpdateAttendance.length > 0) {
                for (let i = 0; i < UpdateAttendance.length; i++) {
                    let currentForm = UpdateAttendance[i];
                    currentForm.addEventListener("submit", function (evt) {
                        evt.preventDefault();

                        let formData = new FormData(currentForm);
                        let xhr = new XMLHttpRequest();
                        xhr.open("POST", currentForm.getAttribute('action'));
                        xhr.onreadystatechange = function () {
                            if (this.readyState == 4) {
                                if (this.status == 200) {
                                    toastr.success('Attendance was saved', 'A2D Boosting')
                                } else {
                                    toastr.error('Attendance was NOT saved', 'A2D Boosting')
                                }
                            }
                        };
                        xhr.send(formData)
                    }, true);
                }
            }

        });

    </script>
</body>

</html>
