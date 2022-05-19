<?php
require 'classes/DB.php';
require_once 'vendor/autoload.php';

$db = new DB();
$faker = Faker\Factory::create();

$limit = 10;

foreach(range(1,$limit) as $x):

$name = $faker->name;
$cat = rand(1,2);

$query = 'truncate table persons';
// $query = 'insert into persons (name,cat) values (:name,:cat)';

$stmt = $db->con->prepare($query);

// $stmt->bindParam(':name', $name);
// $stmt->bindParam(':cat', $cat);

$stmt->execute();

endforeach;

