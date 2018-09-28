<?php

namespace codingninjas;

class ControllerPages
{
    /**
     * Tasks Page
     * @param $page
     * @return TasksPage
     */
    public function tasks(Page $page)
    {
        $data = $page->data();
        $data['tasks_table'] = new TableTasks();

        $page = new TasksPage($data);

        $data = [
            'content' => App::view (
                ['pages', 'tasks.php'],
                ['page' => $page]
            )
        ];

        $page->update($data);

        return $page;
    }

    /**
     * Dashboard page
     * @param Page $page
     * @return Page
     */
    public function dashboard(Page $page)
    {
        $data = [
            'content' => do_shortcode ('[cn_dashboard]'),
        ];

        $page->update($data);

        return $page;
    }

    public function addNewTask(Page $page)
    {
        
    }
}