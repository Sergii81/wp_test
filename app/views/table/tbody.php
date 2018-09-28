<?php if (!$rows) {return;} ?>

<tbody>
<?php
$num_class = 'odd';
foreach ($rows as $row) {
    $num_class = ('odd' == $num_class) ? 'even' : 'odd';
    ?>
    <tr class="<?php echo $num_class; ?>">
    <?php foreach ($row as $col) { ?>
        <td><?php echo $col; ?></td>
    <?php  }?>
    </tr>
<?php  }?>
</tbody>
