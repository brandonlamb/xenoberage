<?php ?>
<!--
var myi = '<?php echo $_GET['mySEC'];?>';
setTimeout("rmyx();",1000);

function rmyx()
{
    myi = myi - 1;
    if (myi <= 0)
    {
        myi = <?php echo ($_GET['sched_ticks'] * 60); ?>
    }
    document.getElementById("myx").innerHTML = myi;
    setTimeout("rmyx();",1000);
}
-->
