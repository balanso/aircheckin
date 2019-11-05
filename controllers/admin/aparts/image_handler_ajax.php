<?
$ds = DIRECTORY_SEPARATOR;
require_once ROOT .'/lib/images.php';

if (isset($_GET['action'])) {
  if (!empty($_GET['apart_id'])) {
    $apart_id = $_GET['apart_id'];
  } else {
    $apart_id = 'temp';
  }

  $storeFolder = ROOT . '/public/img/aparts/' . $apart_id . '/';
  $thumbsFolder = ROOT . '/public/img/aparts/' . $apart_id . '/small/';

  if (!is_dir($thumbsFolder)) {
    mkdir($thumbsFolder, 0777, true);
  }

  if ($_GET['action'] == 'upload' && !empty($_FILES)) {
    $tempFile   = $_FILES['file']['tmp_name'];
    $filename = $_FILES['file']['name'];
    $targetPath = $storeFolder;
    $targetFile = $targetPath . $filename;
    move_uploaded_file($tempFile, $targetFile);

    createThumbnail($targetFile, $thumbsFolder.$filename, 200);
  }

  if ($_GET['action'] == 'get') {
    $result = array();
    $files  = scandir($storeFolder);

    if (false !== $files) {
      foreach ($files as $file) {
        if ('.' != $file && '..' != $file && is_file($storeFolder . $file)) {
          $obj['apart_id'] = $apart_id;
          $obj['name']     = $file;
          $obj['size']     = filesize($storeFolder . $file);
          $result[]        = $obj;
        }
      }
    }

    header('Content-type: text/json');
    header('Content-type: application/json');
    echo json_encode($result);
  }

  if ($_GET['action'] == 'remove') {
    // Remove file
    $filename = $storeFolder . $_POST['name'];
    unlink($filename);

    if (is_file($thumbsFolder . $_POST['name'])) {
      unlink($thumbsFolder . $_POST['name']);
    }
    exit;
  }
}
