<?
// debug($_POST);
$content_tpl = [];
$content_data = [];

if (!empty($param1)) {
  $owners        = $db->getAll("SELECT * FROM owners");
  $update_fields = [];
  $message       = '';

  switch ($param1) {
    case 'add':
      $required_params = ['name', 'address', 'owner_id', 'wifi_name', 'wifi_pass', 'pms_id'];
      if (!empty($_POST['owner_id'])) {

        $params_checked = true;

        foreach ($required_params as $param) {
          if (!isset($_POST[$param]) || empty($_POST[$param])) {
            $message        = 'Параметр ' . $param . ' не получен';
            $params_checked = false;
            break;
          } else {
            $update_fields[$param] = $_POST[$param];
          }
        }

        if ($params_checked) {
          $db = get_db_connect();
          $db->query("INSERT INTO aparts SET ?u", $update_fields);
          $apart_id = $db->insertId();

          $tempFolder = ROOT . '/public/img/aparts/temp/';
          if (is_dir($tempFolder)) {
            $imagesFolder = ROOT . '/public/img/aparts/' . $apart_id . '/';
            rename($tempFolder, $imagesFolder);
          }

          $message = 'Добавлен новый апартамент ' . $_POST['name'];
          go('/admin/aparts/list');
        }
      }

      $content_tpl = '/views/admin/aparts/add_form.tpl';
      $content_data = ['owners' => $owners, 'message' => $message];
      break;

    case 'edit':
      if (isset($param2) && is_numeric($param2)) {
        $apart_id = $param2;
        $apart    = $db->getRow("SELECT * FROM aparts WHERE id=?i", $apart_id);

        if (!empty($apart)) {
          $required_params = ['name', 'address', 'owner_id', 'wifi_name', 'wifi_pass', 'pms_id'];

          if (isset($_POST['submit_delete'])) {
            $db->query("DELETE FROM aparts WHERE id=?i", $apart_id);
            $imagesFolder = ROOT . '/public/img/aparts/' . $apart_id . '/';

            $it = new RecursiveDirectoryIterator($imagesFolder, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it,
                         RecursiveIteratorIterator::CHILD_FIRST);
            foreach($files as $file) {
                if ($file->isDir()){
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            rmdir($imagesFolder);

            go('/admin/aparts/list');
            exit($message);

          } else {
            if (!empty($_POST['owner_id'])) {
              $params_checked = true;

              foreach ($required_params as $param) {
                if (!isset($_POST[$param]) || empty($_POST[$param])) {
                  $message        = 'Параметр ' . $param . ' не получен';
                  $params_checked = false;
                  break;
                } else {
                  $update_fields[$param] = $_POST[$param];
                }
              }

              if ($params_checked) {
                $db = get_db_connect();
                $db->query("UPDATE aparts SET ?u WHERE id=?i", $update_fields, $apart_id);
                $apart    = array_merge($apart, $update_fields);
                $apart_id = $db->insertId();

                $message = 'Отредактирован апартамент ' . $_POST['name'];
                go('/admin/aparts/list');
              }
            }
          }

          $content_tpl = '/views/admin/aparts/edit_form.tpl';
          $content_data = ['apart' => $apart, 'owners' => $owners, 'message' => $message];
        } else {
          debug('Не найден апартамент с ID ' . $apart_id);
        }
      } else {
        debug('Для редактирования необходимо указать id апартамента');
      }
      break;

    case 'list':
      $content_tpl = '/views/admin/aparts/table.tpl';
      break;

    default:
      # code...
      break;
  }
}



load_tpl('/views/admin/template/header.tpl', ['show_menu' => true]);
load_tpl($content_tpl, $content_data);
load_tpl('/views/admin/template/footer.tpl');
