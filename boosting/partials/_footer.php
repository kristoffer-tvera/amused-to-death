<footer class="footer footer-transparent">
    <div class="container">
        <div class="row text-center align-items-center flex-row-reverse">
            <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                <p class="mb-0">Servertime: <span id="clock-h"><?php echo date("H") ?></span>:<span id="clock-m"><?php echo date("i") ?></span>:<span id="clock-s"><?php echo date("s") ?></span>. Copyright © <?php echo date("Y"); ?> <a href="." class="text-reset">Amused to Death</a>. All rights reserved. </p>
            </div>
        </div>
    </div>
</footer>
<script>
let serverTime = new Date('<?php echo date("c") ?>');

function leftPad(time){
    if(time < 10) return '0'+time;
    return time;
}

setInterval(function () {
  serverTime.setSeconds(serverTime.getSeconds() + 1);
  document.getElementById("clock-h").innerHTML = leftPad(serverTime.getUTCHours());
  document.getElementById("clock-m").innerHTML = leftPad(serverTime.getUTCMinutes());
  document.getElementById("clock-s").innerHTML = leftPad(serverTime.getUTCSeconds());
}, 1000);
</script>
