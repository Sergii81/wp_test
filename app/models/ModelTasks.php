<?php

namespace codingninjas;

use \Exception;

class ModelTasks
{
    public function getAll()
    {
        $args = array(
            'numberposts' => -1,
            'post_type'   => Task::POST_TYPE
        );

        $posts = get_posts( $args );

        if (!$posts) {
            return false;
        }

        foreach ($posts as &$post) {
            $post = new Task($post);
        }

        return $posts;
    }
}