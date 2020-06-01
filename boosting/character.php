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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" integrity="sha256-ENFZrbVzylNbgnXx0n3I1g//2WeO47XxoPe0vkp3NC8=" crossorigin="anonymous" />
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
                                    <a href="./raids.php" class="nav-link"><i class="fe fe-database"></i>Raids</a>
                                </li>
                                <li class="nav-item">
                                    <a href="./characters.php" class="nav-link active"><i class="fe fe-users"></i>Characters</a>
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
                            <form action="/boosting/api/AddOrUpdateCharacter.php" method="post" class="card" id="characterform">
                                <div class="card-header">
                                    <h3 class="card-title">Form elements</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label class="form-label">Id</label>
                                                <div class="form-control-plaintext"><?php echo $id ?></div>
                                                <input type="hidden" name="id" value="<?php echo $id ?>"/>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Ilvl</label>
                                                <div class="form-control-plaintext"><?php echo $ilvl ?></div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Name</label>
                                                <input type="text" class="form-control" name="name"
                                                    placeholder="Name.." required="required" value="<?php echo $name ?>">
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">Class</label>
                                                <select name="class" id="select-class" class="form-control custom-select">
                                                <option value="0" <?php if($class == 0) echo "selected=\"selected\""?>>Druid</option>
                                                <option value="1" <?php if($class == 1) echo "selected=\"selected\""?>>Paladin</option>
                                                <option value="2" <?php if($class == 2) echo "selected=\"selected\""?>>Warrior</option>
                                                <option value="3" <?php if($class == 3) echo "selected=\"selected\""?>>Demon Hunter</option>
                                                <option value="4" <?php if($class == 4) echo "selected=\"selected\""?>>Hunter</option>
                                                <option value="5" <?php if($class == 5) echo "selected=\"selected\""?>>Mage</option>
                                                <option value="6" <?php if($class == 6) echo "selected=\"selected\""?>>Rogue</option>
                                                <option value="7" <?php if($class == 7) echo "selected=\"selected\""?>>Death Knight</option>
                                                <option value="8" <?php if($class == 8) echo "selected=\"selected\""?>>Priest</option>
                                                <option value="9" <?php if($class == 9) echo "selected=\"selected\""?>>Warlock</option>
                                                <option value="10" <?php if($class == 10) echo "selected=\"selected\""?>>Shaman</option>
                                                <option value="11" <?php if($class == 11) echo "selected=\"selected\""?>>Monk</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">Main</label>
                                                <select name="main" id="select-main" class="form-control custom-select">
                                                <option value="-1" <?php if($main == 0) echo "selected=\"selected\"" ?>>None</option>
                                                <?php
                                                $characters = GetCharacters($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters);
                                                foreach($characters as $character):
                                                ?>
                                                <option value="<?php echo $character["id"] ?>" <?php if($main == $character["id"]) echo "selected=\"selected\"" ?> ><?php echo $character["name"] ?></option>
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
                                                <th class="w-1">Id.</th>
                                                <th>Name</th>
                                                <th>Bosses</th>
                                                <th>Paid</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><span class="text-muted">1</span></td>
                                                <td><a href="raid.php?id=1" class="text-inherit">Char1</a></td>
                                                <td>
                                                    12
                                                </td>
                                                <td>
                                                    <div class="custom-controls-stacked">
                                                        <label class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input"
                                                                name="example-checkbox1" value="option1" checked="">
                                                            <span class="custom-control-label"></span>
                                                        </label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div> <!-- table-responsive -->
                            </div> <!-- card -->
                        </div> <!-- col-12 -->

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
        
                $('#select-class').selectize({});
                $('#select-main').selectize({});
            });
        });

        require(['toastr']);

        var form = document.querySelector('#characterform');
        if (form) {
            form.addEventListener("submit", function(evt){
                evt.preventDefault();

                let formData = new FormData(form);
                let xhr = new XMLHttpRequest();
                xhr.open("POST", form.getAttribute('action'));
                xhr.onreadystatechange = function() {
                    if (this.readyState == 4) {
                        if(this.status == 200){
                            toastr.success('Have fun storming the castle!', 'Miracle Max Says')
                        } else {
                            toastr.error('I do not think that word means what you think it means.', 'Inconceivable!')
                        }
                    }
                };
                xhr.send(formData)
            }, true);
        }
    </script>
</body>

</html>
