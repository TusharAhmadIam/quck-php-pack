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
$pg->itemsPerPage = 3;
$rows = $pg->fetch_results();
if($pg->totalResults > 0){
    foreach($rows as $row){
        echo $row['id']. " -> " .$row['name']. "<br>";
    }
}
echo '<br>';
echo $pg->links();
?>
</div>