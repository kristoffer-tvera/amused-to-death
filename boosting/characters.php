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
                            Characters
                        </h1>
                    </div>

                    <div class="row row-cards row-deck">

                        <div class="col-12">
                            <div class="card">
                                <a href="/boosting/character/" class="card-header text-default justify-content-center">
                                    <h3 class="card-title">New character</h3>
                                </a>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Characters</h3>
                                </div>
                                <div class="table-responsive">
                                    <table class="table card-table table-vcenter text-nowrap">
                                        <thead>
                                            <tr>
                                                <th class="w-1">Id.</th>
                                                <th>Name</th>
                                                <th>Main</th>
                                                <th>Class</th>
                                                <th>ilvl</th>
                                                <th>Created</th>
                                                <th>Updated</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            include_once './api/db.php';
                                            include_once './api/db_helper.php';
                                            $characters = GetCharacters($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters);

                                            foreach($characters as $character):
                                             ?>
                                            <tr>
                                                <td>
                                                    <span class="text-muted"> <?php echo $character['id']; ?> </span>
                                                </td>
                                                <td>
                                                    <a href="/boosting/character/?id=<?php echo $character['id']; ?>"
                                                        class="text-inherit"><?php echo $character['name']; ?></a>
                                                </td>
                                                <td>
                                                    <?php 
                                                        if(!empty($character['main'])){
                                                            foreach($characters as $mainCharacter){
                                                                if($mainCharacter['id'] == $character['main']){
                                                                    ?>
                                                    <a href="character.php?id=<?php echo $mainCharacter['id']; ?>"
                                                        class="text-inherit"><?php echo $mainCharacter['name']; ?></a>
                                                    <?php
                                                                }
                                                            }
                                                        }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php echo ClassFromId($character['class']); ?>
                                                </td>
                                                <td>
                                                    <?php echo $character['ilvl']; ?>
                                                </td>
                                                <td>
                                                    <?php echo $character['added_date']; ?>
                                                </td>
                                                <td>
                                                    <?php echo $character['change_date']; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
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
</body>

</html>
