<!doctype html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport"
        content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta http-equiv="Content-Language" content="en" />
    <meta name="msapplication-TileColor" content="#2d89ef">
    <meta name="theme-color" content="#4188c9">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="HandheldFriendly" content="True">
    <meta name="MobileOptimized" content="320">
    <link rel="icon" href="./favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" type="image/x-icon" href="./favicon.ico" />
    <!-- Generated: 2018-04-16 09:29:05 +0200 -->
    <title>Homepage - tabler.github.io - a responsive, flat and full featured admin template</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,300i,400,400i,500,500i,600,600i,700,700i&amp;subset=latin-ext">
    <script src="./assets/js/require.min.js"></script>
    <script>
        requirejs.config({
            baseUrl: '.'
        });

    </script>
    <!-- Dashboard Core -->
    <link href="./assets/css/dashboard.css" rel="stylesheet" />
    <script src="./assets/js/dashboard.js"></script>
    <!-- c3.js Charts Plugin -->
    <link href="./assets/plugins/charts-c3/plugin.css" rel="stylesheet" />
    <script src="./assets/plugins/charts-c3/plugin.js"></script>
    <!-- Google Maps Plugin -->
    <link href="./assets/plugins/maps-google/plugin.css" rel="stylesheet" />
    <script src="./assets/plugins/maps-google/plugin.js"></script>
    <!-- Input Mask Plugin -->
    <script src="./assets/plugins/input-mask/plugin.js"></script>
</head>

<body class="">
    <div class="page">
        <div class="page-main">
            <div class="header collapse d-lg-flex p-0" id="headerMenuCollapse">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg order-lg-first">
                            <ul class="nav nav-tabs border-0 flex-column flex-lg-row">
                                <li class="nav-item">
                                    <a href="./index.php" class="nav-link"><i class="fe fe-home"></i>Home</a>
                                </li>
                                <li class="nav-item">
                                    <a href="./raids.php" class="nav-link active"><i
                                            class="fe fe-database"></i>Raids</a>
                                </li>
                                <li class="nav-item">
                                    <a href="./characters.php" class="nav-link"><i
                                            class="fe fe-users"></i>Characters</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
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
                                        <form class="row border-top py-2 update-attendance" action="/boosting/api/UpdateAttendance.php">
                                            <div class="col-1 col-lg-1 my-2 my-lg-0">
                                                <span class="text-muted"><?php echo $attendee["id"]?></span>
                                                <input type="hidden" name="character" value="<?php echo $attendee["characterId"]?>" />
                                                <input type="hidden" name="raid" value="<?php echo $attendee["raidId"]?>" />
                                            </div>
                                            <div class="col-6 col-lg-3 my-2 my-lg-0">
                                                <a href="character.php?id=<?php echo $attendee["characterId"]?>"
                                                    class="text-inherit"><?php echo $attendee["name"]?></a>
                                            </div>
                                            <div class="col-5 col-lg-3 my-2 my-lg-0">
                                            <?php echo ClassFromId($attendee["class"]) ?>

                                            </div>
                                            <div class="col-6 col-lg-2 my-2 my-lg-0">
                                                <label class="mb-0 w-100">
                                                    <input type="number" name="bosses" class="w-100" value="<?php echo $attendee["bosses"]?>" />
                                                </label>
                                            </div>
                                            <div class="col-6 col-lg-1 my-2 my-lg-0">
                                                <input type="checkbox" name="paid" value="1" <?php echo $attendee["paid"] == 0 ? "" : "checked=\"checked\""?> style="height: 22px; width: 22px;">
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
    <footer class="footer">
        <div class="container">
            <div class="row align-items-center flex-row-reverse">
                <div class="col-12 col-lg-auto mt-3 mt-lg-0 text-center">
                    Copyright © <?php echo date("Y"); ?> <a href=".">Amused to Death</a>. All rights reserved.
                </div>
            </div>
        </div>
    </footer>
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

        var AddOrUpdateRaid = document.querySelector('#AddOrUpdateRaid');
        if (AddOrUpdateRaid) {
            AddOrUpdateRaid.addEventListener("submit", function (evt) {
                evt.preventDefault();

                let formData = new FormData(AddOrUpdateRaid);
                let xhr = new XMLHttpRequest();
                xhr.open("POST", AddOrUpdateRaid.getAttribute('action'));
                xhr.onreadystatechange = function () {
                    if (this.readyState == 4) {
                        if (this.status == 200) {
                            alert('Raid Saved');
                        } else {
                            alert('error');
                        }
                    }
                };
                xhr.send(formData)
            }, true);
        }
    
        var UpdateAttendance = document.querySelectorAll('form.update-attendance');
        if (UpdateAttendance && UpdateAttendance.length > 0){
            for(let i = 0; i < UpdateAttendance.length; i++){
                let currentForm = UpdateAttendance[i];
                currentForm.addEventListener("submit", function (evt) {
                    evt.preventDefault();

                    let formData = new FormData(currentForm);
                    let xhr = new XMLHttpRequest();
                    xhr.open("POST", currentForm.getAttribute('action'));
                    xhr.onreadystatechange = function () {
                        if (this.readyState == 4) {
                            if (this.status == 200) {
                                console.log(this)
                                alert('attendance updated');
                            } else {
                                alert('error');
                            }
                        }
                    };
                    xhr.send(formData)
                }, true);
            }
        }

        // var AddAttendance = document.querySelector('#AddAttendance');
        // if (AddAttendance) {
        //     AddAttendance.addEventListener("submit", function(evt){
        //         evt.preventDefault();

        //         let formData = new FormData(AddAttendance);
        //         let xhr = new XMLHttpRequest();
        //         xhr.open("POST", AddAttendance.getAttribute('action'));
        //         xhr.onreadystatechange = function() {
        //             if (this.readyState == 4) {
        //                 if(this.status == 200){
        //                     alert('added new char');
        //                 } else {
        //                     alert('error');
        //                 }
        //             }
        //         };
        //         xhr.send(formData)
        //     }, true);
        // }

    </script>
</body>

</html>
