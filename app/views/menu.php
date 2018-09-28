<ul class="nav" id="side-menu">
    <?php foreach ($menu as $route => $item) {
        $active = (\codingninjas\App::get($item, 'active')) ? 'active' : '';
        $icon = \codingninjas\App::get($item, 'icon');
        $icon = ($icon) ? '<i class="fa '.$icon.' fa-fw"></i> ' : '';
        $title = \codingninjas\App::get($item, 'title');
        $url = \codingninjas\App::get($item, 'url');

        if (!$url) {
            $url = home_url ($route);
        }
        ?>
    <li>
        <a class="<?php echo $active; ?>" href="<?php echo $url; ?>"><?php echo $icon; ?><?php echo $title; ?></a>
    </li>
    <?php } ?>
</ul>