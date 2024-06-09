<?php require './partials/_session_start.php'; ?>
<!doctype html>
<html lang="en" dir="ltr">
<?php
$title = "Battle.net - A2D";
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
                                Blizzard API Authentication status
                            </h2>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">API Status</h3>
                    </div>
                    <div class="card-body">

                        <p>Battle.net tokens are only used for updating <span class="bold">ilvl</span> of your already
                            imported characters (for now)!

                            <?php
                            $token = '';
                            $timeLeft = 0;
                            if (isset($_SESSION['token'])) {
                                $token = $_SESSION['token'];
                                $timeLeft = ($_SESSION['token_create'] + $_SESSION['token_expire']) - time();
                            }

                            if (empty($token)) :
                            ?>
                        <p>You do NOT have an active access-token.</p>
                        <p> <a href="/api/BattleNetToken/?return=/bnet/" class="text-reset text-decoration-underline">Click here</a> to get a new one.</p>
                    <?php elseif ($timeLeft > 0) : ?>
                        <p> Your token has <?php echo $timeLeft ?> seconds left </p>
                    <?php else : ?>
                        <p> Your token has expired. </p>
                        <p> <a href="/api/BattleNetToken/?return=/bnet/" class="text-reset text-decoration-underline">Click here</a> to get a new one.</p>
                    <?php endif; ?>

                    </div> <!-- card-body -->
                </div> <!-- card -->

                <?php if ($timeLeft > 0) : ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Update all characters</h3>
                        </div>
                        <div class="card-body">
                            <button onclick="StartUpdatingCharacters(this)" class="btn btn-success">Click here to start</button>
                            <hr />
                            <ul id="status-list">
                            </ul>
                        </div> <!-- card-body -->
                    </div> <!-- card -->
                <?php endif; ?>

            </div> <!-- container -->
            <?php require './partials/_footer.php' ?>
        </div> <!-- content -->
    </div> <!-- page -->
    <?php require './partials/_scripts.php' ?>
    <script>
        let characters = [];
        <?php
        include_once './api/secrets.php';
        include_once './api/helper.php';
        $characters = GetCharacterIds($dbservername, $dbusername, $dbpassword, $dbname, $dbtable_characters);
        foreach ($characters as $character) {
            echo 'characters.push(' . $character['id'] . ');';
        }
        ?>

        let list = document.querySelector('#status-list');
        let running = false;

        function StartUpdatingCharacters(caller) {
            running = !running;

            if (running) {
                caller.innerText = 'Click here to STOP'
            } else {
                caller.innerText = 'Click here to resume'
            }

            if (!running) return;

            if (!list) {
                alert('Status list not set up properly');
                return;
            }

            if (characters && characters.length > 0) {
                let nextchar = characters.pop();
                UpdateCharacter(nextchar);
            } else {
                alert('No characters in characterlist');
            }
        };


        function UpdateCharacter(characterId) {
            if (!running) return;

            let li = document.createElement('li');

            let xhr = new XMLHttpRequest();
            xhr.open("GET", '/api/BattleNetUpdateCharacter/?ajax=1&id=' + characterId);
            xhr.onreadystatechange = function() {
                if (this.readyState == 4) {
                    li.innerText = decodeURI(xhr.responseText);

                    if (this.status == 200) {
                        li.classList = 'bnet-update--success';
                    } else {
                        li.classList = 'bnet-update--fail bnet-update__code--' + this.status;
                    }
                    list.appendChild(li);

                    if (characters.length > 0) {
                        setTimeout(function() {
                            let nextchar = characters.pop();
                            UpdateCharacter(nextchar);
                        }, 1000);
                    } else {
                        alert('Finished updating all characters');
                    }
                }
            };
            xhr.send()
        }
    </script>
</body>

</html>