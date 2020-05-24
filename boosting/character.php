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
                            <form action="/boosting/api/AddOrUpdateCharacter.php" method="post" class="card" id="characterform">
                                <div class="card-header">
                                    <h3 class="card-title">Form elements</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label class="form-label">Id</label>
                                                <div class="form-control-plaintext">-1</div>
                                                <input type="hidden" name="id" value=""/>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Name</label>
                                                <input type="text" class="form-control" name="name"
                                                    placeholder="Name.." required="required">
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">Class</label>
                                                <select name="class" id="select-class" class="form-control custom-select">
                                                <option value="1">Mage</option>
                                                <option value="4">Druid</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">Main</label>
                                                <select name="main" id="select-main" class="form-control custom-select">
                                                <option value="" selected>None</option>
                                                <option value="1">Mage</option>
                                                <option value="4">Druid</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label class="form-label">Created</label>
                                                <div class="form-control-plaintext">00/00/0000 00:00</div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Updated</label>
                                                <div class="form-control-plaintext">00/00/0000 00:00</div>
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
                            alert(xhr.responseText);

                        } else {
                            alert('error');
                        }
                    }
                };
                xhr.send(formData)
            }, true);
        }
    </script>
</body>

</html>
