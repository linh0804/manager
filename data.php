<?php

define('ACCESS', true);

require 'update.class.php';
require_once __DIR__ . '/lib/pclzip.class.php';
include_once 'function.php';

define('FORMATS', $formats);

function remove_dir($dir = null) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir") remove_dir($dir."/".$object);
                else unlink($dir."/".$object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}
@session_start();

if (!IS_LOGIN) {
    goURL('login.php');
}

$title = 'Cập nhật';

include_once 'header.php';

$update = new Update();
$thisver = __DIR__ .'/tmp/thisversion';

echo '<div class="title">' . $title . '</div>';

$remoteVersion = getNewVersion();


if ($remoteVersion === false) {
    echo '<div class="list">Lỗi máy chủ cập nhật!</div>';
} else {
    if (isset($_POST['submit'])) {     
        if (
            !isset($_POST['token'])
            || !isset($_SESSION['token'])
            || $_POST['token'] != $_SESSION['token']
        ) {
            unset($_SESSION['token']);
            goURL('update.php');
        }
        

        if(!isset($_POST['select']) && !isset($_POST['all'])) {
          echo '<div class="list">Lựa chọn không chính xác!</div>';
        }

        if(isset($_POST['select']) && !isset($_POST['all'])) {
            $select = $_POST['select'];   
            echo '<div class="list">';  
            foreach ($select as $value) {
                $name = explode('/',$value);
                $lastElement = array_pop($name);
                $folder = __DIR__ .'/tmp/'. REMOTE_DIR_IN_ZIP . str_replace('/'. $lastElement,'',$value);
                $save = __DIR__ . str_replace('/'. $lastElement,'',$value);
                if($update->exec($lastElement, $folder, $save)) {
                      echo $lastElement . ' đã được cập nhật!<hr />';
                      @remove_dir($thisver .'/'. REMOTE_DIR_IN_ZIP);
                      $file = 'manager-' . time() . '.zip';                                         
                      import(REMOTE_FILE, $file);
                      $zip = new PclZip($file);
                      if (
                          $zip->extract(
                              PCLZIP_OPT_PATH,
                              $thisver,
                              PCLZIP_OPT_REPLACE_NEWER
                          ) != false
                      ) {
                          @unlink($file);
                      }
                } else {
                  echo  $lastElement . ' không được cập nhật!<hr />';       
                } 
            }
            echo '<a style="color:blue" href="/'. REMOTE_DIR_IN_ZIP .'">Trang chủ</a></div>';
        }

        if(isset($_POST['all'])) {
            copy_folder_recursive(__DIR__ . '/tmp/'. REMOTE_DIR_IN_ZIP, __DIR__);
            @remove_dir(__DIR__ .'/tmp/'. REMOTE_DIR_IN_ZIP);
            @remove_dir($thisver .'/'. REMOTE_DIR_IN_ZIP);
            $file = 'manager-' . time() . '.zip';           
            import(REMOTE_FILE, $file);    
            $zip = new PclZip($file);
            if (
                $zip->extract(
                    PCLZIP_OPT_PATH,
                    $thisver,
                    PCLZIP_OPT_REPLACE_NEWER
                ) != false
            ) {
                if(unlink($file)) {
                    goURL('/'. REMOTE_DIR_IN_ZIP);
                }
            }
        }
    } else {

        if (!hasNewVersion()) {
            echo '<div class="list">
                    Bạn đang sử dụng phiên bản manager mới nhất!<br />
                </div>';
        }

        if (!is_dir(__DIR__ . '/tmp')) {
          mkdir(__DIR__ . '/tmp', 0777);
        }
        $file = 'manager-' . time() . '.zip';

        if (!isset($_POST['submit']) && import(REMOTE_FILE, $file)) {
            $zip = new PclZip($file);
            if (
                $zip->extract(
                    PCLZIP_OPT_PATH,
                    __DIR__ .'/tmp',
                    PCLZIP_OPT_REPLACE_NEWER
                ) != false
            ) {
                @unlink($file);
               // $file_update = $update->get_changle('/tmp/'. REMOTE_DIR_IN_ZIP,__DIR__ . '/tmp/' . REMOTE_DIR_IN_ZIP);               
               // if(!isset($_POST['select']) && $file_update !== false) 
                   // echo '<div class="list">'. $file_update .'</div>';
                                               
            } else {
                echo '<div class="list">Lỗi! Không thể cài đặt bản cập nhật</div>';
            }
            
        } else {
            echo '<div class="list">Lỗi! Không thể tải bản  cập nhật</div>';
        }
        $token = time();
        $_SESSION['token'] = $token;
        $old = __DIR__;
        $new = __DIR__ . '/tmp/'. REMOTE_DIR_IN_ZIP;
        echo '<div class="list" style="padding:5px;font-size:xsmall;"><details>           
            <summary><b>Bảng thông tin file update!</b></summary><hr />';
        echo '<button style="display:none;" class="button" value="1" name="hidden_file" id="hidden_file" onClick="javascript:hidden_same();">Đóng file không update!</button><br />';
        echo '<form action="update.php" method="post">'.
        '<input type="hidden" name="token" value="' . $token . '" />';
        if($remoteVersion['major'] . '.' . $remoteVersion['minor'] . '.' . $remoteVersion['patch'] !== VERSION_MAJOR .'.'. VERSION_MINOR .'.'. VERSION_PATCH) {
            echo '<input type="submit" name="submit" value="Cập nhật file đã chọn"/>';
        }
        echo '<table style="display:block;white-space: nowrap;margin-top:0;overflow: scroll;margin: auto;" height="500px">
              <tbody>
                <thead>
                  <tr>
                    <td style="border-right:1px solid red;word-break: break-all;text-align:left;margin: 0; vertical-align: top;" width="50%"><b>Bản hiện tại</b><br />';
        echo $update->compareAll($old, $new,1);
        echo '</td>
          <td style="word-break: break-all;border-left:1px solid red;text-align:left;margin: 0; vertical-align: top;" width="50%"><b>Bản mới</b><br />';
        echo $update->compareAll($new, $old,2);
        echo '</td>
          </tr>
            </thead>
              </tbody>
                </table>
                  </form>
                    </details>
                      </div>';
        echo '<script>'.
          'const hidden_same = () => {
            var elements = document.getElementsByClassName("file");
            var parentElements = [];
            for (var i = 0; i < elements.length; i++) {
              var parentElement = elements[i].parentNode;
              if (!parentElements.includes(parentElement)) {
                parentElements.push(parentElement);
              }
            }
            for (var i = 0; i < elements.length; i++) {
              if (elements[i].classList.contains("fileSame")) {
                  elements[i].style.display = (elements[i].style.display === "none" || elements[i].style.display === "") ? "block" : "none";
              }
              for (var j = 0; j < parentElements.length; j++) {
                var allFileSameNone = true;
                var fileSameChildren = parentElements[j].getElementsByClassName("file");
                for (var k = 0; k < fileSameChildren.length; k++) {
                  if (fileSameChildren[k].style.display !== "none") {
                    allFileSameNone = false;
                    break;
                  }
                }
                var emptyClassElement = parentElements[j].getElementsByClassName("emptys")[0];
                if (emptyClassElement) {
                    emptyClassElement.style.display = allFileSameNone ? "block" : "none";
                }
              }
            }
            var button = document.querySelector("#hidden_file");
            if(button.value === \'0\') {
              var elements = document.getElementsByClassName("emptys");
              for (var i = 0; i < elements.length; i++) {
                elements[i].style.display = "none";
              }
            }
            button.innerText = button.value === \'1\' ? \'Mở file không update!\' : \'Đóng file không update!\';
            button.value = button.value === \'1\' ? \'0\' : \'1\';
          }
         // hidden_same();'.
        '</script>';
        if($remoteVersion['major'] . '.' . $remoteVersion['minor'] . '.' . $remoteVersion['patch'] !== VERSION_MAJOR .'.'. VERSION_MINOR .'.'. VERSION_PATCH) {
            echo '<div class="list">
                <span>Có phiên bản <b>' . $remoteVersion['major'] . '.' . $remoteVersion['minor'] . '.' . $remoteVersion['patch'] . '</b>, bạn có muốn cập nhật?</span><hr />
                <span>' . $remoteVersion['message'] . '</span><hr />
                <form action="update.php" method="post" name="form" onsubmit="return validateForm();">
                    <input type="hidden" name="all" value="1" />
                    <input type="hidden" name="token" value="' . $token . '" />
                    <input type="submit" name="submit" value="Cập nhật tất cả"/>
                    </form>
                </div>';
            echo '<script type="text/javascript">
                const validateForm = () => {
                  if (window.confirm(\'Bạn có muốn cập nhật tất cả, có thể làm mất những gì bạn sửa trong manager!\')) {
                    return true;
                  } else {
                    return false;                       
                  }
                }               
            </script>';
        }
    }
}

include_once 'footer.php';
