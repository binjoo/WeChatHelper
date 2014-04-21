<?php
include_once 'common.php';
include 'header.php';
include 'menu.php';
$siteUrl = Helper::options()->siteUrl;
?>
<div class="main">
    <div class="body container">
        <div class="typecho-page-title">
            <h2><?php _e($menu->title);?></h2>
        </div>
        <div class="row typecho-page-main">
            <div class="col-mb-12 col-tb-8" role="main">
                <div class="typecho-list-operate clearfix">
                    <div class="operate">
                        <form action="<?php _e($siteUrl.'action/WeChat?users&do=syncList') ?>" method="post">
                            <button class="btn dropdown-toggle btn-s" type="submit">同步微信关注者数据</button>
                        </form>
                    </div>
                </div>

                <div class="typecho-table-wrap">
                    <table class="typecho-list-table">
                        <colgroup>
                            <col width="15%">
                            <col width="25%">
                            <col width="25%">
                            <col width="25%">
                            <col width="10%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th></th>
                                <th><?php _e('标题'); ?></th>
                                <th><?php _e('类型'); ?></th>
                                <th><?php _e('Key / URL'); ?></th>
                                <th><?php _e('操作'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php Typecho_Widget::widget('WeChatHelper_Widget_Menus')->to($menus);?>
                            <?php if($menus->have()): ?>
                                <?php while ($menus->next()): ?>
                                    <tr id="menus-mid-<?php $menus->mid(); ?>" style="cursor: move;<?php _e($menus->tr) ?>" >
                                        <td><?php _e($menus->levelVal) ?></td>
                                        <td><a href="<?php _e(Helper::url('WeChatHelper/Page/Menus.php').'&mid='.$menus->mid) ?>"><?php _e($menus->name) ?></a></td>
                                        <td><?php _e($menus->type) ?></td>
                                        <td><?php _e($menus->value) ?></td>
                                        <td><input type="checkbox" value="<?php $menus->mid(); ?>" name="mid[]" style="display: none"/><?php _e('操作'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align:center"><?php _e('没有任何用户'); ?></td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-mb-12 col-tb-4" role="form">
                <?php Typecho_Widget::widget('WeChatHelper_Widget_Menus')->form()->render(); ?>
            </div>
        </div>
    </div>
</div>
<?php
include 'copyright.php';
include 'common-js.php';
?>

<script type="text/javascript">
(function () {
    $(document).ready(function () {
        var table = $('.typecho-list-table').tableDnD({
            onDrop : function () {
                var ids = [];

                $('input[type=checkbox]', table).each(function () {
                    ids.push($(this).val());
                });

                $.post('<?php $security->index('/action/WeChat?menus&do=order'); ?>', $.param({mid : ids}));

                $('tr', table).each(function (i) {
                    if (i % 2) {
                        $(this).addClass('even');
                    } else {
                        $(this).removeClass('even');
                    }
                });
            }
        });
    })
})();
</script>
<?php include 'footer.php';?>