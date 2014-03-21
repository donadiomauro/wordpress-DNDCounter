<?php
$dropdowns = array(
    'month' => array( 'items' => $months,   'selected' => $date_month ),
    'day'   => array( 'items' => $days,     'selected' => $date_day ),
    'year'  => array( 'items' => $years,    'selected' => $date_year )
);

foreach ( $dropdowns as $name => $dropdown ) { ?>
    <select name="<?php echo "{$prefix}_{$name}"; ?>">
        <?php foreach ( $dropdown['items'] as $item ) {
            echo "<option value=\"$item\" " . selected( $dropdown['selected'], $item, false ) . ">$item</option>";
        } ?>
    </select>
<?php }