<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<div class="container py-2">
<?php
spl_autoload_register(function($class){
    require_once 'classes/'.$class.'.php';
});

$db = new DB();
$connection = $db->con;
$query = 'select * from persons';
// function bind_function($stmt){
//     $cat = 1;
//     $stmt->bindParam(':cat',$cat,PDO::PARAM_INT);
// }
$pg = new Pagination($connection,$query);
$pg->customQueryString = '&name=tushar&daughter=trisha';
$pg->itemsPerPage = 5;
$pg->buttonNumbers = 5;
$rows = $pg->fetch_results();
if($pg->totalResults > 0){
    foreach($rows as $row){
        echo $row['id']. " -> " .$row['name']. "<br>";
    }
}else{
    echo 'No records found';
}
echo '<br>';
echo $pg->totalResults;
echo $pg->links();

?>
<form action="" method="get">
    <input type="text" name="name"><br><br>
    <input type="text" name="email"><br><br>
    <input type="submit" value="submit"><br><br>
</form>
</div>