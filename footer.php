<?php if (!defined('ACCESS')) die('Not access'); ?>

<?php if (IS_LOGIN) { ?>
<?php
    require __DIR__ . '/lib/bookmark.class.php';

    define('BOOKMARK_FILE', __DIR__ . '/bookmark.json');

    $Bookmark = new Bookmark(BOOKMARK_FILE);
    
    $add_bookmark = isset($_GET['add_bookmark']) ? trim($_GET['add_bookmark']) : '';
    if (!empty($add_bookmark)) {
        $add_bookmark = rawurldecode($add_bookmark);
        
        if (is_dir($add_bookmark)) {
            $Bookmark->add($add_bookmark);
            goURL('index.php?dir=' . rawurlencode($add_bookmark));
        }
    }

    $delete_bookmark = isset($_GET['delete_bookmark']) ? trim($_GET['delete_bookmark']) : '';
    if (!empty($delete_bookmark)) {
        $Bookmark->delete(rawurldecode($delete_bookmark));
        goURL('index.php');
    }
    
    $bookmarks = array_reverse($Bookmark->get());
?>

<style>
    ul.list li {
        white-space: normal;
        font-size: small;
    }
</style>
<div class="title">Bookmark</div>
<ul class="list">
    <?php if (
        !empty($dir)
        && is_dir(processDirectory($dir))
    ) { ?>
    <li>
        <img src="icon/create.png" />
        <a href="index.php?add_bookmark=<?php echo rawurlencode($dir); ?>">
            Thêm thư mục hiện tại
        </a>
    </li>
    <?php } ?>

    <?php foreach ($bookmarks as $bookmark) { ?>
    <li>
        <img src="icon/folder.png" />
        <a href="index.php?dir=<?php echo rawurlencode($bookmark); ?>">
            <?php echo htmlspecialchars($bookmark); ?>
        </a>
        <a href="index.php?delete_bookmark=<?php echo rawurlencode($bookmark); ?>">
            <span style="color: red">[X]</span>
        </a>
    </li>
    <?php } ?>
</ul>
<?php } // IS_LOGIN ?>

</div>

<div id="footer">
    <span>
		ngatngay cooperation with linh
	</span><br />
    <span>Version: <?php echo VERSION_MAJOR; ?>.<?php echo VERSION_MINOR; ?>.<?php echo VERSION_PATCH; ?></span>
</div>
<div id="menuOverlay"></div>
<div
    id="scroll"
    class="scroll-to-top scroll-to-top-icon"
    style="display: block; visibility: visible; opacity: 0.5; display: none;"
></div>

<script src="<?= asset('script.js') ?>"></script>

</body>
</html>

<?php ob_end_flush(); ?>