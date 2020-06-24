<?php session_start(); ?>
<!doctype html>
<html lang="en" dir="ltr">
<?php 
	$title = "Raid - A2D";
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
                                Add/Update raid
                            </h2>
                        </div>
                    </div>
                </div>

                <form action="/boosting/api/AddOrUpdateRaid.php" method="post" class="card" id="AddOrUpdateRaid">
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

                <?php if(!empty($raid)): ?>
                <?php $attendees = GetAttendanceForRaid($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_attendance, $dbtable_characters, $id); ?>

                <?php
                $players = array();
                foreach($attendees as $attendee){
                    if(!in_array($attendee["id"], $players) && empty($attendee["main"])){
                        $players[] = $attendee["id"]; // This is a main
                    } 
                    
                    if(!in_array($attendee["main"], $players) && !empty($attendee["main"])) {
                        $players[] = $attendee["main"]; //This puts mains in, if they are not there already.
                    }
                }
                ?>
                <div class="page-header" data-players-header>
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <h2 class="page-title">
                                Players (<?php echo sizeof($players) ?>)
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="row" data-players>
                    <?php
                    for($i = 0; $i < sizeof($players); $i++):
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card" data-player-id="<?php echo $players[$i] ?>">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <?php 
                                    $characters = array();
                                    foreach($attendees as $attendee){
                                        if($attendee['id'] == $players[$i]){
                                            $characters[] = $attendee;
                                            continue;
                                        };

                                        if($attendee['main'] == $players[$i]){
                                            $characters[] = $attendee;
                                            continue;
                                        }; 
                                    }

                                    foreach($characters as $character):
                                    ?>
                                    <div class="ml-2 row w-100">
                                        <div class="col-auto px-0">
                                            <span class="avatar avatar-sm"
                                                style="background-image: url(/boosting/assets/images/classes/<?php echo $character['class']; ?>.png)"></span>
                                        </div>
                                        <div class="col-auto d-flex px-1">
                                            <h3 class="mb-0 mr-1">
                                                <a href="/boosting/character/?id=<?php echo $character['characterId']; ?>"
                                                    title="Ilvl: <?php echo $character['ilvl']; ?>"
                                                    class="text-reset text-decoration-underline"><?php echo $character["name"] ?></a>
                                                <?php if($alt['role_tank'] == 1): ?>
                                                <span class="avatar avatar-sm"
                                                    style="background-image: url(/boosting/assets/images/roles/role_tank.png)">
                                                </span>
                                                <?php endif; ?>
                                                <?php if($alt['role_heal'] == 1): ?>
                                                <span class="avatar avatar-sm"
                                                    style="background-image: url(/boosting/assets/images/roles/role_heal.png)">
                                                </span>
                                                <?php endif; ?>
                                                <?php if($alt['role_dps'] == 1): ?>
                                                <span class="avatar avatar-sm"
                                                    style="background-image: url(/boosting/assets/images/roles/role_dps.png)">
                                                </span>
                                                <?php endif; ?>
                                            </h3>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div><!-- card-body -->
                        </div><!-- card -->
                    </div> <!-- col-md-6 col-lg-4 -->
                    <?php endfor; ?>
                </div><!-- d-flex flex-wrap -->

                <div class="card" data-add>
                    <div class="card-header">
                        <h3 class="card-title">Add characters</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <form action="/boosting/api/AddAttendance.php" method="post" id="AddAttendance">
                                    <div class="form-group">
                                        <label class="form-label">Select character</label>
                                        <select name="character" id="select-character"
                                            class="form-control custom-select">
                                            <?php
                                        $characters = GetCharacters($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters);
                                        usort($characters, function($a, $b) {return strcmp($a["name"], $b["name"]);});
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

                <div class="card" data-characters>
                    <div class="card-header">
                        <h3 class="card-title">Characters</h3>
                    </div>
                    <div class="card-body">
                        <div class="row d-none d-lg-flex py-2 text-muted">
                            <div class="col-1 col-lg-1">Id</div>
                            <div class="col-6 col-lg-3">Name</div>
                            <div class="col-5 col-lg-3">Signup date</div>
                            <div class="col-6 col-lg-1">Cut</div>
                            <div class="col-6 col-lg-1">Bosses</div>
                            <div class="col-6 col-lg-1">Paid</div>
                            <div class="col-6 col-lg-1"></div>
                            <div class="col-6 col-lg-1"></div>
                        </div>
                        <?php 
                            if(!empty($attendees)){
                            foreach($attendees as $attendee):
                            ?>
                        <form class="row border-top py-2 update-attendance" action="/boosting/api/UpdateAttendance.php">
                            <div class="col-1 col-lg-1 my-2 my-lg-0">
                                <span class="text-muted"><?php echo $attendee["id"]?></span>
                                <input type="hidden" name="character" value="<?php echo $attendee["characterId"]?>" />
                                <input type="hidden" name="raid" value="<?php echo $attendee["raidId"]?>" />
                            </div>
                            <div class="col-6 col-lg-3 my-2 my-lg-0">
                                <a href="/boosting/character/?id=<?php echo $attendee["characterId"]?>"
                                    class="text-reset text-decoration-underline"><?php echo $attendee["name"]?></a>
                            </div>
                            <div class="col-5 col-lg-3 my-2 my-lg-0">
                                <?php echo $attendee["added_date"] ?>
                            </div>
                            <div class="col-6 col-lg-1 my-2 my-lg-0">
                                <label class="mb-0 w-100">
                                    <input type="number" disabled="disabled" class="w-100" data-cut>
                                </label>
                            </div>
                            <div class="col-6 col-lg-1 my-2 my-lg-0">
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

                <?php endif; ?>
            </div> <!-- container-xl -->

            <!-- Toast success -->
            <div aria-live="polite" aria-atomic="true" style="position: fixed; top: 0; right: 0; width: 320px;">
                <div class="toast success" role="alert" aria-live="assertive" aria-atomic="true" data-delay="6000">
                    <div class="toast-header">
                        <strong class="mr-auto">A2D</strong>
                        <small class="text-muted">just now</small>
                        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="toast-body bg-green">
                        Change saved
                    </div>
                </div><!-- toast -->
            </div>

            <!-- Toast error -->
            <div aria-live="polite" aria-atomic="true" style="position: fixed; top: 0; right: 0; width: 320px;">
                <div class="toast error" role="alert" aria-live="assertive" aria-atomic="true" data-delay="6000">
                    <div class="toast-header">
                        <strong class="mr-auto">A2D</strong>
                        <small class="text-muted">just now</small>
                        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="toast-body bg-red">
                        Error. Logged in?
                    </div>
                </div><!-- toast -->
            </div>

            <?php require './partials/_footer.php' ?>
        </div> <!-- content -->
    </div> <!-- page -->
    <?php require './partials/_scripts.php' ?>
    <script>
        var UpdateAttendance = document.querySelectorAll('form.update-attendance');
        if (UpdateAttendance && UpdateAttendance.length > 0) {
            for (let i = 0; i < UpdateAttendance.length; i++) {
                let currentForm = UpdateAttendance[i];
                currentForm.addEventListener("submit", function (evt) {
                    evt.preventDefault();
                    UpdateCut()
                    let formData = new FormData(currentForm);
                    let xhr = new XMLHttpRequest();
                    xhr.open("POST", currentForm.getAttribute('action'));
                    xhr.onreadystatechange = function () {
                        if (this.readyState == 4) {
                            if (this.status == 200) {
                                $('.toast.success').toast('show')
                            } else {
                                $('.toast.error').toast('show')
                            }
                        }
                    };
                    xhr.send(formData)
                }, true);
            }
        }

        let goldField = document.querySelector('input[name="gold"]');
        if (goldField) {
            goldField.addEventListener('input', UpdateCut);
        }

        function UpdateCut() {
            let gold = 0;
            if (goldField) {
                let value = goldField.value;
                gold = Number.parseInt(value);
            }
            if (gold == 0) return;
            let remainingGold = gold;

            let bossCount = 0;
            let attendance = document.querySelectorAll('form.update-attendance');
            if (!attendance) return;
            for (let i = 0; i < attendance.length; i++) {
                let currentBossCountField = attendance[i].querySelector('input[name="bosses"]');
                if (!currentBossCountField) continue;

                let currentBossCount = Number.parseInt(currentBossCountField.value);
                bossCount += currentBossCount;
            }

            let share = gold / bossCount;

            if (bossCount == 0) return;

            for (let i = 0; i < attendance.length; i++) {
                let currentBossCountField = attendance[i].querySelector('input[name="bosses"]');
                if (!currentBossCountField) continue;
                let currentBossCount = Number.parseInt(currentBossCountField.value);

                let currentCutField = attendance[i].querySelector('input[data-cut]');
                if (!currentCutField) continue;
                //let currentCut = Number.parseInt(currentCutField.value);

                let cut = share * currentBossCount;

                cut = Math.floor(cut / 5000) * 5000;

                currentCutField.value = cut;
                remainingGold -= cut;
            }

            console.log('GuildBank: ' + remainingGold);
        }
        UpdateCut();

        let playerCards = document.querySelectorAll('[data-players] div.card');
        if (playerCards && playerCards.length > 0) {
            // let colors = ['rgb(208, 255, 254)', 'rgb(255, 253, 219)', 'rgb(228, 255, 222)', 'rgb(255, 211, 253)', 'rgb(255, 231, 211)', 'rgb(255, 255, 255)'];
            let colors = ['rgb(45, 91, 107)', 'rgb(196, 122, 83)', 'rgb(143, 71, 49)', 'rgb(82, 73, 76)',
                'rgb(123, 125, 42)', 'rgb(53, 64, 82)'
            ];
            for (let i = 0; i < playerCards.length; i++) {
                playerCards[i].addEventListener('click', function (e) {
                    let card = e.currentTarget;
                    let currentColor = card.style.backgroundColor;
                    var colorIndex = (colors.indexOf(currentColor) + 1) % colors.length;
                    card.style.backgroundColor = colors[colorIndex];
                });
            }
        }

    </script>
</body>

</html>
