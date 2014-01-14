<script>

// Ticker Constructor
news                      = new newsTicker();

// Initialize the Ticker, You need to supply the HTML id as the argument, returns true or false.
var result                = news.initTicker('news_ticker');

// I have put in some safaty precautions, but just in case always check the return value from initTicker().
if (result == true)
{
    // Set the width of the Ticker (in pixles)
    news.Width(500);

    // Gets the Width of the Ticker (in pixles)
    var width = news.Width();

    // Sets the Interval/Update Time in seconds.
    news.Interval(5);

    // Gets the Interval/Update Time in Seconds.
    var interval = news.Interval();

    // I have decided on adding single news articles at a time due to it makes it more easier to add when using PHP or XSL.
    // We can supply the information by either of the following ways:
    // 1: Supply the information from a Database and inserting it with PHP.
    // 2: Supply the information from a Database and convert it into XML (for formatting) and have the XSLT Stylesheet extract the information and insert it.

<?php
    global $db_logging;

    $startdate = date("Y/m/d");
    $res = $db->Execute("SELECT * FROM {$db->prefix}news WHERE date > '{$startdate} 00:00:00' AND date < '{$startdate} 23:59:59' ORDER BY news_id");
    db_op_result ($db, $res, __LINE__, __FILE__, $db_logging);

    if ($res->EOF)
    {
        echo "    url = 'news.php';\n";
        echo "    text = \"{$l->get('l_news_none')}\";\n";
        echo "    type = null;    // Not used as yet.\n";
        echo "    delay = 5;                       // in seconds.\n";
        echo "    news.addArticle(url, text, type, delay);\n";
    }
    else
    {
        while (!$res->EOF)
        {
            $row = $res->fields;
            $headline = addslashes($row['headline']);
            echo "    url = 'news.php';\n";
            echo "    text = '{$headline}';\n";
            echo "    type = '{$row['news_type']}';    // Not used as yet.\n";
            echo "    delay = 5;                       // in seconds.\n";
            echo "    news.addArticle(url, text, type, delay);\n";
            echo "\n";
            $res->MoveNext();
        }
        echo "    news.addArticle(null, 'End of News', null, 5);\n";
    }
?>
    // Starts the Ticker.
    news.startTicker();

    // If for some reason you need to stop the Ticker use the following line.
    // news.stopTicker();
}
</script>
