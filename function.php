<?php

if (!defined('ACCESS')) {
    die('Not access');
}

ini_set('display_errors', true);
ini_set('display_startup_errors', true);

error_reporting(E_ALL);

mysqli_report(MYSQLI_REPORT_ERROR);

ob_start();

// Check require function
{
    $require = [
        'curl_init',
        'filter_var',
        'json_encode',
        'json_decode',
        'getenv'
    ];

    foreach ($require as $function) {
        if (!function_exists($function)) {
            exit($function . ' not found');
        }
    }
}


// tạo tmp nếu chưa có
{
	$tmp_dir = __DIR__ . '/tmp';
	$tmp_file = $tmp_dir . '/.htaccess';

	if (!is_dir($tmp_dir)) {
		mkdir($tmp_dir);
	}

	if (!file_exists($tmp_file)) {
		file_put_contents(
			$tmp_file,
			'deny from all'
		);
	}

	unset($tmp_dir);
	unset($tmp_file);
}

{
    $dir = getenv('SCRIPT_NAME');
    $dir = str_replace('\\', '/', $dir);
    $dir = strpos($dir, '/') !== false ? dirname($dir) : null;
    $dir = str_replace('\\', '/', $dir);
    $dir = $dir == '.' || $dir == '/' ? '' : $dir;

    $_SERVER['DOCUMENT_ROOT'] = realpath('.');
    $_SERVER['DOCUMENT_ROOT'] = $dir == null ? $_SERVER['DOCUMENT_ROOT'] : substr($_SERVER['DOCUMENT_ROOT'], 0, strlen($_SERVER['DOCUMENT_ROOT']) - strlen($dir));
    $_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);

    unset($dir);
}

define('REALPATH', realpath('./'));
const PATH_CONFIG = 'config.inc.php';
const PATH_DATABASE = 'database.inc.php';
const LOGIN_USERNAME_DEFAULT = 'Admin';
const LOGIN_PASSWORD_DEFAULT = '12345';
const PAGE_LIST_DEFAULT = 10000000;
const PAGE_FILE_EDIT_DEFAULT = 10000000;
const PAGE_FILE_EDIT_LINE_DEFAULT = 10000000;
const PAGE_DATABASE_LIST_ROWS_DEFAULT = 10000000;

const PAGE_NUMBER      = 7;
const PAGE_URL_DEFAULT = 'default';
const PAGE_URL_START   = 'start';
const PAGE_URL_END     = 'end';

const DEVELOPMENT          = false;
const NAME_SUBSTR          = 1000;
const NAME_SUBSTR_ELLIPSIS = '...';

const FM_COOKIE_NAME = 'fm_php';

{ // lay thong tin phien ban hien tai
    $version = json_decode(
        file_get_contents('version.json'),
        true
    );
    
    define('VERSION_MAJOR', $version['major']);
    define('VERSION_MINOR', $version['minor']);
    define('VERSION_PATCH', $version['patch']);
    define('VERSION_MESSAGE', $version['message']);
    
    unset($version);
}

{ // lay phien ban moi
    define('REMOTE_FILE', 'https://github.com/linh0804/linh0804/archive/main.zip');
	define('REMOTE_FILE_CURRENT', 'https://github.com/linh0804/linh0804/archive/refs/tags/' . VERSION_MAJOR . '.' . VERSION_MINOR . '.' . VERSION_PATCH . '.zip');
    define('REMOTE_DIR_IN_ZIP', 'file-manager-main');
    define('REMOTE_VERSION_FILE', 'https://raw.githubusercontent.com/linh0804/linh0804/main/version.json');

	$version = getNewVersion();
	$remoteFileNew = REMOTE_FILE_CURRENT;

	if ($version !== false) {
		$remoteFileNew = 'https://github.com/linh0804/linh0804/archive/refs/tags/' . $version['major'] . '.' . $version['minor'] . '.' . $version['patch'] . '.zip';
	}

	define('REMOTE_FILE_NEW', $remoteFileNew);

	unset($remoteFileNew);
	unset($version);
}

$configs = array();
$jsons   = null;

$pages = array(
    'current'     => 1,
    'total'       => 0,
    'paramater_0' => null,
    'paramater_1' => null
);

$formats = array(
    'image'    => array('png', 'ico', 'jpg', 'jpeg', 'gif', 'bmp'),
    'text'     => array('cpp', 'css', 'csv', 'h', 'htaccess', 'html', 'java', 'js', 'lng', 'pas', 'php', 'pl', 'py', 'rb', 'rss', 'sh', 'svg', 'tpl', 'txt', 'xml', 'ini', 'cnf', 'config', 'conf', 'conv'),
    'archive'  => array('7z', 'rar', 'tar', 'tarz', 'zip'),
    'audio'    => array('acc', 'midi', 'mp3', 'mp4', 'swf', 'wav'),
    'font'     => array('afm', 'bdf', 'otf', 'pcf', 'snf', 'ttf'),
    'binary'   => array('pak', 'deb', 'dat'),
    'document' => array('pdf'),
    'source'   => array('changelog', 'copyright', 'license', 'readme'),
    'zip'      => array('zip', 'jar'),
    'other'    => array('rpm', 'sql')
);

if (is_file(PATH_CONFIG)) {
    include PATH_CONFIG;
}


if (count($configs) == 0) {
    setcookie(FM_COOKIE_NAME, '', 0);
}

if (
    !isset($configs['username']) ||
    !isset($configs['password']) ||
    !isset($configs['page_list']) ||
    !isset($configs['page_file_edit']) ||
    !isset($configs['page_file_edit_line']) ||
    !isset($configs['page_database_list_rows'])
) {
    define('IS_CONFIG_UPDATE', true);
} else {
    define('IS_CONFIG_UPDATE', false);
}

if (!IS_CONFIG_UPDATE && (
        !preg_match('#\\b[0-9]+\\b#', $configs['page_list']) ||
        !preg_match('#\\b[0-9]+\\b#', $configs['page_file_edit']) ||
        !preg_match('#\\b[0-9]+\\b#', $configs['page_file_edit_line']) ||
        !preg_match('#\\b[0-9]+\\b#', $configs['page_database_list_rows']) ||

        empty($configs['username']) || $configs['username'] == null ||
        empty($configs['password']) || $configs['password'] == null)
) {
    define('IS_CONFIG_ERROR', true);
} else {
    define('IS_CONFIG_ERROR', false);
}

if (IS_CONFIG_UPDATE || IS_CONFIG_ERROR) {
    setcookie(FM_COOKIE_NAME, '', 0);
}

if (
    isset($configs['page_list'])
    && $configs['page_list'] > 0
    && isset($_GET['page_list'])
) {
    $pages['current'] = intval($_GET['page_list']) <= 0
        ? 1
        : intval($_GET['page_list']);

    if ($pages['current'] > 1) {
        $pages['paramater_0'] = '?page_list=' . $pages['current'];
        $pages['paramater_1'] = '&page_list=' . $pages['current'];
    }
}

$login = isset($_COOKIE[FM_COOKIE_NAME])
    && isset($configs['password'])
    && $_COOKIE[FM_COOKIE_NAME] === $configs['password'];

define('IS_LOGIN', $login);

function createConfig(
    $username = LOGIN_USERNAME_DEFAULT,
    $password = LOGIN_PASSWORD_DEFAULT,
    $pageList = PAGE_LIST_DEFAULT,
    $pageFileEdit = PAGE_FILE_EDIT_DEFAULT,
    $pageFileEditLine = PAGE_FILE_EDIT_LINE_DEFAULT,
    $pageDatabaseListRows = PAGE_DATABASE_LIST_ROWS_DEFAULT,
    $isEncodePassword = true
) {
    $content = "<?php if (!defined('ACCESS')) die('Not access'); else \$configs = array(";
    $content .= "'username' => '$username', ";
    $content .= "'password' => '" . ($isEncodePassword ? getPasswordEncode($password) : $password) . "', ";
    $content .= "'page_list' => '$pageList', ";
    $content .= "'page_file_edit' => '$pageFileEdit', ";
    $content .= "'page_file_edit_line' => '$pageFileEditLine',";
    $content .= "'page_database_list_rows' => '$pageDatabaseListRows',";
    $content .= '); ?>';

    if (is_file(PATH_CONFIG)) {
        unlink(PATH_CONFIG);
    }

    $put = file_put_contents(PATH_CONFIG, $content);

    if ($put) {
        return true;
    } else {
        $handler = @fopen(PATH_CONFIG, "w+");

        if ($handler) {
            if (@fwrite($handler, $content)) {
                @fclose($handler);
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    return true;
}


function createDatabaseConfig($host, $username, $password, $name, $auto)
{
    $content = "<?php if (!defined('ACCESS')) die('Not access'); else \$databases = array(";
        $content .= "'db_host' => '$host', ";
        $content .= "'db_username' => '$username', ";
        $content .= "'db_password' => '$password', ";
        $content .= "'db_name' => '$name', ";
        $content .= "'is_auto' => " . ($auto == true ? 'true' : 'false') . "";
    $content .= '); ?>';

    if (@is_file(REALPATH . '/' . PATH_DATABASE))
        @unlink(REALPATH . '/' . PATH_DATABASE);

    $put = @file_put_contents(REALPATH . '/' . PATH_DATABASE, $content);

    if ($put) {
        return true;
    } else {
        $handler = @fopen(REALPATH . '/' . PATH_DATABASE, "w+");

        if ($handler) {
            if (@fwrite($handler, $content))
                @fclose($handler);
            else
                return false;
        } else {
            return false;
        }
    }

    return true;
}

function isDatabaseVariable($array)
{
    return is_array($array) &&
                isset($array['db_host']) &&
                isset($array['db_username']) &&
                isset($array['db_password']) &&
                isset($array['db_name']) &&
                isset($array['is_auto']) &&
                empty($array['db_host']) == false && $array['db_host'] != null &&
                empty($array['db_username']) == false && $array['db_username'] != null;
}

function getNewVersion() {
    $remoteVersion = json_decode(@file_get_contents(REMOTE_VERSION_FILE), true);

    return is_array($remoteVersion) && isset($remoteVersion['message'])
        ? $remoteVersion
        : false;
}


function hasNewVersion() {
	return REMOTE_FILE_CURRENT !== REMOTE_FILE_NEW;
}


function goURL($url)
{
    header('Location:' . $url);
    exit(0);
}


function getPasswordEncode($pass)
{
    return md5(md5(trim($pass)));
}


function getFormat($name)
{
    return strrchr($name, '.') !== false
        ? strtolower(str_replace('.', '', strrchr($name, '.')))
        : '';
}


function isFormatText($name)
{
    global $formats;

    $format = getFormat($name);

    if ($format == null)
        return false;

    return in_array($format, $formats['text']) || in_array($format, $formats['other']) || in_array(strtolower(strpos($name, '.') !== false ? substr($name, 0, strpos($name, '.')) : $name), $formats['source']);
}


function isFormatUnknown($name)
{
    global $formats;

    $format = getFormat($name);

    if ($format == null)
        return true;

    foreach ($formats as $array)
        if (in_array($format, $array))
            return false;

    return true;
}

function str_replace_first($needle, $replace, $haystack) {
    $pos = strpos($haystack, $needle);

    if ($pos !== false) {
        return substr_replace($haystack, $replace, $pos, strlen($needle));
    }

    return $haystack;
}

function isURL($url)
{
    return filter_var($url, FILTER_VALIDATE_URL);
}


function processDirectory($var, $seSlash = false)
{
    if (empty($var)) {
        $var = '';
    }
    
    $var = str_replace('\\', '/', $var);
    $var = preg_replace('#/\./#', '//', $var);
    $var = preg_replace('#/\.\./#', '//', $var);
    $var = preg_replace('#/\.{1,2}$#', '//', $var);
    $var = preg_replace('|/{2,}|', '/', $var);
    $var = preg_replace('|(.+?)/$|', '$1', $var);

    // thêm / vào đầu và cuối
    if ($seSlash) {
        $var = trim($var, '/');
        $var = '/' . $var . '/';
    }

    return $var;
}


function processImport($url)
{
    if (!preg_match('|^http[s]?://(.+?)$|i', $url))
        $url = 'http://' . $url;

    return $url;
}


function processPathZip($var)
{
    if (empty($var)) {
        $var = '';
    }
    
    $var = str_replace('\\', '/', $var);
    $var = preg_replace('#/\./#', '//', $var);
    $var = preg_replace('#/\.\./#', '//', $var);
    $var = preg_replace('#/\.{1,2}$#', '//', $var);
    $var = preg_replace('|/{2,}|', '/', $var);
    $var = preg_replace('|/?(.+?)/?$|', '$1', $var);

    return $var;
}

function processName($var)
{
    $var = str_replace('/', '', $var);
    $var = str_replace('\\', '', $var);

    return $var;
}

function isNameError($var)
{
    return strpos($var, '\\') !== false || strpos($var, '/') !== false;
}

function rrmdir($path)
{
    $handler = @scandir($path);

    if ($handler !== false) {
        foreach ($handler as $entry) {
            if ($entry != '.' && $entry != '..') {
                $pa = $path . '/' . $entry;

                if (@is_file($pa)) {
                    if (!@unlink($pa))
                        return false;
                } elseif (@is_dir($pa)) {
                    if (!rrmdir($pa))
                        return false;
                } else {
                    return false;
                }
            }
        }

        return @rmdir($path);
    }

    return false;
}

function rrms($entrys, $dir)
{
    foreach ($entrys as $e) {
        $pa = $dir . '/' . $e;

        if (@is_file($pa)) {
            if (!@unlink($pa))
                return false;
        } elseif (@is_dir($pa)) {
            if (!rrmdir($pa))
                return false;
        } else {
            return false;
        }
    }

    return true;
}

function copydir($old, $new, $isParent = true)
{
    $handler = @scandir($old);

    if ($handler !== false) {
        if ($isParent && $old != '/') {
            $arr = explode('/', $old);
            $end = $new = $new . '/' . end($arr);

            if (@is_file($end) || (!@is_dir($end) && !@mkdir($end)))
                return false;
        } elseif (!$isParent && !@is_dir($new) && !@mkdir($new)) {
            return false;
        }

        foreach ($handler as $entry) {
            if ($entry != '.' && $entry != '..') {
                $paOld = $old . '/' . $entry;
                $paNew = $new . '/' . $entry;

                if (@is_file($paOld)) {
                    if (!@copy($paOld, $paNew))
                        return false;
                } elseif (@is_dir($paOld)) {
                    if (!copydir($paOld, $paNew, false))
                        return false;
                } else {
                    return false;
                }
            }
        }

        return true;
    }

    return false;
}

function copys($entrys, $dir, $path)
{
    foreach ($entrys as $e) {
        $pa = $dir . '/' . $e;

        if (isPathNotPermission(processDirectory($path . '/' . $e))) {
            /* Entry not permission */
        } elseif (@is_file($pa)) {
            if (!@copy($pa, $path . '/' . $e))
                return false;
        } elseif (@is_dir($pa)) {
            if (!copydir($pa, $path))
                return false;
        } else {
            return false;
        }
    }

    return true;
}

function movedir($old, $new, $isParent = true)
{
    $handler = @scandir($old);

    if ($handler !== false) {
        if ($isParent && $old != '/') {
            $s   = explode('/', $old);
            $end = $new = $new . '/' . end($s);

            if (@is_file($end) || (!@is_dir($end) && !@mkdir($end)))
                return false;
        } elseif (!$isParent && !@is_dir($new) && !@mkdir($new)) {
            return false;
        }

        foreach ($handler as $entry) {
            if ($entry != '.' && $entry != '..') {
                $paOld = $old . '/' . $entry;
                $paNew = $new . '/' . $entry;

                if (@is_file($paOld)) {
                    if (!@copy($paOld, $paNew))
                        return false;

                    @unlink($paOld);
                } elseif (@is_dir($paOld)) {
                    if (!movedir($paOld, $paNew, false))
                        return false;
                } else {
                    return false;
                }
            }
        }

        return @rmdir($old);
    }

    return false;
}

function moves($entrys, $dir, $path)
{
    foreach ($entrys as $e) {
        $pa = $dir . '/' . $e;

        if (isPathNotPermission(processDirectory($path . '/' . $e))) {
            /* Entry not permission */
        } elseif (@is_file($pa)) {
            if (!@copy($pa, $path . '/' . $e))
                return false;

            @unlink($pa);
        } elseif (@is_dir($pa)) {
            if (!movedir($pa, $path))
                return false;
        } else {
            return false;
        }
    }

    return true;
}


function copy_folder_recursive($source, $destination, $overwrite = true) {
    if (!is_dir($source)) {
        return false; // Source is not a directory
    }

    if (!file_exists($destination)) {
        mkdir($destination);
    }

    $dir = opendir($source);

    while ($file = readdir($dir)) {
        if ($file !== '.' && $file !== '..') {
            $src_file = $source . '/' . $file;
            $dst_file = $destination . '/' . $file;

            if (is_dir($src_file)) {
                copy_folder_recursive($src_file, $dst_file);
            } else {
                copy($src_file, $dst_file); // Overwrite existing files
            }
        }
    }

    closedir($dir);

    return true;
}


// chi dung de doc tat ca file
function readDirectoryIterator(
    $path,
    $excludes = []
) {
    $directory = new RecursiveDirectoryIterator(
        $path,
        FilesystemIterator::UNIX_PATHS
        | FilesystemIterator::SKIP_DOTS
    );
    
    $filter = new RecursiveCallbackFilterIterator($directory, function ($current, $key, $iterator) use ($path, $excludes) {
        $relative_path = str_replace_first($path, '', $current->getPathname());

        $excludes = array_map(function ($data) {
            return ltrim($data, '/');
        }, $excludes);
  
        foreach ($excludes as $exclude) {
            $exclude = trim($exclude);

            if (empty($exclude)) {
                continue;
            }

            $fileName = $current->getFilename();

            if (
                substr($exclude, -1) == '/'
                && $current->isDir()
            ) {
                $fileName .= '/';
            }

            if ($fileName === $exclude) {
                return false;
            }
        }
      
        return true;
    });

    return new RecursiveIteratorIterator(
        $filter,
        RecursiveIteratorIterator::SELF_FIRST
    );
}


function dirSize($path) {
    $size = 0;
    
    $files = readDirectoryIterator($path);

    foreach ($files as $file) {
        if ($file->isFile()) {
            $size += $file->getSize();
        }
    }
    
    return $size;
}


function zipdir($path, $file, $isDelete = false)
{
    require_once __DIR__ . '/lib/pclzip.class.php';

    if (@is_file($file))
        @unlink($file);

    $zip = new PclZip($file);

    if ($zip->add($path, PCLZIP_OPT_REMOVE_PATH, $path)) {
        if ($isDelete)
            rrmdir($path);

        return true;
    }

    return false;
}

function zips($dir, $entrys, $file, $isDelete = false)
{
    require_once __DIR__ . '/lib/pclzip.class.php';

    if (@is_file($file))
        @unlink($file);

    $zip = new PclZip($file);

    foreach ($entrys as $e)
        if (!$zip->add($dir . '/' . $e, PCLZIP_OPT_REMOVE_PATH, $dir))
            return false;

    if ($isDelete)
        rrms($entrys, $dir);

    return true;
}

function chmods($dir, $entrys, $folder, $file)
{
    $folder = intval($folder, 8);
    $file   = intval($file, 8);

    foreach ($entrys as $e) {
        $path = $dir . '/' . $e;

        if (@is_file($path)) {
            if (!@chmod($path, $file))
                return false;
        } elseif (@is_dir($path)) {
            if (!@chmod($path, $folder))
                return false;
        } else {
            return false;
        }
    }

    return true;
}

function size($size)
{
    if ($size < 1024)
        $size = $size . 'B';
    elseif ($size < 1048576)
        $size = round($size / 1024, 2) . 'KB';
    elseif ($size < 1073741824)
        $size = round($size / 1048576, 2) . 'MB';
    else
        $size = round($size / 1073741824, 2) . 'GB';

    return $size;
}

function grab($url, $ref = '', $cookie = '', $user_agent = '', $header = '')
{
    $ch = curl_init();

    $headers[] = 'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';
    $headers[] = 'Accept-Language: en-us,en;q=0.5';
    $headers[] = 'Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7';
    $headers[] = 'Keep-Alive: 300';
    $headers[] = 'Connection: Keep-Alive';
    $headers[] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';

    curl_setopt($ch, CURLOPT_URL, $url);

    if ($user_agent)
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
    else
        curl_setopt($ch, CURLOPT_USERAGENT, 'Nokia3110c/2.0 (04.91) Profile/MIDP-2.0 Configuration/CLDC-1.1');

    if ($header)
        curl_setopt($ch, CURLOPT_HEADER, 1);
    else
        curl_setopt($ch, CURLOPT_HEADER, 0);

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    if ($ref)
        curl_setopt($ch, CURLOPT_REFERER, $ref);
    else
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.google.com.vn/search?hl=vi&client=firefox-a&rls=org.mozilla:en-US:official&hs=hKS&q=video+clip&start=20&sa=N');

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

    if (strncmp($url, 'https', 6))
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    if ($cookie)
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);

    curl_setopt($ch, CURLOPT_TIMEOUT, 100);

    $html       = curl_exec($ch);
    $mess_error = curl_error($ch);

    curl_close($ch);

    return $html;
}

function import($url, $path)
{
    $binarys = file_get_contents($url);

    if (!file_put_contents($path, $binarys)) {
        @unlink($path);
        return false;
    }

    return true;
}

function page($current, $total, $url)
{
    $html                   = '<div class="page">';
    $center                 = PAGE_NUMBER - 2;
    $link                   = array();
    $link[PAGE_URL_DEFAULT] = $url[PAGE_URL_DEFAULT] ?? null;
    $link[PAGE_URL_START]   = $url[PAGE_URL_START] ?? null;
    $link[PAGE_URL_END]     = $url[PAGE_URL_END] ?? null;

    if ($total <= PAGE_NUMBER) {
        for ($i = 1; $i <= $total; ++$i) {
            if ($current == $i) {
                $html .= '<strong class="current">' . $i . '</strong>';
            } else {
                if ($i == 1)
                    $html .= '<a href="' . $link[PAGE_URL_DEFAULT] . '" class="other">' . $i . '</a>';
                else
                    $html .= '<a href="' . $link[PAGE_URL_START] . $i . $link[PAGE_URL_END] . '" class="other">' . $i . '</a>';
            }
        }
    } else {
        if ($current == 1)
            $html .= '<strong class="current">1</strong>';
        else
            $html .= '<a href="' . $link[PAGE_URL_DEFAULT] . '" class="other">1</a>';

        if ($current > $center) {
            $i = $current - $center < 1 ? 1 : $current - $center;

            if ($i == 1)
                $html .= '<a href="' . $link[PAGE_URL_DEFAULT] . '" class="text">...</a>';
            else
                $html .= '<a href="' . $link[PAGE_URL_START] . $i . $link[PAGE_URL_END] . '" class="text">...</a>';
        }

        $offset = array();

        {
            if ($current <= $center)
                $offset['start'] = 2;
            else
                $offset['start'] = $current - ($current > $total - $center ? $current - ($total - $center) : floor($center >> 1));

            if ($current >= $total - $center + 1)
                $offset['end'] = $total - 1;
            else
                $offset['end'] = $current + ($current <= $center ? ($center + 1) - $current : floor($center >> 1));
        }

        for ($i = $offset['start']; $i <= $offset['end']; ++$i) {
            if ($current == $i)
                $html .= '<strong class="current">' . $i . '</strong>';
            else
                $html .= '<a href="' . $link[PAGE_URL_START] . $i . $link[PAGE_URL_END] . '" class="other">' . $i . '</a>';
        }

        if ($current < $total - $center + 1)
            $html .= '<a href="' . $link[PAGE_URL_START] . ($current + $center > $total ? $total : $current + $center) . $link[PAGE_URL_END] . '" class="text">...</a>';

        if ($current == $total)
            $html .= '<strong class="current">' . $total . '</strong>';
        else
            $html .= '<a href="' . $link[PAGE_URL_START] . $total . $link[PAGE_URL_END] . '" class="other">' . $total . '</a>';
    }

    $html .= '</div>';

    return $html;
}

function getChmod($path)
{
    $perms = fileperms($path);

    if ($perms !== false) {
        $perms = decoct($perms);
        $perms = substr($perms, strlen($perms) == 5 ? 2 : 3, 3);
    } else {
        $perms = 0;
    }

    return $perms;
}

function jsonEncode($var)
{
    global $jsons;

    return json_encode($var);
}

function jsonDecode($var, $isAssoc = false)
{
    return json_decode($var, $isAssoc);
}

function countStringArray($array, $search, $isLowerCase = false)
{
    $count = 0;

    if ($array != null && is_array($array)) {
        foreach ($array as $entry) {
            if ($isLowerCase)
                $entry = strtolower($entry);

            if ($entry == $search)
                ++$count;
        }
    }

    return $count;
}

function isInArray($array, $search, $isLowerCase)
{
    if ($array == null || !is_array($array))
        return false;

    foreach ($array as $entry) {
        if ($isLowerCase)
            $entry = strtolower($entry);

        if ($entry == $search)
            return true;
    }

    return false;
}

function substring($str, $offset, $length = -1, $ellipsis = '')
{
    if ($str != null && strlen($str) > $length - $offset)
        $str = ($length == -1 ? substr($str, $offset) : substr($str, $offset, $length)) . $ellipsis;

    return $str;
}

function printPath($path, $isHrefEnd = false)
{
    $html = null;

    if ($path != null && $path != '/' && strpos($path, '/') !== false) {
        $array = explode('/', preg_replace('|^/(.*?)$|', '\1', $path));
        $item  = null;
        $url   = null;

        foreach ($array as $key => $entry) {
            if ($key === 0) {
                $seperator = preg_match('|^\/(.*?)$|', $path) ? '/' : null;
                $item      = $seperator . $entry;
            } else {
                $item = '/' . $entry;
            }

            if ($key < count($array) - 1 || ($key == count($array) - 1 && $isHrefEnd))
                $html .= '<span class="path_seperator">/</span><a href="index.php?dir=' . rawurlencode($url . $item) . '">';
            else
                $html .= '<span class="path_seperator">/</span>';

            $url  .= $item;
            $html .= '<span class="path_entry">' . substring($entry, 0, NAME_SUBSTR, NAME_SUBSTR_ELLIPSIS) . '</span>';

            if ($key < count($array) - 1 || ($key == count($array) - 1 && $isHrefEnd))
                $html .= '</a>';
        }
    }

    return $html;
}

function getPathPHP()
{
    if ($path = getenv('PATH')) {
        $array = @explode(strpos($path, ':') !== false ? ':' : PATH_SEPARATOR, $path);

        foreach ($array as $entry) {
            if (strstr($entry, 'php.exe') && isset($_SERVER['WINDIR']) && is_file($entry)) {
                return $entry;
            } else {
                $bin = $entry . DIRECTORY_SEPARATOR . 'php' . (isset($_SERVER['WINDIR']) ? '.exe' : null);

                if (is_file($bin))
                    return $bin;
            }
        }
    }

    return 'php';
}

function isFunctionExecEnable()
{
    return function_exists('exec')
        && isFunctionDisable('exec') == false;
}

function isFunctionDisable($func)
{
    $list = @ini_get('disable_functions');

    if (empty($list) == false) {
        $func = strtolower(trim($func));
        $list = explode(',', $list);

        foreach ($list as $e)
            if (strtolower(trim($e)) == $func)
                return true;
    }

    return false;
}

function debug($o)
{
    echo('<pre>');
    var_dump($o);
    echo('</pre>');
}


function asset($asset) {
    $lastModified = $filename = $asset;    
    if (file_exists($filename)) {
        $lastModified = $asset .'?t='. filemtime($filename);
    }
    return $lastModified;
}


include_once 'development.inc.php';

$dir       = isset($_GET['dir']) && !empty($_GET['dir']) ? rawurldecode($_GET['dir']) : null;
$name      = isset($_GET['name']) && !empty($_GET['name']) ? $_GET['name'] : null;
$dirEncode = $dir != null ? rawurlencode($dir) : null;

require_once 'permission.inc.php';
