<?php
$alternate = false;
?>
<div class="wrap">
    <h2><?php _e( 'DND Views Report', 'author-page-views' ); ?></h2>
    <p id="date-range">
        <form method="GET" action="">
            <input type="hidden" name="page" value="<?php echo $_GET['page']; ?>" />
            <label><?php _e( 'Show report from:', 'author-page-views' ); ?></label> <?php self::show_date_dropdown(self::get_date_begin(), 'begin' ); ?>
            <label><?php _e( 'To:', 'author-page-views' ); ?></label> <?php self::show_date_dropdown(self::get_date_end(), 'end' ); ?>
            <input type="submit" class="button" value="Filter" />
        </form>
    </p>
    <table class="wp-list-table widefat fixed">
        <thead>
            <tr>
                <th style="width: 70px"><?php _e( 'Post ID', 'author-page-views' ); ?></th>
                <th><?php _e( 'Post Title', 'author-page-views' ); ?></th>
                <th style="width: 150px"><?php _e( 'Views', 'author-page-views' ); ?></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th><?php _e( 'Post ID', 'author-page-views' ); ?></th>
                <th><?php _e( 'Post Title', 'author-page-views' ); ?></th>
                <th><?php _e( 'Views', 'author-page-views' ); ?></th>
            </tr>
        </tfoot>
        <?php foreach ( $page_views as $page_id => $page_details ) : ?>
        <tr class="<?php echo ($alternate = !$alternate) ? 'alternate' : ''; ?>">
            <td><?php echo $page_id; ?></td>
			<td><a href="<?php echo get_page_link($page_id) ?>" target="_blank" title="open preview in a new tab"><?php echo $page_details['pTitle']; ?></a></td>
            <td><?php echo $page_details['view_count']; ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p>
        <a href="<?php echo add_query_arg( 'export_csv', 1 ); ?>">Export CSV</a>
    </p>
</div>