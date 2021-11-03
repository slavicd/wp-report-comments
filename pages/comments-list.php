<?php
/**
 * @var array $comments
 * @var int $count
 */

if (!defined('ABSPATH')) {
    die(__('Cheatin&#8217; uh?'));
}

?>
<div class="wrap">
    <div id="icon-edit-comments" class="icon32"></div>
    <h2><?php echo $this->strings['page_title'] ?></h2><br />

    <?php if ($count>sizeof($comments)): ?>
        <p>The following list was truncated to 1000 comments.</p>
    <?php endif; ?>

    <table class="widefat fixed comments" cellspacing="0">
        <thead>
        <tr>
            <th class="column-author"><?php _e('Author') ?></th>
            <th class="column-comment"><?php _ex('Comment', 'column name') ?></th>
            <th class="column-response"><?php _ex('In Response To', 'column name') ?></th>
            <th class="column-response">Report Count</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($comments as $comment) {
            $delete_nonce = wp_create_nonce('delete-comment_' . $comment->comment_id);
            $ignore_nonce = wp_create_nonce('ignore-report_' . $comment->comment_id);
            $post_link = get_permalink($comment->comment_post_ID);
            $post_title = get_the_title($comment->comment_post_ID);

            ?>
            <tr id="comment-<?php echo $comment->comment_id ?>">
                <td class="author column-author">
                    <strong><?php echo $comment->comment_author ?></strong><br />
                    <a href="mailto:<?php echo $comment->comment_author_email ?>"><?php echo $comment->comment_author_email ?></a><br />
                    <a href="edit-comments.php?s=<?php echo $comment->comment_author_IP ?>&mode=detail"><?php echo $comment->comment_author_IP ?></a>
                </td>
                <td class="comment column-comment">
                    <div class="submitted-on">
                        <?php printf(__('Submitted on <a href="%1$s">%2$s at %3$s</a>'),
                            $post_link. '#comment-' .$comment->comment_id,
                            get_comment_date(__( 'Y/m/d' ), $comment->comment_id),
                            get_comment_date(get_option('time_format'), $comment->comment_id));
                        ?>
                    </div>
                    <p><?php echo htmlspecialchars($comment->comment_content) ?></p>
                    <div class="row-actions">
                        <span class="green"><a href="admin.php?action=<?php echo $this->pluginPrefix ?>_ignore&c=<?php echo $comment->comment_id ?>&_wpnonce=<?php echo $ignore_nonce ?>"><?php echo $this->strings['ignore_report']; ?></a></span> |
                        <span><a href="comment.php?c=<?php echo $comment->comment_id ?>&action=editcomment"><?php _e('Edit') ?></a></span> |
                        <span class="spam"><a href="comment.php?c=<?php echo $comment->comment_id ?>&action=spamcomment&_wpnonce=<?php echo $delete_nonce ?>"><?php _ex('Spam', 'verb') ?></a></span> |
                        <!--						<span class="delete"><a href="comment.php?c=--><?php //echo $comment->comment_id ?><!--&action=trash&_wpnonce=--><?php //echo $delete_nonce ?><!--">--><?php //_ex('Trash', 'verb') ?><!--</a></span>-->
                        <span class="delete"><a href="admin.php?c=<?php echo $comment->comment_id ?>&action=<?php echo $this->pluginPrefix ?>_trash&_wpnonce=<?php echo $delete_nonce ?>"><?php _ex('Trash', 'verb') ?></a></span>
                    </div>
                </td>
                <td class="response column-response">
                    <a href="<?php echo $post_link ?>"><?php echo $post_title ?></a>
                </td>

                <td>
                    <?php echo $comment->meta_value ?>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
