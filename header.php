<?php if (!defined('ACCESS')) die('Not access'); ?>
<?php $exclude = isset($_POST['exclude']) ? $_POST['exclude'] : '.git/' . PHP_EOL . 'node_modules/' . PHP_EOL . 'vendor/'; ?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <title><?php echo $title; ?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="<?php echo asset('style.css') ?>" media="all,handheld" />
    <link rel="icon" type="image/png" href="icon/icon.png">
    <link rel="icon" type="image/x-icon" href="icon/icon.ico" />
    <link rel="shortcut icon" type="image/x-icon" href="icon/icon.ico" />
</head>
<body>

<div id="header">
    <div class="nav-menu">
        <div id="nav-menu">
            <div id="menu"></div>
        </div>
    </div>
    <?php if(IS_LOGIN): ?>
        <form id="search_form" action="search_api.php?dir=<?=$dir?>" style="display:inline-block;text-align: center;" method="post">
            <input type="text" id="input_search" name="search" value="<?= (isset($_POST['search']) ? htmlspecialchars(ltrim($_POST['search'], '/')) : '') ?>" placeholder="Tìm kiếm" style="border-radius:10px 0 0 10px;width: 65%;margin:0; display:inline-block;" />
            <input type="hidden" name="only_file" value="1" />
            <textarea name="exclude" rows="5" style="display:none"><?= htmlspecialchars($exclude) ?></textarea>
            <input type="submit" id="submit_search" name="submit" value="Tìm kiếm" style="border-radius:0 10px 10px 0;display:inline-block;margin-left:-10px;"/>
        </form>
    <?php endif; ?>
</div>
        <div class="menuToggle">
            <ul class="menuContent">
                <li><a href="index.php"><img src="icon/home.png" /> Trang chủ</a></li><br />
                <?php if (!IS_INSTALL_ROOT_DIRECTORY && IS_LOGIN) { ?>
                    <?php if (!defined('IS_CONNECT')) { ?>
                        <li><a href="database.php"><img src="icon/database.png"/> Cơ sở dữ liệu</a></li><br />
                    <?php } else { ?>
                        <li><a href="database_disconnect.php"><img src="icon/disconnect.png"/> Thoát cơ sở dữ liệu</a></li><br />
                    <?php } ?>
                    <li><a href="setting.php"><img src="icon/setting.png" /> Cài đặt</a></li><br />
                    <li><a href="logout.php"><img src="icon/exit.png" /> Đăng xuất</a></li>
                <?php } ?>
            </ul>
        </div>

<div id="container">
    <?php if (IS_LOGIN && hasNewVersion()) { ?>
        <div class="tips" style="margin-top: 0 !important">
            <img src="icon/tips.png" alt="">
            Có phiên bản mới! <a href="update.php"><span style="font-weight: bold; font-style: italic">Cập nhật</span></a> ngay!
        </div>
    <?php } ?>
