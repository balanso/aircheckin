<?
$storeFolder = ROOT . '/public/img/aparts/' . $_POST['apart_id'] . '/';

$result = array();
if (!empty($_POST['apart_id'])) {

  $files = scandir($storeFolder); //1
  if (false !== $files) {
    foreach ($files as $file) {
      if ('.' != $file && '..' != $file) { //2
        $obj['apart_id'] = $_POST['apart_id'];
        $obj['name']     = $file;
        $obj['size']     = filesize($storeFolder . $file);
        $result[]        = $obj;
      }
    }
  }
}

header('Content-type: text/json'); //3
header('Content-type: application/json');
echo json_encode($result);