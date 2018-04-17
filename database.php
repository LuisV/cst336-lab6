<?php
function getDatabaseConnection() {
    $host = "localhost";
     $user = "lvalencia";
     $pass = "101010Lv..";
     $db = "shopping_cart_cst336_sp_2018"; 
$charset = 'utf8mb4';
//checking whether the URL contains "herokuapp" (using Heroku)
if(strpos($_SERVER['HTTP_HOST'], 'herokuapp') !== false) {
    $url = parse_url(getenv("CLEARDB_DATABASE_URL"));
    $host = $url["host"];
    $db = substr($url["path"], 1);
    $user = $url["user"];
    $pass = $url["pass"];
}

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$opt = [
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
PDO::ATTR_EMULATE_PREPARES => false,
];
$pdo = new PDO($dsn, $user, $pass, $opt);
return $pdo;
}



function getMatchingItems($query, $category, $fromPrice, $toPrice, $order, $displayPics) {
    $db = getDatabaseConnection();
    $imgSQL = $displayPics ? ', item.image_url' : "";
    
    $sql = "SELECT DISTINCT item.item_id, item.name, item.price $imgSQL FROM item INNER JOIN item_category ON item.item_id = item_category.item_id INNER JOIN category ON item_category.category_id =category.category_id  WHERE 1";
    
    if(!empty($query)) {
        $sql .= " AND name LIKE '%$query%'";
    }
    
    if(!empty($category)) {
        $sql .= " AND category.category_name = '$category'";
    }
    
    if(!empty($fromPrice)) {
        $sql .= " AND item.price >= '$fromPrice'";
    }
    
    if(!empty($toPrice)) {
        $sql .= " AND item.price <= '$toPrice'";
    }
    
    if(!empty($order)) {
        if($order == 'byName') {
            $byThis = 'item.name';
        } else {
            $byThis = 'item.price';
        }
        
        $sql .= " ORDER BY $byThis";
    }
    
    $statement = $db->prepare($sql);
    
    $statement->execute();
    
    $records = $statement->fetchAll();
    
    return $records;
}


function insertItemIntoDB($items)
{
    if(!$items)
        return;
    $db= getDatabaseConnection();
     foreach($items as $item) {
                $itemName= $item['name'];
                $itemPrice= $item['salePrice'];
                $itemImage= $item['thumbnailImage'];
               
               
     
    $sql= "INSERT INTO Item (item_id, name, price, image_url) VALUES (NULL, :itemName, :itemPrice, :itemImage)";
    $statement= $db->prepare($sql);
    $statement->execute(array(
        itemName => $itemName,
        itemPrice => $itemPrice,
        itemImage => $itemImage));
   /* $db->exec($sql);*/
}
}

function addCategoriesForItems($itemStart, $itemEnd, $category_id){
 $db = getDatabaseConnection();   
for ($i = $itemStart; $i <= $itemEnd; $i++) {
        $sql = "INSERT INTO item_category (grouping_id, item_id, category_id) VALUES (NULL, '$i', '$category_id')";
        $db->exec($sql);
    }

}


function getCategoriesHTML() {
 $db = getDatabaseConnection(); 
 $categoriesHTML = "<option value=''></option>"; // User can opt to not select a category 

$sql = "SELECT category_name FROM category"; 

$statement = $db->prepare($sql); 

$statement->execute(); 

$records = $statement->fetchAll(PDO::FETCH_ASSOC); 

foreach ($records as $record) {
 $category = $record['category_name']; 
 $categoriesHTML .= "<option value='$category'>$category</option>"; 
 }
 
 return $categoriesHTML; 
}



?>
