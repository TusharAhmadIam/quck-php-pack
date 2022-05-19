<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<div class="container py-2">
<?php
spl_autoload_register(function($class){
    require_once 'classes/'.$class.'.php';
});

$db = new DB();
$connection = $db->con;
// $cat = 2;

$query = 'select * from persons';
// $query = 'select * from persons where cat = '.$cat;
// $query = 'select * from persons where cat = ?';
// $query = 'select * from persons where cat = :cat';

// function bind_function($stmt){
//     $cat = 2;
//     $stmt->bindParam(':cat',$cat,PDO::PARAM_INT);
// }

// function bind_function($stmt){
//     $cat =2;
//     $stmt->bind_param('i',$cat);
// }

// $pg = new Pagination($connection,$query,'bind_function');
$pg = new Pagination($connection,$query);

$pg->pdo = true;

// $pg->customQueryString = '&name=tushar&daughter=trisha';

$pg->itemsPerPage = 5;

$pg->buttonNumbers = 5;

echo '<pre>';

$rows = $pg->fetch_results();

if($pg->totalResults >0 ){
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
    <input type="text" name="name"><br>
    <input type="text" name="email"><br>
    <input type="submit" value="submit"><br>
</form>
</div>