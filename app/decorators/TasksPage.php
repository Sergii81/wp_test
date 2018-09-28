<?php

namespace codingninjas;

use \Exception;

class TasksPage extends Page
{
    /**
     * Get panel title
     * @return string|void
     */
    public function panelTitle()
    {
        $title = __('List of Tasks', 'cn');
        return apply_filters ('cn_tasks_page_title', $title, $this);
    }

    /**
     * Get tasks list
     */
    public function tasks()
    {
        $table = App::get($this->data, 'tasks_table');

        if (!$table) {
            return '';
        }

        $content = $table->render();

        return apply_filters ('cn_tasks_page_tasks_table_html', $content, $this);
    }
}