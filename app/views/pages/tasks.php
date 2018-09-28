<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo $page->panelTitle(); ?>
    </div>
    <!-- /.panel-heading -->
    <div class="panel-body">
        <?php do_action ('cn_before_tasks_table', $page)?>
        <?php echo $page->tasks(); ?>
        <?php do_action ('cn_after_tasks_table', $page)?>
    </div>
    <!-- /.panel-body -->
</div>
<!-- /.panel -->