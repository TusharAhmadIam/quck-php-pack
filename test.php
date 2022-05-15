
<form action="" method="get">
    <input type="text" name="page"><br><br>
    <input type="text" name="name"><br><br>
    <input type="password" name="email"><br><br>

    <button type="submit">Submit</button>
</form>

<?php
    $url = $_SERVER['REQUEST_URI'];
    $queryString = substr($url,strpos($url,'&'));
    echo $queryString;
?>