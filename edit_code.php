<?php

define('ACCESS', true);

include_once 'function.php';

if (!IS_LOGIN) {
    goURL('login.php');
}

$title = 'Sửa tập tin dạng Code';

include_once 'header.php';

echo '<div class="tips" style="margin-top: 0 !important">
    <img src="icon/tips.png" alt="">
    Nếu không thấy nội dung file, vui lòng không chỉnh sửa trên web!
</div>';

echo '<div class="title">' . $title . '</div>';

if ($dir == null || $name == null || !is_file(processDirectory($dir . '/' . $name))) {
    echo '<div class="list"><span>Đường dẫn không tồn tại</span></div>
        <div class="title">Chức năng</div>
        <ul class="list">
            <li><img src="icon/list.png"/> <a href="index.php' . $pages['paramater_0'] . '">Danh sách</a></li>
        </ul>';
} else if (!isFormatText($name) && !isFormatUnknown($name)) {
    echo '<div class="list"><span>Tập tin này không phải dạng văn bản</span></div>
        <div class="title">Chức năng</div>
        <ul class="list">
            <li><img src="icon/list.png"/> <a href="index.php?dir=' . $dirEncode . $pages['paramater_1'] . '">Danh sách</a></li>
        </ul>';
} else {
    $dir = processDirectory($dir);
    $path = $dir . '/' . $name;
    $content = file_get_contents($path);
    $actionEdit = 'edit_api.php?dir=' . $dirEncode . '&name=' . $name;
    $fileExt = getFormat($name);

    $codeLang = 'text';
    $codeType = [
        'text' => 'Text',
        'php' => 'PHP',
        'javascript' => 'JavaScript',
    ];

    if (array_key_exists($fileExt, $codeType)) {
        $codeLang = $fileExt;
    }

    echo '<div class="list">
        <span>' . printPath($dir, true) . '</span><hr/>
        <div class="ellipsis break-word">
            <span class="bull">&bull;</span>
            Tập tin:
            <strong class="file_name_edit">' . $name . '</strong><hr />
            <div>
                <a href="edit_text.php?dir=' . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">
                    <button class="button">Chế độ sửa văn bản</button>
                </a><hr />
             </div>
            <div class="code_action">
                Loại code:
                <select id="code_lang">';

    foreach ($codeType as $cType => $cValue) {
        $cSeleted = $codeLang == $cType ? 'selected="selected"' : '';
        echo "<option {$cSeleted} value=\"{$cType}\">{$cValue}</option>";
    }

    // <input type="checkbox" checked="checked" id="code_readonly" /> ReadOnly
    echo '</select>
            <span style="float: right">
                <input type="checkbox" id="code_wrap" /> Wrap
            </span>
            </div>
            <hr/>
        </div>

        <form id="code_form" action="javascript:void(0)">
            <div>
                <textarea id="content" style="display: none">' . PHP_EOL . htmlspecialchars($content) . '</textarea>
                <div id="editor"></div>
            </div>
            <div class="input_action">
                <input type="submit" value="Lưu lại" />
                <span style="margin-right: 12px"></span>
                <input type="checkbox" id="code_check_php" /> Kiểm tra lỗi PHP
            </div>
        </form>
        </div>
        <div id="code_check_message" class="list">
        </div>

        <style type="text/css" media="screen">
            @media (min-width: 240px) {
                .cm-editor {
                    height: 320px;
                }
            }
            
            @media (min-width: 320px) {
                .cm-editor {
                    height: 480px;
                }
            }
            
            @media (min-width: 480px) {
                .cm-editor {
                    height: 640px;
                }
            }
            
            @media (min-width: 640px) {
                .cm-editor {
                    height: 720px;
                }
            }

			.cm-focused .cm-selectionBackground,
			.cm-selectionBackground,
			.cm-content ::selection {
				background-color: #4a4a4a !important;
			}

			.cm-activeLine.cm-line::selection,
			.cm-activeLine.cm-line ::selection {
				background-color: #8a8a8a !important;
			}
        </style>';
        
		echo '<script src="' . asset('edit_code.bundle.js') . '"></script>';
        echo '<script>
            const codeCheckMessageElement = document.getElementById("code_check_message");
            const codeCheckPHPElement = document.getElementById("code_check_php");
            
            var codeFormElement = document.getElementById("code_form");
            codeFormElement.addEventListener("submit", function (event) {
                var data = new FormData();
                data.append("requestApi", 1);
                data.append("content", editor.state.doc.toString());
                
                codeCheckMessageElement.innerHTML = "";
                if (codeCheckPHPElement.checked) {
                    data.append("check", 1);
                } else {
                    data.append("check", 0);
                }

                fetch("' . $actionEdit . '", {
                    method: "POST",
                    body: data,
                    cache: "no-cache"
                }).then(function (response) {
                    if (response.status != 200) {
                        alert("Lỗi kết nối!");
                        return false;
                    }
                    
                    return response.json();
                }).then((data) => {
                    alert(data.message)

                    if (data.error) {
                        codeCheckMessageElement.innerHTML = data.error;
                    }
                });

                event.preventDefault();
                return false;
            });
        </script>

        <div class="title">Chức năng</div>
        <ul class="list">
            <li><img src="icon/info.png"/> <a href="file.php?dir='      . $dirEncode . '&name=' . $name . $pages['paramater_1'] . '">Thông tin</a></li>
        </ul>';
}

include_once 'footer.php';

