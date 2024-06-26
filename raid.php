<?php require './partials/_session_start.php'; ?>
<!doctype html>
<html lang="en" dir="ltr">
<?php
$title = "Raid - A2D";
require './partials/_head.php';
?>

<body class="antialiased" data-bs-theme="dark">
    <div class="page">
        <?php require './partials/_nav.php'; ?>
        <div class="content">
            <div class="container-xl container-fix">
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

                <form action="/api/AddOrUpdateRaid.php" method="post" class="card" id="AddOrUpdateRaid">
                    <?php
                    include_once './api/secrets.php';
                    include_once './api/helper.php';
                    $id = "";
                    $name = "";
                    $gold = 0;
                    $paid = 0;
                    $comment = "";
                    $created = date("d-m-Y H:i:s");
                    $updated = date("d-m-Y H:i:s");

                    if (isset($_GET['id'])) {
                        $id = htmlspecialchars($_GET["id"]);
                        $raid = GetRaid($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_raids, $id);
                        if (!empty($raid)) {
                            $id = $raid["id"];
                            $name = $raid["name"];
                            $gold = $raid["gold"];
                            $paid = $raid["paid"];
                            $comment = $raid["comment"];
                            $created = $raid["added_date"];
                            $updated = $raid["change_date"];
                        }
                    }
                    ?>
                    <div class="card-header">
                        <h3 class="card-title">Raid</h3>
                        <input type="hidden" name="id" value="<?php echo $id ?>" />
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label class="form-label">Name</label>
                                    <input type="text" class="form-control" name="name" placeholder="Name.." value="<?php echo $name ?>" required="required">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Gold</label>
                                    <input type="number" class="form-control" name="gold" placeholder="Gold.." value="<?php echo $gold ?>">
                                </div>

                                <div class="form-group my-3">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="paid" name="paid" value="1" <?php if ($paid == 1) echo "checked";  ?>>
                                        <label class="form-check-label" for="paid">Paid by community</label>
                                    </div>
                                </div>


                                <div class="form-group">
                                    <label class="form-label" for="comment">Comment</label>
                                    <textarea class="form-control" data-toggle="autosize" id="comment" name="comment" placeholder="Comment" rows="5"><?php echo $comment ?></textarea>
                                </div>

                                <hr />

                                <div class="form-group">
                                    <label class="form-label">Created</label>
                                    <div class="form-control-plaintext"><?php echo $created ?></div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Updated</label>
                                    <div class="form-control-plaintext"><?php echo $updated ?></div>
                                </div>
                                <?php if (isset($_SESSION['admin']) && $id > 0) : ?>

                                    <hr />
                                    <div class="form-group">
                                        <a class="btn btn-danger" href="/api/RemoveAttendeesWithNoBossesFromRaid/?return=/raid/&raidId=<?php echo $id ?>">
                                            Remove characters with zero bosses</a>
                                        <br />
                                        <br />
                                        <a class="btn btn-danger" href="/api/AddAllRaiders/?return=/raid/&raidId=<?php echo $id ?>">
                                            Add all raiders (ONLY WORKS FOR RAIDS WITH 0 SIGNUPS) </a>
                                    </div>

                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <?php if (isset($_SESSION['admin'])) : ?>
                            <div class="d-flex">
                                <input type="hidden" name="return" value="/raid/" />
                                <button type="submit" class="btn btn-primary ml-auto">Save</button>
                            </div>
                        <?php endif; ?>
                    </div>
                </form>

                <?php if (!empty($raid)) : ?>
                    <?php $attendees = GetAttendanceForRaid($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_attendance, $dbtable_characters, $id); ?>

                    <?php
                    $players = array();
                    foreach ($attendees as $attendee) {
                        if (!in_array($attendee["characterId"], $players) && empty($attendee["main"])) {
                            $players[] = $attendee["characterId"]; // This is a main
                        }

                        if (!in_array($attendee["main"], $players) && !empty($attendee["main"])) {
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

                    <div class="row row-deck row-cards" data-players>
                        <?php
                        for ($i = 0; $i < sizeof($players); $i++) :
                        ?>
                            <div class="col-md-6 col-lg-3">
                                <div class="card" data-player-id="<?php echo $players[$i] ?>" style="border-color: rgb(53, 64, 82)">
                                    <div class="card-body p-1">
                                        <div class="row align-items-center">
                                            <?php
                                            $characters = array();
                                            foreach ($attendees as $attendee) {
                                                if ($attendee['characterId'] == $players[$i]) {
                                                    $characters[] = $attendee;
                                                    continue;
                                                }

                                                if ($attendee['main'] == $players[$i]) {
                                                    $characters[] = $attendee;
                                                }
                                            }

                                            foreach ($characters as $character) :
                                            ?>
                                                <div class="row w-100">
                                                    <div class="col-auto">
                                                        <span class="avatar avatar-sm" style="background-image: url(/assets/images/classes/<?php echo $character['class']; ?>.png)"></span>
                                                    </div>
                                                    <div class="col-auto d-flex px-1">
                                                        <h3 class="mb-1">
                                                            <a href="/character/?id=<?php echo $character['characterId']; ?>" title="Ilvl: <?php echo $character['ilvl']; ?>" class="text-reset text-decoration-underline"><?php echo $character["name"] . ($character["vip"] == 1 ? '*' : '') ?></a>
                                                            <?php if ($character['role_tank'] == 1) : ?>
                                                                <span class="avatar avatar-sm" style="background-image: url(/assets/images/roles/role_tank.png)">
                                                                </span>
                                                            <?php endif; ?>
                                                            <?php if ($character['role_heal'] == 1) : ?>
                                                                <span class="avatar avatar-sm" style="background-image: url(/assets/images/roles/role_heal.png)">
                                                                </span>
                                                            <?php endif; ?>
                                                            <?php if ($character['role_dps'] == 1) : ?>
                                                                <span class="avatar avatar-sm" style="background-image: url(/assets/images/roles/role_dps.png)">
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

                    <?php if (isset($_SESSION['admin'])) : ?>

                        <div class="card" data-update-attendance>
                            <div class="card-header">
                                <h3 class="card-title">[Admin] Update attendance</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">

                                        <div class="form-group mb-3">
                                            <label class="form-label">Characternames (from addon) </label>
                                            <input type="text" class="form-control" id="bulk-update-characters" placeholder="a,b,c,d">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="form-label"> Bosscount (default 10) </label>
                                            <input type="number" class="form-control" id="bulk-update-count" value="10">
                                        </div>

                                        <div class="form-group mb-3">
                                            <button class="btn btn-primary" id="bulk-update-preview">Preview</button>
                                        </div>

                                        <div class="form-group mb-3">
                                            <button class="btn btn-primary" id="bulk-update-run">Run</button>
                                        </div>

                                        <ul id="status-list">
                                        </ul>

                                    </div>
                                </div>
                            </div>
                        </div> <!-- card -->

                        <div class="card" data-update-attendance>
                            <div class="card-header">
                                <h3 class="card-title">[Admin] Export payment data</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">

                                        <div class="form-group mb-3">
                                            <button class="btn btn-primary" onclick="PaymentListToClipBoard()">Export payment-data to clipboard</button>
                                        </div>

                                        <div class="form-group mb-3">
                                            <button class="btn btn-primary" onclick="SetAllPaid(<?php echo $id ?>)">Mark all as paid</button>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div> <!-- card -->

                        <div class="card" data-admin-add>
                            <div class="card-header">
                                <h3 class="card-title">[Admin] Add characters</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-12">
                                        <form action="/api/AddAttendance.php" method="post" id="AddAttendance">
                                            <div class="form-group">
                                                <label class="form-label">Select character</label>
                                                <select name="character" id="select-character" class="form-control custom-select">
                                                    <?php
                                                    $characters = GetCharacters($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters);
                                                    usort($characters, function ($a, $b) {
                                                        return strcmp($a["name"], $b["name"]);
                                                    });
                                                    foreach ($characters as $character) :
                                                        $alreadyInTheRaid = false;
                                                        foreach ($attendees as $attendee) {
                                                            if ($attendee["characterId"] == $character["id"]) {
                                                                $alreadyInTheRaid = true;
                                                            }
                                                        }

                                                        if ($alreadyInTheRaid) {
                                                            continue;
                                                        }
                                                    ?>
                                                        <option value="<?php echo $character["id"] ?>">
                                                            <?php echo $character["name"] ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <input type="hidden" name="raid" value="<?php echo $id ?>" />
                                                <input type="hidden" name="bosses" value="0" />
                                                <input type="hidden" name="return" value="/raid/" />
                                            </div>
                                            <div class="form-group">
                                                <input class="btn btn-primary" type="submit" value="Add">
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- card -->

                    <?php endif; ?>

                    <?php if (isset($_SESSION['auth'])) : ?>

                        <div class="card" data-add>
                            <div class="card-header">
                                <h3 class="card-title">Add my characters</h3>
                            </div>

                            <div class="card-body">
                                <div class="row d-none d-lg-flex py-2 text-muted">
                                    <div class="col-1 col-lg-1">Id</div>
                                    <div class="col-6 col-lg-5">Name</div>
                                    <div class="col-5 col-lg-5">Class</div>
                                    <div class="col-12 col-lg-1"></div>
                                </div>
                                <?php
                                $myCharacters = GetMyCharacters($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters, $_SESSION['auth']);
                                foreach ($myCharacters as $myCharacter) :
                                    $alreadyInTheRaid = false;
                                    foreach ($attendees as $attendee) {
                                        if ($attendee["characterId"] == $myCharacter["id"]) {
                                            $alreadyInTheRaid = true;
                                        }
                                    }

                                    if ($alreadyInTheRaid) continue;
                                ?>
                                    <form class="row border-top py-2 add-attendance" method="post" action="/api/AddAttendance.php">
                                        <div class="col-1 col-lg-1 my-2 my-lg-0">
                                            <span class="text-muted"><?php echo $myCharacter["id"] ?></span>
                                            <input type="hidden" name="character" value="<?php echo $myCharacter["id"] ?>" />
                                            <input type="hidden" name="raid" value="<?php echo $id ?>" />
                                            <input type="hidden" name="return" value="/raid/" />
                                        </div>
                                        <div class="col-6 col-lg-5 my-2 my-lg-0">
                                            <a href="/character/?id=<?php echo $myCharacter["id"] ?>" class="text-reset text-decoration-underline"><?php echo $myCharacter["name"] ?></a>
                                        </div>
                                        <div class="col-5 col-lg-5 my-2 my-lg-0">
                                            <?php echo ClassFromId($myCharacter["class"]) ?>
                                        </div>
                                        <div class="col-12 col-lg-1 my-2 my-lg-0">
                                            <button type="submit" class="btn btn-success btn-sm">Add</button>
                                        </div>
                                    </form>

                                <?php endforeach; ?>

                            </div> <!-- card-body -->

                        </div> <!-- card -->

                    <?php endif; ?>

                    <div class="card" data-characters>
                        <div class="card-header">
                            <h3 class="card-title">Characters (<?php echo sizeof($attendees) ?>)</h3>
                        </div>
                        <div class="card-body">
                            <div class="row d-none d-lg-flex py-2 text-muted">
                                <div class="col-1 col-lg-1">Id</div>
                                <div class="col-6 col-lg-3">Name</div>
                                <div class="col-5 col-lg-3">Signup date</div>
                                <div class="col-6 col-lg-1">Cut</div>
                                <div class="col-6 col-lg-1">Bosses</div>
                                <div class="col-6 col-lg-1">Paid</div>
                                <?php if (isset($_SESSION['admin'])) : ?>
                                    <div class="col-6 col-lg-1"></div>
                                <?php endif; ?>
                                <div class="col-6 col-lg-1"></div>
                            </div>
                            <?php
                            if (!empty($attendees)) {
                                foreach ($attendees as $attendee) :
                            ?>
                                    <form class="row border-top py-2 update-attendance" action="/api/UpdateAttendance.php" data-character-name="<?php echo $attendee["name"] ?>" data-character-id="<?php echo $attendee["characterId"] ?>">
                                        <div class="col-1 col-lg-1 my-2 my-lg-0">
                                            <span class="text-muted"><?php echo $attendee["id"] ?></span>
                                            <input type="hidden" name="characterId" value="<?php echo $attendee["characterId"] ?>" />
                                            <input type="hidden" name="raidId" value="<?php echo $attendee["raidId"] ?>" />
                                            <?php
                                            $mainName = "";
                                            if (!empty($attendee["main"])) {
                                                foreach ($characters as $character) {
                                                    if ($attendee["main"] == $character["id"]) {
                                                        $mainName = $character["name"];
                                                        break;
                                                    }
                                                }
                                            } else {
                                                $mainName = $attendee["name"];
                                            }
                                            ?>
                                            <input type="hidden" name="mainName" value="<?php echo $mainName ?>" />
                                        </div>
                                        <div class="col-6 col-lg-3 my-2 my-lg-0">
                                            <a href="/character/?id=<?php echo $attendee["characterId"] ?>" class="text-reset text-decoration-underline"><?php echo $attendee["name"] ?></a>
                                            <button type="button" onclick="navigator.clipboard.writeText('<?php echo $attendee["name"] ?>'); this.innerText='(copied!)'" class="border-0 bg-transparent ml-2 text-reset"> (copy) </button>
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
                                                <input type="number" name="bosses" class="w-100" value="<?php echo $attendee["bosses"] ?>" />
                                            </label>
                                        </div>
                                        <div class="col-6 col-lg-1 my-2 my-lg-0">
                                            <input type="checkbox" name="paid" value="1" <?php echo $attendee["paid"] == 0 ? "" : "checked=\"checked\"" ?> style="height: 22px; width: 22px;">
                                            <span class="d-inline-block d-lg-none align-top"> (Paid) </span>
                                        </div>
                                        <?php if (isset($_SESSION['admin'])) : ?>
                                            <div class="col-6 col-lg-1 my-2 my-lg-0">
                                                <button type="submit" class="btn btn-primary btn-sm">Update</button>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (isset($_SESSION['admin']) || $_SESSION['auth'] == $attendee["discord"]) : ?>
                                            <div class="col-6 col-lg-1 my-2 my-lg-0">
                                                <a href="/api/DeleteAttendanceForCharacter/?characterId=<?php echo $attendee["characterId"] ?>&raidId=<?php echo $id ?>&return=/raid/" class="btn btn-danger btn-sm">Remove</a>
                                            </div>
                                        <?php endif; ?>
                                    </form>

                            <?php endforeach;
                            } ?>

                        </div> <!-- card-body -->
                    </div> <!-- card -->

                <?php endif; ?>
            </div> <!-- container-xl -->

            <!-- Toast success -->
            <div aria-live="polite" aria-atomic="true" style="position: fixed; top: 0; right: 0; width: 320px;">
                <div class="toast success" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-toggle="toast">
                    <div class="toast-header">
                        <strong class="me-auto">A2D</strong>
                        <small>just now</small>
                        <button type="button" class="ms-2 btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        Change saved
                    </div>
                </div>
            </div>

            <!-- Toast error -->
            <div aria-live="polite" aria-atomic="true" style="position: fixed; top: 0; right: 0; width: 320px;">
                <div class="toast error" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true" data-bs-toggle="toast">
                    <div class="toast-header">
                        <strong class="me-auto">A2D</strong>
                        <small>just now</small>
                        <button type="button" class="ms-2 btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        Error when trying to save
                    </div>
                </div>
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
                currentForm.addEventListener("submit", function(evt) {
                    evt.preventDefault();
                    UpdateCut()
                    let formData = new FormData(currentForm);
                    let xhr = new XMLHttpRequest();
                    xhr.open("POST", currentForm.getAttribute('action'));
                    xhr.onreadystatechange = function() {
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

        (function() {
            let playerCards = document.querySelectorAll('[data-players] div.card');
            if (playerCards && playerCards.length > 0) {
                // let colors = ['rgb(208, 255, 254)', 'rgb(255, 253, 219)', 'rgb(228, 255, 222)', 'rgb(255, 211, 253)', 'rgb(255, 231, 211)', 'rgb(255, 255, 255)'];
                let colors = [
                    'rgb(53, 64, 82)', //default
                    'rgb(196, 31, 59)', //Death Knight
                    'rgb(163, 48, 201)', //Demon Hunter
                    'rgb(255, 125, 10)', //Druid
                    'rgb(169, 210, 113)', //Hunter
                    'rgb(64, 199, 235)', //Mage
                    'rgb(0, 255, 150)', //Monk
                    'rgb(245, 140, 186)', //Paladin
                    'rgb(255, 255, 255)', //Priest
                    'rgb(255, 245, 105)', //Rogue
                    'rgb(0, 112, 222)', //Shaman
                    'rgb(135, 135, 237)', //Warlock
                    'rgb(199, 156, 110)', //Warrior
                    'rgb(51, 147, 127)' //Evoker
                ];
                for (let i = 0; i < playerCards.length; i++) {
                    playerCards[i].addEventListener('click', function(e) {
                        let card = e.currentTarget;
                        let currentColor = card.style.borderColor;
                        var colorIndex = (colors.indexOf(currentColor) + 1) % colors.length;
                        card.style.borderColor = colors[colorIndex];
                    });

                    playerCards[i].addEventListener('contextmenu', function(e) {
                        if (e.preventDefault != undefined)
                            e.preventDefault();
                        if (e.stopPropagation != undefined)
                            e.stopPropagation();

                        let card = e.currentTarget;
                        let currentColor = card.style.borderColor;
                        var colorIndex = ((colors.indexOf(currentColor) - 1) + colors.length) % colors
                            .length;
                        card.style.borderColor = colors[colorIndex];

                        return false;
                    });
                }
            }
        }());

        let raidId = <?php echo $id ?>;
        (function() {
            let characterList = document.querySelector('#bulk-update-characters');
            let attendanceCountElem = document.querySelector('#bulk-update-count');
            let attendanceCount = 0;
            let characters = [];
            let preview = document.querySelector('#bulk-update-preview');
            let run = document.querySelector('#bulk-update-run');
            let list = document.querySelector('#status-list');

            if (characterList) {
                characterList.addEventListener('input', function(e) {
                    let csv = characterList.value
                    characters = csv.split(',');
                    if (run) {
                        run.disabled = true;
                    }
                });
            }

            if (preview) {
                preview.addEventListener('click', function(e) {
                    console.log(characters)
                    if (run) {
                        run.disabled = false;
                    }

                    if (characters && characters.length > 0) {
                        let allCharacters = document.querySelectorAll('[data-character-name]');
                        if (allCharacters && allCharacters.length > 0) {
                            for (let i = 0; i < allCharacters.length; i++) {
                                const characterUpdateForm = allCharacters[i];
                                const characterName = characterUpdateForm.getAttribute(
                                    'data-character-name');
                                if (characters.indexOf(characterName) === -1) {
                                    characterUpdateForm.style.backgroundColor = 'rgba(100, 0, 0, 0.25)';
                                }
                            }
                        }
                    }
                });
            }

            if (run) {
                run.addEventListener('click', function(e) {
                    if (attendanceCountElem) {
                        attendanceCount = Number.parseInt(attendanceCountElem.value)
                    }

                    run.disabled = true;
                    let timeIncrement = 500;
                    let time = timeIncrement;

                    if (characters && characters.length > 0) {
                        let allCharacters = document.querySelectorAll('[data-character-name]');
                        if (allCharacters && allCharacters.length > 0) {
                            for (let i = 0; i < allCharacters.length; i++) {
                                const characterUpdateForm = allCharacters[i];
                                const characterName = characterUpdateForm.getAttribute(
                                    'data-character-name');
                                const characterId = characterUpdateForm.getAttribute('data-character-id');
                                if (characters.indexOf(characterName) === -1) {
                                    setTimeout(() => {
                                        let xhr = new XMLHttpRequest();
                                        xhr.open("GET",
                                            '/api/DeleteAttendanceForCharacter/?characterId=' +
                                            characterId + '&raidId=' + raidId);
                                        xhr.onreadystatechange = function() {
                                            if (this.readyState == 4) {
                                                let li = document.createElement('li');
                                                li.innerText = 'Removed ' + characterName;
                                                list.appendChild(li);
                                                characterUpdateForm.style.display = 'none';
                                            }
                                        };
                                        xhr.send()
                                    }, time);
                                } else {
                                    setTimeout(() => {
                                        let xhr = new XMLHttpRequest();
                                        let formData = new FormData();
                                        formData.append("characterId", characterId);
                                        formData.append("raidId", raidId);
                                        formData.append("bosses", attendanceCount);
                                        formData.append("paid", 0);

                                        xhr.open("POST", '/api/UpdateAttendance/');
                                        xhr.onreadystatechange = function() {
                                            if (this.readyState == 4) {
                                                let li = document.createElement('li');
                                                li.innerText = 'Updated ' + characterName +
                                                    ' with attendance count: ' +
                                                    attendanceCount;
                                                list.appendChild(li);

                                                let currentCharacterAttendanceCount =
                                                    characterUpdateForm.querySelector(
                                                        'input[name="bosses"]');
                                                if (currentCharacterAttendanceCount) {
                                                    currentCharacterAttendanceCount.value =
                                                        attendanceCount;
                                                }
                                            }
                                        };
                                        xhr.send(formData)
                                    }, time);
                                }

                                time += timeIncrement;
                            }
                        }
                    }
                });
            }

        }());

        function GetPaymentList() {
            let paymentList = '';

            var characterForms = document.querySelectorAll('form.update-attendance');
            if (characterForms && characterForms.length > 0) {
                for (let i = 0; i < characterForms.length; i++) {
                    const element = characterForms[i];
                    let mainName = element.querySelector('input[name="mainName"]').value;
                    let cut = element.querySelector('input[data-cut]').value;
                    let paid = element.querySelector('input[name="paid"]').checked;

                    if (!paid) {
                        paymentList += mainName + "=" + cut + ";";
                    }
                }
            }

            return paymentList
        }

        function PaymentListToClipBoard() {
            navigator.clipboard.writeText(GetPaymentList());
            alert('Payment-list has been copied to clipboard! Remember to mark all as paid.');
        }

        function SetAllPaid(raidId) {
            let xhr = new XMLHttpRequest();
            xhr.open("GET", '/api/SetAllPaid/?raidId=' + raidId);
            xhr.onreadystatechange = function() {
                if (this.readyState == 4) {
                    alert("Complete. Refresh site for updated data.");
                }
            };
            xhr.send()
        }
    </script>
    <style>
        *[data-player-id] {
            border: 4px solid;
        }
    </style>
</body>

</html>