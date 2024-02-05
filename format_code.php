<?php
    $tempFile = __DIR__ .'/fixer.txt'; 
    $cacheFile = __DIR__ .'/.php-cs-fixer.cache';  
    $data = array(
      'highlight' => '',
      'error' => 'Không thành công! yêu cầu cài php-cs-fixer vào manager'
    );
    $content = isset($_POST['content']) ? $_POST['content'] : null;
    if(isset($content) && isset($_POST['requestApi'])){       
        file_put_contents($tempFile, $content);
        if(exec('cd '. __DIR__ .' && vendor/bin/php-cs-fixer fix fixer.txt')){
            $data['highlight'] = file_get_contents($tempFile);
            $data['error'] = '';
            @unlink($tempFile);
            @unlink($cacheFile);
        } 
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
