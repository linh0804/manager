<?php
    define('ACCESS', true);
    require_once 'function.php';
    header('Content-type: application/json; charset=utf-8');

    
    $data = [
        "search" => [],
        "total" => 0,
        "post" => json_encode($_POST)
    ];
    $search = isset($_POST['search']) ? $_POST['search'] : '';
    $total = 0;
    if($dir == null) {
        $data['search'][] = '<li class="normal">Danh sách trống</li>';
        echo json_encode($data);
        exit;
    }
    if(IS_LOGIN && !empty($search)) {
        $dir = processDirectory($dir);
        $files = readDirectoryIterator($dir,[
            '.git/',
            'node_modules/',
            'vendor/'
        ]);

        foreach ($files as $file) {
            $file_name = $file->getFilename();
            $file_path = $file->getPathname();
            $file_path = processDirectory($file_path);
            $file_path_sort = str_replace($dir, '', $file_path);
            $file_path_sort = ltrim($file_path_sort, '/');
            if (!empty($file_name) && stripos($file_name, $search) !== false) {
                if ($file->isDir()) {
                    $data['search'][] = '<li class="folder"><img src="icon/folder.png"/>'. str_replace($search, '<font color="red">' . $search . '</font>', $file_name) .'</li>';
                }
                if ($file->isFile()) {

                    $icon   = 'unknown';
                    $type   = getFormat($file_name);
                    if (in_array($type, $formats['other'])) {
                        $icon = $type;
                    } elseif (in_array($type, $formats['text'])) {
                        $icon   = $type;
                        $isEdit = true;
                    } elseif (in_array($type, $formats['archive'])) {
                        $icon = $type;
                    } elseif (in_array($type, $formats['audio'])) {
                        $icon = $type;
                    } elseif (in_array($type, $formats['font'])) {
                        $icon = $type;
                    } elseif (in_array($type, $formats['binary'])) {
                        $icon = $type;
                    } elseif (in_array($type, $formats['document'])) {
                        $icon = $type;
                    } elseif (in_array($type, $formats['image'])) {
                        $icon = 'image';
                    } elseif (in_array(strtolower(strpos($file_name, '.') !== false ? substr($file_name, 0, strpos($file_name, '.')) : $file_name), $formats['source'])) {
                        $icon   = strtolower(strpos($file_name, '.') !== false ? substr($file_name, 0, strpos($file_name, '.')) : $file_name);
                    } elseif (isFormatUnknown($file_name)) {
                        $icon   = 'unknown';
                    }
                    $data['search'][] = '<li class="file"><img src="icon/mime/' . $icon . '.png"/>'. str_replace($search, '<font color="red">' . $search . '</font>', $file_name) .'</li>';
                }
                $total++;
            }
        }
    }
    if(!$total) {
        $data['search'][] = '<li class="normal">Danh sách trống</li>';
        $total++;
    }
    $data['total'] = (int)$total;
    echo json_encode($data);