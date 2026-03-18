<?php
require_once __DIR__."/../../../config/db.php";

$service_id = $_GET["service_id"] ?? 0;

/* ADD BLOCK */

if(isset($_POST["add_block"])){

$type = $_POST["block_type"];

$stmt = $pdo->prepare("
INSERT INTO service_blocks
(service_id,block_type,sort_order)
VALUES (?,?,999)
");

$stmt->execute([$service_id,$type]);

header("Location: services_blocks.php?service_id=".$service_id);
exit;

}

/* UPDATE BLOCK */

if(isset($_POST["save_block"])){

$stmt = $pdo->prepare("
UPDATE service_blocks
SET
title=?,
content=?,
video_url=?
WHERE id=?
");

$stmt->execute([
$_POST["title"],
$_POST["content"],
$_POST["video_url"],
$_POST["block_id"]
]);

}

/* DELETE */

if(isset($_GET["delete"])){

$stmt=$pdo->prepare("DELETE FROM service_blocks WHERE id=?");
$stmt->execute([$_GET["delete"]]);

header("Location: services_blocks.php?service_id=".$service_id);
exit;

}

/* LOAD BLOCK */

$stmt=$pdo->prepare("
SELECT *
FROM service_blocks
WHERE service_id=?
ORDER BY sort_order,id
");

$stmt->execute([$service_id]);

$blocks=$stmt->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">

<title>Page Builder</title>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<style>

body{
font-family:Arial;
padding:40px;
background:#f5f5f5;
}

.builder{
max-width:900px;
margin:auto;
}

.block{
background:#fff;
border:1px solid #ddd;
margin-bottom:15px;
padding:20px;
}

textarea{
width:100%;
height:120px;
}

input{
width:100%;
padding:8px;
margin:5px 0;
}

select{
padding:8px;
}

button{
padding:8px 14px;
}

.drag{
cursor:move;
color:#888;
}

</style>

</head>

<body>

<div class="builder">

<h2>Service Page Builder</h2>

<a href="services_cms.php">← Back</a>

<hr>

<h3>Add Block</h3>

<form method="post">

<select name="block_type">

<option value="heading">Heading</option>
<option value="text">Text</option>
<option value="image">Image</option>
<option value="video">YouTube</option>

</select>

<button name="add_block">Add Block</button>

</form>

<hr>

<div id="blocks">

<?php foreach($blocks as $b): ?>

<div class="block" data-id="<?= $b["id"] ?>">

<div class="drag">☰ drag</div>

<form method="post">

<input type="hidden" name="block_id" value="<?= $b["id"] ?>">

<strong><?= $b["block_type"] ?></strong>

<?php if($b["block_type"]=="heading"): ?>

<input
name="title"
value="<?= htmlspecialchars($b["title"]) ?>"
placeholder="Heading"
>

<?php endif ?>

<?php if($b["block_type"]=="text"): ?>

<textarea name="content"><?= htmlspecialchars($b["content"]) ?></textarea>

<?php endif ?>

<?php if($b["block_type"]=="image"): ?>

<input type="file" name="image">

<?php if($b["image"]): ?>

<img
src="/assets/upload/services/<?= $b["image"] ?>"
style="max-width:200px"
>

<?php endif ?>

<?php endif ?>

<?php if($b["block_type"]=="video"): ?>

<input
name="video_url"
value="<?= htmlspecialchars($b["video_url"]) ?>"
placeholder="YouTube embed URL"
>

<?php endif ?>

<br><br>

<button name="save_block">Save</button>

<a href="?service_id=<?= $service_id ?>&delete=<?= $b["id"] ?>">
Delete
</a>

</form>

</div>

<?php endforeach ?>

</div>

</div>

<script>

new Sortable(document.getElementById("blocks"),{
animation:150
})

</script>

</body>
</html>